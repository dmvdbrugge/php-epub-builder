<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Validation;

interface Validator
{
    public function valid(): bool;

    /**
     * @throws ValidationFailure
     */
    public function validate(): void;
}
