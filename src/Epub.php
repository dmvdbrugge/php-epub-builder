<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder;

use DMvdBrugge\EpubBuilder\Zip\ZipWrapper;

/**
 * Representation of the built Epub.
 *
 * Supposed to be built with the {@see EpubBuilder}.
 */
class Epub
{
    public function __construct(
        private readonly ZipWrapper $zip,
        private readonly string $title,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function getHttpHeaders(): array
    {
        return [
            'Content-Type' => 'application/epub+zip',
            'Content-Length' => (string)$this->getFileSize(),
            'Content-Disposition' => "attachment; filename=\"{$this->title}.epub\"",
        ];
    }

    public function getFileName(): string
    {
        return $this->zip->getFileName();
    }

    public function getFileSize(): int
    {
        return $this->zip->getFileSize();
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
