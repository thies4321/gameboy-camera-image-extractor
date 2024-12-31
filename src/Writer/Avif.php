<?php

declare(strict_types=1);

namespace GameboyCameraImageExtractor\Writer;

use GdImage;

use function imageavif;

final class Avif extends Writer
{
    public const string EXTENSION = 'avif';

    public function write(GdImage $image, string $filename, ?string $outputDirectory): void
    {
        $filename = $this->getFileName($filename, $outputDirectory);
        imageavif($image, $filename);
    }
}
