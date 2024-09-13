<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Tests\Zip;

use ZipArchive;

/**
 * By default, all methods succeed. Change the *Return values to change behaviour.
 */
class ZipArchiveStub extends ZipArchive
{
    /** true = success, false = general failure, int = specific failure */
    public bool | int $openReturn = true;
    public bool $addFromStringReturn = true;
    public bool $closeReturn = true;

    /** @var array<string, string> FileName => Content */
    private array $content = [];

    public function open(string $filename, ?int $flags = null): bool | int
    {
        return $this->openReturn;
    }

    public function addFromString(string $name, string $content, int $flags = ZipArchive::FL_OVERWRITE): bool
    {
        if ($this->addFromStringReturn) {
            $this->content[$name] = $content;
        }

        return $this->addFromStringReturn;
    }

    public function close(): bool
    {
        return $this->closeReturn;
    }

    /**
     * @return array<string, string> FileName => Content
     */
    public function getContent(): array
    {
        return $this->content;
    }
}
