<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Tests\Validation\Validators;

use DMvdBrugge\EpubBuilder\Validation\ValidationFailure;
use DMvdBrugge\EpubBuilder\Validation\Validators\IetfValidator;
use PHPUnit\Framework\TestCase;

class IetfValidatorTest extends TestCase
{
    public function testValidateThrowsOnInvalidLanguage(): void
    {
        $validator = new IetfValidator('this is not a language');

        $this->expectException(ValidationFailure::class);
        $this->expectExceptionMessage("Language 'this is not a language' is not a valid IETF language tag");

        $validator->validate();
    }

    /**
     * @dataProvider dpValidIetfs
     */
    public function testValidIetf(string $color): void
    {
        $validator = new IetfValidator($color);

        self::assertTrue($validator->valid());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function dpValidIetfs(): array
    {
        // This list is far from comprehensive but provides a nice baseline.
        return [
            'English' => ['en'],
            'Flemish (Belgian Dutch)' => ['nl-BE'],
            'American English Braille' => ['en-Brai-US'], // Needs a very special ereader
            // Following examples come from the IETF wiki page
            'Latin American Spanish' => ['es-419'],
            'Romansh Sursilvan' => ['rm-sursilv'],
            'Serbian using Cyrillic script' => ['sr-Cyrl'],
            'Min Nan Chinese using trad. Han characters, spoken in Taiwan' => ['nan-Hant-TW'],
            'Cantonese using trad. Han characters, spoken in Hong Kong' => ['yue-Hant-HK'],
            'Zürich German' => ['gsw-u-sd-chzh'],
            'English translated from Japanese' => ['en-t-jp'],
            'Arabic using latin digits' => ['ar-u-nu-latn'],
            'Hebrew, spoken in Israel, using the trad. Hebrew calendar, in the Asia/Jerusalem time zone' => ['he-IL-u-ca-hebrew-tz-jeruslm'],
        ];
    }

    /**
     * @dataProvider dpInvalidIetfs
     * @dataProvider dpInvalidSpecialCases
     */
    public function testInvalidIetf(string $color): void
    {
        $validator = new IetfValidator($color);

        self::assertFalse($validator->valid());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function dpInvalidIetfs(): array
    {
        // This list is far from comprehensive but provides a nice baseline.
        return [
            'Empty string' => [''],
            'Single character' => ['a'],
            'Four characters' => ['true'],
            'Nine characters' => ['abcabcabc'],
            'Alphanumeric' => ['y2k'],
            'Ending dash 1' => ['en-'],
            'Ending dash 2' => ['en-GB-'],
            'Ending dash 3' => ['sr-Cyrl-'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function dpInvalidSpecialCases(): array
    {
        return [
            'English' => ['english'],
            'Capitalized' => ['English'],
        ];
    }
}
