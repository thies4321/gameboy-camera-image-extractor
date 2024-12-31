<?php

declare(strict_types=1);

namespace GameboyCameraImageExtractor\Exception;

use Exception;
use GameboyCameraImageExtractor\ImageExtractor;

use function sprintf;

final class InvalidFileSize extends Exception
{
    public static function forFileSize(int $fileSize) : self
    {
        return new self(sprintf('Expected file size [%d] but got file size [%d]', ImageExtractor::SAVE_FILE_SIZE, $fileSize));
    }
}
