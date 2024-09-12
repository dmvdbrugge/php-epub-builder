<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder;

use DateTimeInterface;
use DMvdBrugge\EpubBuilder\Validation\ValidationFailure;
use DMvdBrugge\EpubBuilder\Validation\Validators;
use DMvdBrugge\EpubBuilder\Zip\BuildFailure;
use DMvdBrugge\EpubBuilder\Zip\ZipWrapper;

class EpubBuilder
{
    // Meta-data
    private string $author = '';
    private string $description = '';
    private string $identifier = '';
    private string $language = '';
    private string $modified = '';
    private string $publisher = '';
    private string $title = '';

    // Content
    /** @var array<string, string> */
    private array $chapters = [];

    // Formatting
    private string $backgroundColor = '#111';
    private string $textColor = '#eee';
    private ?string $css;

    // Inner workings
    private string $file;
    private bool $cleanup = true;

    /**
     * @return $this
     */
    public function author(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return $this
     */
    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return $this
     */
    public function identifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return $this
     */
    public function language(string $language): self
    {
        Validators::ietf($language)->validate();

        $this->language = $language;

        return $this;
    }

    /**
     * @return $this
     */
    public function modified(DateTimeInterface $modified): self
    {
        $this->modified = $modified->format('Y-m-d\TH:i:s\Z');

        return $this;
    }

    /**
     * @return $this
     */
    public function publisher(string $publisher): self
    {
        $this->publisher = $publisher;

        return $this;
    }

    /**
     * @return $this
     */
    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Sets (replaces) the entirety of chapters.
     *
     * @param array<string, string> $chapters Array of name => content
     *
     * @return $this
     */
    public function chapters(array $chapters): self
    {
        Validators::chapters($chapters)->validate();

        $this->chapters = $chapters;

        return $this;
    }

    /**
     * @throws ValidationFailure When $name already exists
     *
     * @return $this
     */
    public function addChapter(string $name, string $content): self
    {
        if (array_key_exists($name, $this->chapters)) {
            throw new ValidationFailure("Chapter '{$name}' already exists");
        }

        $this->chapters[$name] = $content;

        return $this;
    }

    /**
     * @throws ValidationFailure When given an invalid color
     *
     * @return $this
     */
    public function backgroundColor(string $color): self
    {
        $this->backgroundColor = $this->sanitizeColor($color);

        return $this;
    }

    /**
     * @throws ValidationFailure When given an invalid color
     *
     * @return $this
     */
    public function textColor(string $color): self
    {
        $this->textColor = $this->sanitizeColor($color);

        return $this;
    }

    /**
     * Set custom css.
     *
     * Replaces the default, and thus the backgroundColor and
     * textColor properties will have no more effect.
     *
     * @return $this
     */
    public function css(?string $css): self
    {
        $this->css = $css;

        return $this;
    }

    /**
     * Warning: existing files will be truncated and fully overwritten!
     *
     * @throws ValidationFailure When given file cannot be opened/created
     *
     * @return $this
     */
    public function file(string $filename): self
    {
        Validators::writable($filename)->validate();

        $this->file = $filename;

        return $this;
    }

    /**
     * Clean up after itself.
     *
     * This will delete the temporary file when totally done. Default behaviour.
     *
     * @return $this
     */
    public function cleanup(): self
    {
        $this->cleanup = true;

        return $this;
    }

    /**
     * Do NOT clean up after itself.
     *
     * The temporary file will remain.
     *
     * @return $this
     */
    public function noCleanup(): self
    {
        $this->cleanup = false;

        return $this;
    }

    /**
     * @throws BuildFailure
     */
    public function build(): Epub
    {
        if (empty($this->chapters)) {
            throw new BuildFailure("No content added, not building");
        }

        $file = $this->file ?? tempnam(sys_get_temp_dir(), 'zip');

        if ($file === false) {
            throw new BuildFailure("Cannot create a temporary file: either specify a file or make sure the temp dir is usable");
        }

        $zip = new ZipWrapper(cleanup: $this->cleanup);
        $zip->start($file);

        // Meta HAS TO BE the first thing in the file
        $this->addMeta($zip);
        $this->addContent($zip);

        $zip->finish();

        return new Epub($zip, $this->title);
    }

    private function addMeta(ZipWrapper $zip): void
    {
        // This mimetype file HAS TO BE the first thing in the zip
        $zip->addFromString("mimetype", "application/epub+zip");

        $zip->addFromString(
            "META-INF/container.xml",
            <<<XML
                <?xml version="1.0"?>
                <container xmlns="urn:oasis:names:tc:opendocument:xmlns:container" version="1.0">
                    <rootfiles>
                        <rootfile full-path="content/content.opf" media-type="application/oebps-package+xml"/>
                    </rootfiles>
                </container>
                XML,
        );
    }

    private function addContent(ZipWrapper $zip): void
    {
        $items = '';
        $itemrefs = '';
        $nav = '';

        foreach ($this->chapters as $name => $value) {
            $sanitizedName = preg_replace('/[^\w\-]/u', '_', $name);

            if ($sanitizedName === null) {
                throw new BuildFailure("Failed sanitizing content identifier '{$name}'");
            }

            $file = "{$sanitizedName}.xhtml";

            $items .= <<<XML
                <item id="{$sanitizedName}" href="{$file}" media-type="application/xhtml+xml"/>
                XML;

            $itemrefs .= <<<XML
                <itemref idref="{$sanitizedName}"/>
                XML;

            $nav .= <<<XML
                <li><a href="{$file}">{$name}</a></li>
                XML;

            $zip->addFromString(
                "content/{$file}",
                <<<HTML
                    <html xmlns="http://www.w3.org/1999/xhtml">
                        <head>
                            <title>{$name}</title>
                            <link rel="stylesheet" type="text/css" href="page.css"/>
                        </head>
                        <body>{$value}</body>
                    </html>
                    HTML,
            );
        }

        $items .= <<<XML
            <item id="toc" href="toc.xhtml" media-type="application/xhtml+xml" properties="nav"/>
            <item id="css" href="page.css" media-type="text/css"/>
            XML;

        $zip->addFromString(
            "content/toc.xhtml",
            <<<HTML
                <html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops">
                    <head>
                        <title>TOC</title>
                        <link rel="stylesheet" type="text/css" href="page.css"/>
                    </head>
                    <body>
                        <nav epub:type="toc"><ol>{$nav}</ol></nav>
                    </body>
                </html>
                HTML,
        );

        /*
         * This styling is an attempt to use as much space as Apple's iPhone app "Books" allows.
         * As I have no ereader, I don't know how it behaves on other readers, sorry.
         */
        $css = $this->css ?? <<<CSS
            @page {
                margin: 0;
                padding: 0;
            }
            body {
                margin: 0;
                padding: 0;
                color: {$this->textColor};
                background-color: {$this->backgroundColor};
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

        $zip->addFromString('content/page.css', $css);

        $zip->addFromString(
            'content/content.opf',
            <<<XML
                <?xml version="1.0" encoding="UTF-8"?>
                <package xmlns="http://www.idpf.org/2007/opf" xmlns:opf="http://www.idpf.org/2007/opf" version="3.0" unique-identifier="BookID">
                    <metadata xmlns:dc="http://purl.org/dc/elements/1.1/">
                        <dc:identifier id="BookID">{$this->identifier}</dc:identifier>
                        <dc:title>{$this->title}</dc:title>
                        <dc:language>{$this->language}</dc:language>
                        <dc:publisher>{$this->publisher}</dc:publisher>
                        <dc:creator>{$this->author}</dc:creator>
                        <dc:description>{$this->description}</dc:description>
                        <meta property="dcterms:modified">{$this->modified}</meta>
                    </metadata>
                    <manifest>
                        {$items}
                    </manifest>
                    <spine>
                        {$itemrefs}
                    </spine>
                </package>
                XML,
        );
    }

    /**
     * @throws ValidationFailure When given an invalid color
     */
    private function sanitizeColor(string $color): string
    {
        $hashColor = str_starts_with($color, '#')
            ? $color
            : '#' . $color;

        Validators::color($hashColor)->validate();

        return $hashColor;
    }
}
