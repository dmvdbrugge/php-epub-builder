<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Content;

use function implode;
use function is_array;

/**
 * Creates the content of the content/content.opf file.
 *
 * This file:
 * - holds the book-metadata;
 * - lists all files present and referenceable in the EPUB (manifest);
 * - determines what is turned into "pages" in the book and in what order (spine).
 */
class Content
{
    public static function item(string $id, string $file): string
    {
        return <<<XML
            <item id="{$id}" href="{$file}" media-type="application/xhtml+xml"/>
            XML;
    }

    public static function itemref(string $idref): string
    {
        return <<<XML
            <itemref idref="{$idref}"/>
            XML;
    }

    /**
     * Variation on {@see self::item()} specifically for the toc.
     */
    public static function tocItem(): string
    {
        return <<<XML
            <item id="toc" href="toc.xhtml" media-type="application/xhtml+xml" properties="nav"/>
            XML;
    }

    /**
     * Variation on {@see self::item()} specifically for the toc.
     */
    public static function cssItem(): string
    {
        return <<<XML
            <item id="css" href="page.css" media-type="text/css"/>
            XML;
    }

    /**
     * @param string|string[] $items    One or more {@see self::item()} lines for the manifest
     * @param string|string[] $itemrefs One or more {@see self::itemref()} lines for the spine
     */
    public static function file(
        string | array $items,
        string | array $itemrefs,
        string $isbn,
        string $identifier,
        string $title,
        string $language,
        string $publisher,
        string $creator,
        string $description,
        string $modified,
    ): string {
        if (is_array($items)) {
            $items = implode('', $items);
        }

        if (is_array($itemrefs)) {
            $itemrefs = implode('', $itemrefs);
        }

        $identifiers = self::identifiers($isbn, $identifier);

        return <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <package xmlns="http://www.idpf.org/2007/opf" xmlns:opf="http://www.idpf.org/2007/opf" version="3.0" unique-identifier="BookID">
                <metadata xmlns:dc="http://purl.org/dc/elements/1.1/">
                    {$identifiers}
                    <dc:title>{$title}</dc:title>
                    <dc:language>{$language}</dc:language>
                    <dc:publisher>{$publisher}</dc:publisher>
                    <dc:creator>{$creator}</dc:creator>
                    <dc:description>{$description}</dc:description>
                    <meta property="dcterms:modified">{$modified}</meta>
                </metadata>
                <manifest>
                    {$items}
                </manifest>
                <spine>
                    {$itemrefs}
                </spine>
            </package>
            XML;
    }

    private static function identifiers(string $isbn, string $identifier): string
    {
        if ($isbn === '') {
            return <<<XML
                <dc:identifier id="BookID">{$identifier}</dc:identifier>
                XML;
        }

        $result = <<<XML
            <dc:identifier id="BookID" opf:scheme="isbn">{$isbn}</dc:identifier>
            XML;

        if ($identifier !== '') {
            $result .= <<<XML
                <dc:identifier>{$identifier}</dc:identifier>
                XML;
        }

        return $result;
    }
}
