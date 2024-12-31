<?php

declare(strict_types=1);

namespace GameboyCameraImageExtractor;

use GameboyCameraImageExtractor\Entity\Palette;
use GameboyCameraImageExtractor\Exception\FileNotFound;
use GameboyCameraImageExtractor\Exception\InvalidColorCode;
use GameboyCameraImageExtractor\Exception\InvalidFileSize;
use GameboyCameraImageExtractor\Writer\Png;
use GameboyCameraImageExtractor\Writer\Writer;
use GdImage;

use function file_exists;
use function file_get_contents;
use function filesize;
use function sprintf;

final readonly class ImageWriter
{
    public function __construct(
        private Writer $writer = new Png(),
        private ImageExtractor $imageExtractor = new ImageExtractor(),
    ) {
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

        if ($fileSize !== ImageExtractor::SAVE_FILE_SIZE) {
            throw InvalidFileSize::forFileSize($fileSize);
        }

        $contents = $this->imageExtractor->unpackSaveData(file_get_contents($filePath));

        return $this->imageExtractor->getImages($contents, $palette);
    }

    /**
     * @throws FileNotFound
     * @throws InvalidColorCode
     * @throws InvalidFileSize
     */
    public function extractAndStoreToDisk(string $filePath, ?string $outputDirectory = null, ?Palette $palette = null): void
    {
        $images = $this->extract($filePath, $palette);

        foreach ($images as $key => $image) {
            $fileName = sprintf('output_%d', ($key + 1));
            $this->writer->write($image, $fileName, $outputDirectory);
        }
    }
}
