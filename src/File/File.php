<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\File;

use function fclose;
use function filesize;
use function fopen;
use function is_resource;
use function sys_get_temp_dir;
use function tempnam;

/**
 * Utility class wrapping file functions, turning ambiguous returns into Succeed or Throw.
 */
class File
{
    /**
     * @throws FileFailure When unable to open the file
     *
     * @return resource
     */
    public static function open(string $fileName, string $mode = 'r')
    {
        $handle = fopen($fileName, $mode);

        if ($handle === false) {
            throw FileFailure::open($fileName, $mode);
        }

        return $handle;
    }

    /**
     * @param resource $handle
     *
     * @throws FileFailure When unable to close the handle (or non-resource given)
     */
    public static function close($handle): void
    {
        if (!is_resource($handle)) {
            throw FileFailure::nonResource($handle);
        }

        if (!fclose($handle)) {
            throw FileFailure::close();
        }
    }

    /**
     * @throws FileFailure When unable to create a temp file
     */
    public static function temp(string $prefix): string
    {
        $file = tempnam(sys_get_temp_dir(), $prefix);

        if ($file === false) {
            throw FileFailure::temp($prefix);
        }

        return $file;
    }

    /**
     * @throws FileFailure When unable to determine the size
     */
    public static function size(string $fileName): int
    {
        $size = filesize($fileName);

        if ($size === false) {
            throw FileFailure::size($fileName);
        }

        return $size;
    }
}
