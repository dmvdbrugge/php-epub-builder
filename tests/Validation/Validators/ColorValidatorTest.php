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
     * @dataProvider dpValidColors
     */
    public function testValidColor(string $color): void
    {
        $validator = new ColorValidator($color);

        self::assertTrue($validator->valid());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function dpValidColors(): array
    {
        return [
            'Shorthand lc' => ['#09f'],
            'Long form lc' => ['#0a1d9f'],
            'Shorthand uc' => ['#09F'],
            'Long form uc' => ['#0A1D9F'],
        ];
    }

    /**
     * @dataProvider dpInvalidColors
     */
    public function testInvalidColor(string $color): void
    {
        $validator = new ColorValidator($color);

        self::assertFalse($validator->valid());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function dpInvalidColors(): array
    {
        return [
            'Name' => ['blue'],
            'No hash' => ['09f'],
            'Non-hex' => ['#GZ!'],
            'Length 1' => ['#1'],
            'Length 2' => ['#12'],
            'Length 4' => ['#1234'],
            'Length 5' => ['#12345'],
            'Length 7' => ['#1234567'],
        ];
    }
}
