<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Validation\Validators;

class ChaptersValidator extends BaseValidator
{
    /** @var string[] */
    private array $messages = [];

    public function __construct(
        /** @var array<string, string> */
        private readonly array $chapters,
    ) {
    }

    public function valid(): bool
    {
        foreach ($this->chapters as $name => &$value) {
            if (!is_string($name)) {
                $this->messages[] = "Invalid chapter name, expected a string, got: " . gettype($name);
            }

            if (!is_string($value)) {
                $this->messages[] = "Invalid chapter content, expected a string, got: " . gettype($value);
            }
        }

        return empty($this->messages);
    }

    protected function message(): string
    {
        return sprintf(
            "Invalid 'chapters' array. Validation resulted in %d error(s): %s",
            count($this->messages),
            implode('; ', $this->messages),
        );
    }
}
