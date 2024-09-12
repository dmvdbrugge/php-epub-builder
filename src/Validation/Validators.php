<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Validation;

use DMvdBrugge\EpubBuilder\Validation\Validators\ChaptersValidator;
use DMvdBrugge\EpubBuilder\Validation\Validators\ColorValidator;
use DMvdBrugge\EpubBuilder\Validation\Validators\IetfValidator;
use DMvdBrugge\EpubBuilder\Validation\Validators\WritableFileValidator;

/**
 * Convience class for shorthand validation.
 */
class Validators
{
    public static function color(string $color): Validator
    {
        return new ColorValidator($color);
    }

    public static function writable(string $file): Validator
    {
        return new WritableFileValidator($file);
    }

    public static function ietf(string $language): Validator
    {
        return new IetfValidator($language);
    }

    /**
     * @param array<string, string> $chapters
     */
    public static function chapters(array $chapters): Validator
    {
        return new ChaptersValidator($chapters);
    }
}
