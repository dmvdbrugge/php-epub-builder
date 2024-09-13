<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Tests\Validation\Validators;

use DMvdBrugge\EpubBuilder\Validation\ValidationFailure;
use DMvdBrugge\EpubBuilder\Validation\Validators\ColorValidator;
use PHPUnit\Framework\TestCase;

class ColorValidatorTest extends TestCase
{
    public function testValidateThrowsOnInvalidColor(): void
    {
        $validator = new ColorValidator('#not-a-hex-color');

        $this->expectException(ValidationFailure::class);
        $this->expectExceptionMessage("Invalid hex color '#not-a-hex-color'");

        $validator->validate();
    }

    /**
     * @dataProvider dpValid
     */
    public function testValid(string $color, bool $expected): void
    {
        $validator = new ColorValidator($color);

        self::assertSame($expected, $validator->valid());
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function dpValid(): array
    {
        return [
            'Shorthand' => ['#09f', true],
            'Long form' => ['#019aff', true],
            'Name' => ['blue', false],
            'No hash' => ['09f', false],
            'Non-hex' => ['#GZ!', false],
            'Length 1' => ['#1', false],
            'Length 2' => ['#12', false],
            'Length 4' => ['#1234', false],
            'Length 5' => ['#12345', false],
            'Length 7' => ['#1234567', false],
        ];
    }
}
