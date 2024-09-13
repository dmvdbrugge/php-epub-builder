<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Validation\Validators;

use function count;
use function implode;
use function is_string;
use function sprintf;

class ChaptersValidator extends BaseValidator
{
    /** @var string[] */
    private array $messages;

    public function __construct(
        /** @var array<string, string> */
        private readonly array $chapters,
    ) {
    }

    public function valid(): bool
    {
        // Multiple calls should not result in a buildup of errors.
        if (isset($this->messages)) {
            return empty($this->messages);
        }

        $this->messages = [];

        foreach ($this->chapters as $name => $value) {
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
            "Invalid 'chapters'. Validation resulted in %d error(s): %s",
            count($this->messages),
            implode('; ', $this->messages),
        );
    }
}
