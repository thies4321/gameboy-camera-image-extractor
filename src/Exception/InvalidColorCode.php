<?php

declare(strict_types=1);

namespace GameboyCameraImageExtractor\Exception;

use Exception;

use function sprintf;

final class InvalidColorCode extends Exception
{
    public static function fromCode(string $code): self
    {
        return new self(sprintf('Color code [%s] is not valid', $code));
    }
}
