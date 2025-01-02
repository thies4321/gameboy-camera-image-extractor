<?php

declare(strict_types=1);

namespace GameboyCameraImageExtractor\Writer;

use GdImage;

use function fopen;
use function imagetruecolortopalette;
use function imagewbmp;

final class Wbmp extends Writer
{
    public const string EXTENSION = 'wbmp';

    public function write(GdImage $image, string $filename, ?string $outputDirectory): void
    {
        $filename = $this->getFileName($filename, $outputDirectory);
        $file = fopen($filename, 'w');
        imagetruecolortopalette($image, true, 4);
        imagewbmp($image, $file);
    }
}
