<?php

declare(strict_types=1);

namespace GameboyCameraImageExtractor\Writer;

use GdImage;

use function sprintf;

abstract class Writer
{
    public const string EXTENSION = '';

    abstract public function write(GdImage $image, string $filename, ?string $outputDirectory) : void;

    protected function getFileName(string $filename, ?string $outputDirectory) : string
    {
        $filename = sprintf('%s.%s', $filename, static::EXTENSION);

        if ($outputDirectory !== null) {
            $filename = sprintf('%s/%s', $outputDirectory, $filename);
        }

        return $filename;
    }
}
