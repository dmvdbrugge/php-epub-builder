<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder;

use DMvdBrugge\EpubBuilder\File\File;
use DMvdBrugge\EpubBuilder\File\FileFailure;
use DMvdBrugge\EpubBuilder\Zip\ZipWrapper;

use function mb_ereg_replace;
use function trim;

/**
 * Representation of a built Epub.
 *
 * Supposed to be built with the {@see EpubBuilder}.
 */
class Epub
{
    private readonly string $fileName;

    public function __construct(
        private readonly ZipWrapper $zip,
        private readonly string $title,
    ) {
        $this->fileName = $this->sanitizeFileName($title);
    }

    /**
     * @return array<string, string>
     */
    public function getHttpHeaders(): array
    {
        return [
            'Content-Type' => 'application/epub+zip',
            'Content-Length' => (string)$this->getFileSize(),
            'Content-Disposition' => "attachment; filename=\"{$this->fileName}\"",
        ];
    }

    /**
     * @throws FileFailure When unable to get a handle
     *
     * @return resource This is yours now, don't forget to {@see File::close()} it
     */
    public function getFileHandle()
    {
        return File::open($this->getFileOnDisk());
    }

    /**
     * The current file, full path.
     */
    public function getFileOnDisk(): string
    {
        return $this->zip->getFileName();
    }

    /**
     * What it wants to be called, file only.
     */
    public function getFileName(): string
    {
        return $this->fileName;
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

    private function sanitizeFileName(string $fileName): string
    {
        // Adapted from https://stackoverflow.com/questions/2021624/string-sanitizer-for-filename#answer-42058764
        $regex = "[<>:\"/\\\\|?*]|[\u{00}-\u{1F}]|[\u{7F}\u{A0}\u{AD}]";

        $fileName = mb_ereg_replace($regex, '', $fileName);
        $fileName = is_string($fileName) ? trim($fileName, ' .') : '';

        if ($fileName === '') {
            $fileName = 'epub';
        }

        return "{$fileName}.epub";
    }
}
