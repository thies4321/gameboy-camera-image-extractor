<?php

declare(strict_types=1);

namespace GameboyCameraImageExtractor\Writer;

use GdImage;

use function imagetruecolortopalette;
use function imagexbm;

final class Xbm extends Writer
{
    public const string EXTENSION = 'xbm';

    public function write(GdImage $image, string $filename, ?string $outputDirectory): void
    {
        $filename = $this->getFileName($filename, $outputDirectory);
        imagetruecolortopalette($image, true, 4);
        imagexbm($image, $filename);
    }
}
