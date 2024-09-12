<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Zip;

use DMvdBrugge\EpubBuilder\EpubBuilderException;
use RuntimeException;

class BuildFailure extends RuntimeException implements EpubBuilderException
{
}
