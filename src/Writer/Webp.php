<?php

declare(strict_types=1);

namespace GameboyCameraImageExtractor\Writer;

use GdImage;

use function fopen;
use function imagewebp;

final class Webp extends Writer
{
    public const string EXTENSION = 'webp';

    public function write(GdImage $image, string $filename, ?string $outputDirectory): void
    {
        $filename = $this->getFileName($filename, $outputDirectory);
        $file = fopen($filename, 'w');
        imagewebp($image, $file);
    }
}
