<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Content;

/**
 * Creates the default content of the css file (content/page.css),
 * which is included in each chapter (and the toc).
 */
class CssContent
{
    public static function file(string $color, string $backgroundColor): string
    {
        /*
         * This styling is an attempt to use as much space as Apple's iPhone app "Books" allows.
         * As I have no ereader, I don't know how it behaves on other readers (or apps), sorry.
         */
        return <<<CSS
            @page {
                margin: 0;
                padding: 0;
            }
            body {
                margin: 0;
                padding: 0;
                color: {$color};
                background-color: {$backgroundColor};
            }
            p {
                margin-left: 0;
                margin-right: 0;
                margin-bottom: 0;
                padding-left: 0;
                padding-right: 0;
                padding-bottom: 0;
            }
            p:first-child {
                margin-top: 0;
                padding-top: 0;
            }
            CSS;
    }
}
