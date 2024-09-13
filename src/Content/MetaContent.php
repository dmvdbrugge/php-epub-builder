<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Content;

/**
 * Creates the content of the META-INF/container.xml file.
 */
class MetaContent
{
    public static function container(): string
    {
        return <<<XML
            <?xml version="1.0"?>
            <container xmlns="urn:oasis:names:tc:opendocument:xmlns:container" version="1.0">
                <rootfiles>
                    <rootfile full-path="content/content.opf" media-type="application/oebps-package+xml"/>
                </rootfiles>
            </container>
            XML;
    }
}
