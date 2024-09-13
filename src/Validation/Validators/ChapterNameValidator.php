<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Validation\Validators;

use DMvdBrugge\EpubBuilder\Content\Content;

use function in_array;
use function strtolower;

class ChapterNameValidator extends BaseValidator
{
    /** See {@see Content::cssItem()} and {@see Content::tocItem()} */
    private const RESERVED_NAMES = ['css', 'toc'];

    public function __construct(
        private readonly string $originalName,
        private readonly string $sanitizedName,
    ) {
    }

    /**
     * A (sanitized) chapter name is valid, when it is not a reserved name.
     */
    public function valid(): bool
    {
        return !$this->reserved();
    }

    protected function message(): string
    {
        return "Chapter name '{$this->originalName}' when sanitized results in reserved name '{$this->sanitizedName}'";
    }

    private function reserved(): bool
    {
        return in_array(
            strtolower($this->sanitizedName),
            self::RESERVED_NAMES,
            true,
        );
    }
}
