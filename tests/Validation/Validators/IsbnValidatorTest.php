<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Tests\Validation\Validators;

use DMvdBrugge\EpubBuilder\Validation\ValidationFailure;
use DMvdBrugge\EpubBuilder\Validation\Validators\IsbnValidator;
use PHPUnit\Framework\TestCase;

class IsbnValidatorTest extends TestCase
{
    public function testValidateThrowsOnInvalidISBN(): void
    {
        $validator = new IsbnValidator('this is not an isbn');

        $this->expectException(ValidationFailure::class);
        $this->expectExceptionMessage("ISBN 'this is not an isbn' is not a valid ISBN");

        $validator->validate();
    }

    /**
     * @dataProvider dpValidCases
     */
    public function testValidCases(string $color): void
    {
        $validator = new IsbnValidator($color);

        self::assertTrue($validator->valid());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function dpValidCases(): array
    {
        return [
            'all digit 9' => ['684843285'],
            'all digit 10' => ['1843560283'],
            'all digit 13' => ['9781843560289'],
            'with dashes 9' => ['684-84328-5'],
            'with dashes 10' => ['1-84356-028-3'],
            'with dashes 13' => ['978-1-84356-028-9'],
            'with X 9' => ['8044-2957-X'],
            'with X 10' => ['1-55404-295-X'],
            'with M' => ['M-2306-7118-7'],
        ];
    }

    /**
     * @dataProvider dpInvalidCases
     */
    public function testInvalidCases(string $color): void
    {
        $validator = new IsbnValidator($color);

        self::assertFalse($validator->valid());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function dpInvalidCases(): array
    {
        return [
            'empty' => [''],
            'length 8' => ['12345679'], // Would pass the 10-check-calculation
            'length 11' => ['12345678909'], // Would pass the 10-check-calculation
            'length 12' => ['123456789018'], // Would pass the 13-check-calculation
            'length 14' => ['12345678901235'], // Would pass the 13-check-calculation
            'starts with dash' => ['-684-84328-5'],
            'ends with dash' => ['8044-2957-X-'],
            'with X wrong spot 10' => ['1-55404-29X-5'],
            'with M wrong spot' => ['2-M306-7118-7'],
            'invalid checksum 9' => ['684-84328-4'],
            'invalid checksum 10' => ['1-84356-028-4'],
            'invalid checksum 13' => ['978-1-84356-028-4'],
            'non-digit 9' => ['68A-84328-5'],
            'non-digit 10' => ['1-84356-O28-3'],
            'non-digit 13' => ['97B-1-84356-028-9'],
        ];
    }
}
