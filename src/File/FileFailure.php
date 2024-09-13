<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\File;

use DMvdBrugge\EpubBuilder\EpubBuilderException;
use RuntimeException;

class FileFailure extends RuntimeException implements EpubBuilderException
{
    public static function open(string $fileName, string $mode): self
    {
        return new self("Unable to open file '{$fileName}' with mode '{$mode}'");
    }

    public static function close(): self
    {
        return new self("Unable to close file");
    }

    public static function nonResource(mixed $value): self
    {
        $type = gettype($value);

        return new self("Cannot close non-resource '{$type}'");
    }

    public static function temp(string $prefix): self
    {
        return new self("Unable to create a temporary file with prefix '{$prefix}'");
    }

    public static function size(string $fileName): self
    {
        return new self("Unable to determine the size of '{$fileName}'");
    }

    public static function delete(string $fileName): self
    {
        return new self("Unable to delete file '{$fileName}'");
    }
}
