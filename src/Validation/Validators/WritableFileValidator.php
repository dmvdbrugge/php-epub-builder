<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Validation\Validators;

use DMvdBrugge\EpubBuilder\File\File;
use DMvdBrugge\EpubBuilder\File\FileFailure;

class WritableFileValidator extends BaseValidator
{
    public function __construct(
        private readonly string $file,
    ) {
    }

    public function valid(): bool
    {
        try {
            File::close(File::open($this->file, 'w+'));
        } catch (FileFailure) {
            return false;
        }

        return true;
    }

    protected function message(): string
    {
        return "Cannot open or create file '{$this->file}'";
    }
}
