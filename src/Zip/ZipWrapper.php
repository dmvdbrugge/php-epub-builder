<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Zip;

use ZipArchive;

class ZipWrapper
{
    private bool $finished = false;
    private bool $started = false;

    private string $file;

    public function __construct(
        private readonly ZipArchive $zip = new ZipArchive(),
        private readonly bool $cleanup = true,
    ) {
    }

    public function __destruct()
    {
        if ($this->cleanup && isset($this->file) && file_exists($this->file)) {
            unlink($this->file);
        }
    }

    public function start(string $file): void
    {
        if ($this->started) {
            throw new BadMethodCall("Cannot start an already started Epub");
        }

        if ($this->finished) {
            throw new BadMethodCall("Cannot start an already finished Epub");
        }

        $opened = $this->zip->open($file, ZipArchive::CREATE);

        if ($opened !== true) {
            $reason = $opened === false ? '' : ": error code {$opened}";

            throw new BuildFailure("Failed opening or creating underlying Zip{$reason}");
        }

        $this->file = $file;
        $this->started = true;
    }

    public function addFromString(string $name, string $content): void
    {
        if ($this->finished) {
            throw new BadMethodCall("Cannot add to a finished Epub");
        }

        if (!$this->started) {
            throw new BadMethodCall("Cannot add to an unstarted Epub");
        }

        if (!$this->zip->addFromString($name, $content)) {
            throw new BuildFailure("Failed adding content '{$name}' to underlying Zip");
        }
    }

    public function finish(): void
    {
        if ($this->finished) {
            throw new BadMethodCall("Cannot finish an already finished Epub");
        }

        if (!$this->started) {
            throw new BadMethodCall("Cannot finish an unstarted Epub");
        }

        if (!$this->zip->close()) {
            throw new BuildFailure("Failed closing underlying Zip");
        }

        $this->started = false;
        $this->finished = true;
    }

    public function getFileName(): string
    {
        if (!$this->started && !$this->finished) {
            throw new BadMethodCall("An unstarted Epub has no file name");
        }

        return $this->file;
    }

    public function getFileSize(): int
    {
        if (!$this->started && !$this->finished) {
            throw new BadMethodCall("An unstarted Epub has no file size");
        }

        $size = filesize($this->file);

        if ($size === false) {
            throw new BuildFailure("Failed determining the file size of the Epub");
        }

        return $size;
    }
}
