<?php

declare(strict_types=1);

namespace GameboyCameraImageExtractor\Writer;

use GdImage;

use function fopen;
use function imagepng;
use function imagetruecolortopalette;

final class Png extends Writer
{
    public const string EXTENSION = 'png';

    public function write(GdImage $image, string $filename, ?string $outputDirectory): void
    {
        $filename = $this->getFileName($filename, $outputDirectory);
        $file = fopen($filename, 'w');
        imagetruecolortopalette($image, true, 4);
        imagepng($image, $file);
    }
}
