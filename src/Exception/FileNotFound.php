<?php

declare(strict_types=1);

namespace GameboyCameraImageExtractor\Exception;

use Exception;

use function sprintf;

final class FileNotFound extends Exception
{
    public static function forPath(string $path) : self
    {
        return new self(sprintf('File [%s] not found.', $path));
    }
}
