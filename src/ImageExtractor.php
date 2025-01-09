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
use function hexdec;
use function imagecolorallocate;
use function imagecreatetruecolor;
use function imagesetpixel;
use function intval;
use function preg_match;
use function strlen;
use function substr;
use function unpack;

final readonly class ImageExtractor
{
    public const int SAVE_FILE_SIZE = 131072;
    private const string HEX_COLOR_PCRE = '/^#[0-9a-f]{3}([0-9a-f]{3})?$/i';
    private const string RGB_COLOR_PCRE = '/rgb\\(\\s*((?:[0-2]?[0-9])?[0-9])\\s*,\\s*((?:[0-2]?[0-9])?[0-9])\\s*,\\s*((?:[0-2]?[0-9])?[0-9])\\s*\\)$/i';
    private const string RGB_PERCENT_COLOR_PCRE = '/rgb\\(\\s*((?:[0-1]?[0-9])?[0-9])%\\s*,\\s*((?:[0-1]?[0-9])?[0-9])%\\s*,\\s*((?:[0-1]?[0-9])?[0-9])%\\s*\\)$/i';

    /**
     * @throws InvalidColorCode
     */
    private function resolveColor(string $color): array
    {
        if ($color === 'transparent') {
            $color = '#FFF';
        }

        if (preg_match(self::HEX_COLOR_PCRE, $color)) {
            if (strlen($color) === 4) {
                return [hexdec($color[1] . $color[1]), hexdec($color[2] . $color[2]), hexdec($color[3] . $color[3])];
            }

            return [hexdec(substr($color, 1, 2)), hexdec(substr($color, 3, 2)), hexdec(substr($color, 5, 2))];
        }

        if (preg_match(self::RGB_COLOR_PCRE, $color, $matches)) {
            return [intval($matches[1]), intval($matches[2]), intval($matches[3])];
        }

        if (preg_match(self::RGB_PERCENT_COLOR_PCRE, $color, $matches)) {
            return [intval($matches[1] * 2.55), intval($matches[2] * 2.55), intval($matches[3] * 2.55)];
        }

        throw InvalidColorCode::fromCode($color);
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

                    $imageData[$y][($x * 8 + $i)] = $val;
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
     *
     * @throws InvalidColorCode
     */
    public function extractImages(array $saveData, ?Palette $palette = null, bool $includeDeletedImages = false): array
    {
        if (! $palette instanceof Palette) {
            $palette = Palette::createForPreset(PalettePreset::BlackAndWhite);
        }

        $images = [];

        for ($photo = 0; $photo < 30; $photo++) {
            if ($includeDeletedImages === false) {
                if ($this->isPhotoActive($saveData, $photo) === false) {
                    continue;
                }
            }

            $imageData = $this->photoToImageData($saveData, $photo);
            $image = imagecreatetruecolor(128, 112);

            foreach($imageData as $rowId => $row) {
                foreach($row as $position => $pixel) {
                    $rgb = $this->resolveColor($palette->getColorForPixelType($pixel));
                    $colorIdentifier = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
                    imagesetpixel($image, $position, $rowId, $colorIdentifier);
                }
            }

            $images[] = $image;
        }

        return $images;
    }

    public function unpackSaveData(string $saveData): array
    {
        return unpack('x/c*', $saveData);
    }

    /**
     * @return GdImage[]
     *
     * @throws FileNotFound
     * @throws InvalidColorCode
     * @throws InvalidFileSize
     */
    public function extractFromFile(
        string $filePath,
        ?Palette $palette = null,
        bool $includeDeletedImages = false
    ): array {
        if (! file_exists($filePath)) {
            throw FileNotFound::forPath($filePath);
        }

        $fileSize = filesize($filePath);

        if ($fileSize !== ImageExtractor::SAVE_FILE_SIZE) {
            throw InvalidFileSize::forFileSize($fileSize);
        }

        $contents = $this->unpackSaveData(file_get_contents($filePath));

        return $this->extractImages($contents, $palette, $includeDeletedImages);
    }
}
