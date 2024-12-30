<?php

declare(strict_types=1);

namespace GameboyCameraImageExtractor;

use GameboyCameraImageExtractor\Entity\Palette;
use GameboyCameraImageExtractor\Enum\PalettePreset;
use GameboyCameraImageExtractor\Exception\FileNotFound;
use GameboyCameraImageExtractor\Exception\InvalidColorCode;
use GameboyCameraImageExtractor\Exception\InvalidFileSize;
use GdImage;

use function file_exists;
use function file_get_contents;
use function filesize;
use function floor;
use function fopen;
use function hexdec;
use function imagecolorallocate;
use function imagecreatetruecolor;
use function imagepng;
use function imagesetpixel;
use function imagetruecolortopalette;
use function intval;
use function preg_match;
use function sprintf;
use function strlen;
use function substr;
use function unpack;

final class ImageWriter
{
    public const int SAVE_FILE_SIZE = 131072;
    private const string HEX_COLOR_PCRE = '/^#[0-9a-f]{3}([0-9a-f]{3})?$/i';
    private const string RGB_COLOR_PCRE = '/rgb\\(\\s*((?:[0-2]?[0-9])?[0-9])\\s*,\\s*((?:[0-2]?[0-9])?[0-9])\\s*,\\s*((?:[0-2]?[0-9])?[0-9])\\s*\\)$/i';
    private const string RGB_PERCENT_COLOR_PCRE = '/rgb\\(\\s*((?:[0-1]?[0-9])?[0-9])%\\s*,\\s*((?:[0-1]?[0-9])?[0-9])%\\s*,\\s*((?:[0-1]?[0-9])?[0-9])%\\s*\\)$/i';

    /**
     * @throws InvalidColorCode
     */
    public function resolveColor(string $color): array
    {
        if ($color === 'transparent') {
            $color = '#FFF';
        }

        if (preg_match(self::HEX_COLOR_PCRE, $color)) {
            if (strlen($color) === 4) {
                $r = hexdec($color[1] . $color[1]);
                $g = hexdec($color[2] . $color[2]);
                $b = hexdec($color[3] . $color[3]);
            } else {
                $r = hexdec(substr($color, 1, 2));
                $g = hexdec(substr($color, 3, 2));
                $b = hexdec(substr($color, 5, 2));
            }
        } elseif (preg_match(self::RGB_COLOR_PCRE, $color, $matches)) {
            $r = intval($matches[1]);
            $g = intval($matches[2]);
            $b = intval($matches[3]);
        } elseif (preg_match(self::RGB_PERCENT_COLOR_PCRE, $color, $matches)) {
            $r = intval($matches[1] * 2.55);
            $g = intval($matches[2] * 2.55);
            $b = intval($matches[3] * 2.55);
        } else {
            throw InvalidColorCode::fromCode($color);
        }

        return [
            'red' => $r,
            'green' => $g,
            'blue' => $b
        ];
    }

    private function getTileIndex($base, $tile_id): float|int
    {
        return $base + 16 * $tile_id;
    }

    private function photoToImageData(array $saveData, int $photoIndex): array
    {
        $offset = 0x2000 + $photoIndex * 0x1000;
        $w = 128;
        $h = 112;
        $w_tiles = $w >> 3;
        $h_tiles = $h >> 3;
        $imageData = [];

        for ($y = 0; $y < ($h_tiles * 8); $y++) {
            for ($x = 0; $x < $w_tiles; $x++) {
                $tileData = $this->getTileIndex($offset, floor($y >> 3) * 0x10 + $x);

                for ($i = 0; $i < 8; $i++) {
                    $p = (int) ($tileData + (($y & 7) * 2));

                    $val = 0;

                    if (($saveData[$p] & (0x80 >> $i)) != 0) {
                        $val += 1;
                    }
                    if (($saveData[$p + 1] & (0x80 >> $i)) != 0) {
                        $val += 2;
                    }

                    $ding = $x * 8 + $i;

                    $imageData[$y][$ding] = $val;
                }
            }
        }

        return $imageData;
    }

    private function isPhotoActive(array $saveData, int $photoIndex): bool
    {
        $offset = 0x11B2 + $photoIndex;

        if ($saveData[$offset] === -1) {
            return false;
        }

        return true;
    }

    /**
     * @return GdImage[]
     * @throws InvalidColorCode
     */
    private function getImages(array $saveData, ?Palette $palette = null): array
    {
        if (! $palette instanceof Palette) {
            $palette = Palette::getForPreset(PalettePreset::BlackAndWhite);
        }

        $images = [];

        for ($photo = 0; $photo < 30; $photo++) {
            $isActive = $this->isPhotoActive($saveData, $photo);

            if ($isActive === false) {
                continue;
            }

            $imageData = $this->photoToImageData($saveData, $photo);
            $image = imagecreatetruecolor(128, 112);

            foreach($imageData as $rowId => $row) {
                foreach($row as $position => $pixel) {
                    $rgb = $this->resolveColor($palette->getColorForPixelType($pixel));
                    $colorIdentifier = imagecolorallocate($image, $rgb['red'], $rgb['green'], $rgb['blue']);
                    imagesetpixel($image, $position, $rowId, $colorIdentifier);
                }
            }

            $images[] = $image;
        }

        return $images;
    }

    /**
     * @return GdImage[]
     *
     * @throws FileNotFound
     * @throws InvalidColorCode
     * @throws InvalidFileSize
     */
    public function extract(string $filePath, ?Palette $palette = null): array
    {
        if (! file_exists($filePath)) {
            throw FileNotFound::forPath($filePath);
        }

        $fileSize = (int) filesize($filePath);

        if ($fileSize !== self::SAVE_FILE_SIZE) {
            throw InvalidFileSize::forFileSize($fileSize);
        }

        $contents = unpack('x/c*', file_get_contents($filePath));

        return $this->getImages($contents, $palette);
    }

    /**
     * @throws FileNotFound
     * @throws InvalidColorCode
     * @throws InvalidFileSize
     */
    public function extractAndStore(string $filePath, ?string $destinationPath = null, ?Palette $palette = null): void
    {
        $images = $this->extract($filePath, $palette);

        foreach ($images as $key => $image) {
            $fileName = sprintf('output_%d.png', ($key + 1));

            if ($destinationPath !== null) {
                $fileName = sprintf('%s/%s', $destinationPath, $fileName);
            }

            $file = fopen($fileName, 'w');
            imagetruecolortopalette($image, true, 4);
            imagepng($image, $file);
        }
    }
}
