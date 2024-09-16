<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder;

use DMvdBrugge\EpubBuilder\Zip\ZipWrapper;
use PHPUnit\Framework\TestCase;

class EpubTest extends TestCase
{
    /**
     * @dataProvider dpFileNameSanitization
     */
    public function testFileNameSanitization(string $title, string $expected): void
    {
        $epub = new Epub(new ZipWrapper(), $title);

        self::assertSame("{$expected}.epub", $epub->getFileName());
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function dpFileNameSanitization(): array
    {
        return [
            'leading dot' => ['.htaccess and other Apache Magic', 'htaccess and other Apache Magic'],
            'ending dots' => ['The Dolly Dots continues ...', 'The Dolly Dots continues'],
            'reserved' => ['The C:\> Prompt', 'The C Prompt'],
            'control' => ["\tThe Hobbit,\nor There and Back Again", 'The Hobbit,or There and Back Again'],
            'nonPrint' => ["I r\u{A0}ea\u{A0}d normal\u{AD}ly", 'I read normally'],
            'mixed' => [" .\u{A0}:. MSN Culture .:. \nExplained ???", 'MSN Culture .. Explained'],
            'empty title' => ['', 'epub'],
            'empty result' => ['???.', 'epub'],
        ];
    }
}
