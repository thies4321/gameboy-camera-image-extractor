<?php

declare(strict_types=1);

namespace GameboyCameraImageExtractor\Writer;

use GdImage;

use function fopen;
use function imagejpeg;
use function imagetruecolortopalette;

final class Jpeg extends Writer
{
    public const string EXTENSION = 'jpeg';

    public function write(GdImage $image, string $filename, ?string $outputDirectory): void
    {
        $filename = $this->getFileName($filename, $outputDirectory);
        $file = fopen($filename, 'w');
        imagetruecolortopalette($image, true, 4);
        imagejpeg($image, $file);
    }
}
