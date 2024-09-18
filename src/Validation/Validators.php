<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Validation;

use DMvdBrugge\EpubBuilder\Validation\Validators\ChapterNameValidator;
use DMvdBrugge\EpubBuilder\Validation\Validators\ChaptersValidator;
use DMvdBrugge\EpubBuilder\Validation\Validators\ColorValidator;
use DMvdBrugge\EpubBuilder\Validation\Validators\IetfValidator;
use DMvdBrugge\EpubBuilder\Validation\Validators\IsbnValidator;
use DMvdBrugge\EpubBuilder\Validation\Validators\WritableFileValidator;

/**
 * Convience class for shorthand validation.
 *
 * Keep methods in alphabetical order.
 */
class Validators
{
    public static function chapterName(string $originalName, string $sanitizedName): Validator
    {
        return new ChapterNameValidator($originalName, $sanitizedName);
    }

    /**
     * @param array<string, string> $chapters
     */
    public static function chapters(array $chapters): Validator
    {
        return new ChaptersValidator($chapters);
    }

    public static function color(string $color): Validator
    {
        return new ColorValidator($color);
    }

    public static function ietf(string $language): Validator
    {
        return new IetfValidator($language);
    }

    public static function isbn(string $isbn): Validator
    {
        return new IsbnValidator($isbn);
    }

    public static function writable(string $file): Validator
    {
        return new WritableFileValidator($file);
    }
}
