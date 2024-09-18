<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Validation\Validators;

use function implode;
use function preg_match;
use function str_replace;
use function str_split;
use function strlen;
use function strtoupper;

class IsbnValidator extends BaseValidator
{
    public function __construct(
        private readonly string $isbn,
    ) {
    }

    /**
     * @see https://en.wikipedia.org/wiki/ISBN
     */
    public function valid(): bool
    {
        if (preg_match('/(^[^\dM]|[^\dX]$)/i', $this->isbn) !== 0) {
            return false;
        }

        $isbn = str_replace(['-', ' '], '', $this->isbn);

        return match (strlen($isbn)) {
            9 => $this->isbn10Check('0' . $isbn),
            10 => $this->isbn10Check($isbn),
            13 => $this->isbn13Check($isbn),
            default => false,
        };
    }

    protected function message(): string
    {
        return "ISBN '{$this->isbn}' is not a valid ISBN";
    }

    private function isbn10Check(string $isbn): bool
    {
        $digits = str_split($isbn);

        // Music score edge-case
        if (strtoupper($digits[0]) === 'M') {
            $digits[0] = '9790';

            return $this->isbn13Check(implode($digits));
        }

        // 10 % 11 is not a single digit so they used X
        if (strtoupper($digits[9]) === 'X') {
            $digits[9] = '10';
        }

        $tally = $sum = 0;

        foreach ($digits as $digit) {
            $intDigit = (int)$digit;

            if ((string)$intDigit !== $digit) {
                return false;
            }

            $tally += $intDigit;
            $sum += $tally;
        }

        return $sum % 11 === 0;
    }

    private function isbn13Check(string $isbn): bool
    {
        $digits = str_split($isbn);
        $mult = 1;
        $sum = 0;

        foreach ($digits as $digit) {
            $intDigit = (int)$digit;

            if ((string)$intDigit !== $digit) {
                return false;
            }

            $sum += $mult * $intDigit;
            $mult = $mult === 1 ? 3 : 1;
        }

        return $sum % 10 === 0;
    }
}
