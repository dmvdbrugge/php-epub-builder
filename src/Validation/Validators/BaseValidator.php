<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Validation\Validators;

use DMvdBrugge\EpubBuilder\Validation\ValidationFailure;
use DMvdBrugge\EpubBuilder\Validation\Validator;

abstract class BaseValidator implements Validator
{
    abstract protected function message(): string;

    public function validate(): void
    {
        if (!$this->valid()) {
            throw new ValidationFailure($this->message());
        }
    }
}
