<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Validation;

use DMvdBrugge\EpubBuilder\EpubBuilderException;
use InvalidArgumentException;

class ValidationFailure extends InvalidArgumentException implements EpubBuilderException
{
}
