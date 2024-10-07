<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Content;

use function implode;
use function is_array;

/**
 * Creates the content of the toc file (content/toc.xhtml),
 * which handles navigation in the EPUB.
 */
class TocContent
{
    public static function nav(string $href, string $name): string
    {
        return <<<XML
            <li><a href="{$href}">{$name}</a></li>
            XML;
    }

    /**
     * @param string|string[] $nav One or more {@see self::nav()} lines
     */
    public static function file(string | array $nav): string
    {
        if (is_array($nav)) {
            $nav = implode("\n                ", $nav);
        }

        return <<<HTML
            <html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops">
                <head>
                    <title>TOC</title>
                    <link rel="stylesheet" type="text/css" href="page.css"/>
                </head>
                <body>
                    <nav epub:type="toc">
                        <ol>
                            {$nav}
                        </ol>
                    </nav>
                </body>
            </html>
            HTML;
    }
}
