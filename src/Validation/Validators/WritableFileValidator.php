<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Validation\Validators;

class WritableFileValidator extends BaseValidator
{
    public function __construct(
        private readonly string $file,
    ) {
    }

    public function valid(): bool
    {
        $handle = fopen($this->file, 'w+');

        if ($handle === false) {
            return false;
        }

        fclose($handle);

        return true;
    }

    protected function message(): string
    {
        return "Cannot open or create file '{$this->file}'";
    }
}
