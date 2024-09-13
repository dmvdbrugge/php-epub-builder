<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Validation;

interface Validator
{
    /**
     * For just checking validity.
     */
    public function valid(): bool;

    /**
     * For a guard clause, following the "Succeed or Throw" mantra.
     *
     * Implemented when extending {@see BaseValidator}.
     *
     * @throws ValidationFailure
     */
    public function validate(): void;
}
