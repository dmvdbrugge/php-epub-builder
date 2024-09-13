<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Content;

/**
 * Creates the content of a chapter file (content/<sanitizedname>.xhtml).
 */
class ChapterContent
{
    public static function file(string $name, string $content): string
    {
        return <<<HTML
            <html xmlns="http://www.w3.org/1999/xhtml">
                <head>
                    <title>{$name}</title>
                    <link rel="stylesheet" type="text/css" href="page.css"/>
                </head>
                <body>{$content}</body>
            </html>
            HTML;
    }
}
