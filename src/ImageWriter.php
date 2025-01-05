<?php

declare(strict_types=1);

namespace GameboyCameraImageExtractor;

use GameboyCameraImageExtractor\Entity\Palette;
use GameboyCameraImageExtractor\Exception\FileNotFound;
use GameboyCameraImageExtractor\Exception\InvalidColorCode;
use GameboyCameraImageExtractor\Exception\InvalidFileSize;
use GameboyCameraImageExtractor\Writer\Png;
use GameboyCameraImageExtractor\Writer\Writer;

use function sprintf;

final readonly class ImageWriter
{
    public function __construct(
        private Writer $writer = new Png(),
        private ImageExtractor $imageExtractor = new ImageExtractor(),
    ) {
    }

    /**
     * @throws FileNotFound
     * @throws InvalidColorCode
     * @throws InvalidFileSize
     */
    public function extractAndStoreToDisk(string $filePath, ?string $outputDirectory = null, ?Palette $palette = null): void
    {
        $images = $this->imageExtractor->extractFromFile($filePath, $palette);

        foreach ($images as $key => $image) {
            $fileName = sprintf('output_%d', ($key + 1));
            $this->writer->write($image, $fileName, $outputDirectory);
        }
    }
}
