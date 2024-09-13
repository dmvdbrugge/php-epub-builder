<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Zip;

use DMvdBrugge\EpubBuilder\File\File;
use DMvdBrugge\EpubBuilder\File\FileFailure;
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
        if ($this->cleanup && isset($this->file) && File::exists($this->file)) {
            File::delete($this->file);
        }
    }

    /**
     * @throws BadMethodCall When using the class incorrect
     * @throws BuildFailure  When unable to start
     */
    public function start(string $file): void
    {
        if ($this->started) {
            throw new BadMethodCall("Cannot start an already started Epub");
        }

        if ($this->finished) {
            throw new BadMethodCall("Cannot start an already finished Epub");
        }

        // Empty files are invalid archives, so overwrite them, they're empty anyway.
        $flags = File::empty($file) ? ZipArchive::OVERWRITE : ZipArchive::CREATE;
        $opened = $this->zip->open($file, $flags);

        if ($opened !== true) {
            $reason = $opened === false ? '' : ": error code {$opened}";

            throw new BuildFailure("Failed opening or creating underlying Zip{$reason}");
        }

        $this->file = $file;
        $this->started = true;
    }

    /**
     * @throws BadMethodCall When using the class incorrect
     * @throws BuildFailure  When unable to add content
     */
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

    /**
     * @throws BadMethodCall When using the class incorrect
     * @throws BuildFailure  When unable to finish
     */
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

    /**
     * @throws BadMethodCall When using the class incorrect
     */
    public function getFileName(): string
    {
        if (!$this->started && !$this->finished) {
            throw new BadMethodCall("An unstarted Epub has no file name");
        }

        return $this->file;
    }

    /**
     * Note: there is no guarantee the file has a size until after {@see self::finish()}.
     *
     * @throws BadMethodCall When using the class incorrect
     * @throws FileFailure   When unable to determine the size
     */
    public function getFileSize(): int
    {
        if (!$this->started && !$this->finished) {
            throw new BadMethodCall("An unstarted Epub has no file size");
        }

        return File::size($this->file);
    }
}
