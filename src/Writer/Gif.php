<?php

declare(strict_types=1);

namespace GameboyCameraImageExtractor\Writer;

use GdImage;

use function fopen;
use function imagetruecolortopalette;

final class Gif extends Writer
{
    public const string EXTENSION = 'gif';

    public function write(GdImage $image, string $filename, ?string $outputDirectory): void
    {
        $filename = $this->getFileName($filename, $outputDirectory);
        $file = fopen($filename, 'w');
        imagetruecolortopalette($image, true, 4);
        imagegif($image, $file);
    }
}
