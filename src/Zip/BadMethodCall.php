<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Zip;

use BadMethodCallException;
use DMvdBrugge\EpubBuilder\EpubBuilderException;

class BadMethodCall extends BadMethodCallException implements EpubBuilderException
{
}
