<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Validation\Validators;

use function preg_match;

/**
 * A valid color starts with a # followed by either 3 or 6 hex-digits.
 */
class ColorValidator extends BaseValidator
{
    private const REGEX = '/^#([0-9a-f]{3}){1,2}$/i';

    public function __construct(
        private readonly string $color,
    ) {
    }

    public function valid(): bool
    {
        return preg_match(self::REGEX, $this->color) === 1;
    }

    protected function message(): string
    {
        return "Invalid hex color '{$this->color}'";
    }
}
