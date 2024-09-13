<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder;

use DateTimeInterface;
use DMvdBrugge\EpubBuilder\Content\ChapterContent;
use DMvdBrugge\EpubBuilder\Content\Content;
use DMvdBrugge\EpubBuilder\Content\CssContent;
use DMvdBrugge\EpubBuilder\Content\MetaContent;
use DMvdBrugge\EpubBuilder\Content\TocContent;
use DMvdBrugge\EpubBuilder\File\File;
use DMvdBrugge\EpubBuilder\File\FileFailure;
use DMvdBrugge\EpubBuilder\Validation\ValidationFailure;
use DMvdBrugge\EpubBuilder\Validation\Validators;
use DMvdBrugge\EpubBuilder\Zip\BuildFailure;
use DMvdBrugge\EpubBuilder\Zip\ZipWrapper;

use function array_key_exists;
use function preg_replace;
use function str_starts_with;

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
     * @throws ValidationFailure When the language is not a valid IETF Language Tag
     *
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
     * @throws ValidationFailure When malformed
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
     * @throws ValidationFailure When a chapter with the same name already exists
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
     * Let the EPUB clean up after itself.
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
     * Do NOT let the EPUB clean up after itself.
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
     * @throws FileFailure
     */
    public function build(): Epub
    {
        if (empty($this->chapters)) {
            throw new BuildFailure("No content added, not building");
        }

        $file = $this->file ?? File::temp('zip-');

        $zip = new ZipWrapper(cleanup: $this->cleanup);
        $zip->start($file);

        // Meta HAS TO BE the first thing in the file
        $this->addMeta($zip);
        $this->addContent($zip);

        $zip->finish();

        return new Epub($zip, $this->title);
    }

    /**
     * @throws BuildFailure
     */
    private function addMeta(ZipWrapper $zip): void
    {
        // This mimetype file HAS TO BE the first thing in the zip
        $zip->addFromString("mimetype", "application/epub+zip");
        $zip->addFromString("META-INF/container.xml", MetaContent::container());
    }

    /**
     * @throws BuildFailure
     */
    private function addContent(ZipWrapper $zip): void
    {
        $manifest = [];
        $spine = [];
        $nav = [];

        /*
         * Each chapter, becoming its own xhtml file, needs to be added to:
         * - the manifest;
         * - the spine;
         * - the navigation;
         * - the zip.
         */
        foreach ($this->chapters as $name => $value) {
            $sanitizedName = $this->sanitizeChapterName($name);
            $file = "{$sanitizedName}.xhtml";

            $manifest[] = Content::item($sanitizedName, $file);
            $spine[] = Content::itemref($sanitizedName);
            $nav[] = TocContent::nav($file, $name);

            $zip->addFromString("content/{$file}", ChapterContent::file($name, $value));
        }

        $manifest[] = Content::tocItem();
        $zip->addFromString("content/toc.xhtml", TocContent::file($nav));

        $manifest[] = Content::cssItem();
        $zip->addFromString(
            'content/page.css',
            $this->css ?? CssContent::file($this->textColor, $this->backgroundColor),
        );

        $zip->addFromString('content/content.opf', Content::file(
            $manifest,
            $spine,
            $this->identifier,
            $this->title,
            $this->language,
            $this->publisher,
            $this->author,
            $this->description,
            $this->modified,
        ));
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

    /**
     * @throws BuildFailure When unable to sanitize into non-reserved name
     */
    private function sanitizeChapterName(string $name): string
    {
        $result = preg_replace('/[^\w\-]/u', '_', $name);

        if ($result === null) {
            throw new BuildFailure("Failed sanitizing chapter name '{$name}'");
        }

        try {
            // It would be nice if we could do this before building
            Validators::chapterName($name, $result)->validate();
        } catch (ValidationFailure $e) {
            throw new BuildFailure($e->getMessage());
        }

        return $result;
    }
}
