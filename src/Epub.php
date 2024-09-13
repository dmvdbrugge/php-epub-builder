<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder;

use DMvdBrugge\EpubBuilder\File\File;
use DMvdBrugge\EpubBuilder\File\FileFailure;
use DMvdBrugge\EpubBuilder\Zip\ZipWrapper;

/**
 * Representation of a built Epub.
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

    /**
     * @throws FileFailure When unable to get a handle
     *
     * @return resource This is yours now, don't forget to {@see File::close()} it
     */
    public function getFileHandle()
    {
        return File::open($this->getFileName());
    }

    public function getFileName(): string
    {
        return $this->zip->getFileName();
    }

    /**
     * @throws FileFailure When unable to determine the size
     */
    public function getFileSize(): int
    {
        return $this->zip->getFileSize();
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
