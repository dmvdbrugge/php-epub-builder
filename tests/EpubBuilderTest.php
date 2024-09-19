<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder;

use DMvdBrugge\EpubBuilder\Content\ChapterContent;
use DMvdBrugge\EpubBuilder\File\File;
use PHPUnit\Framework\TestCase;
use ZipArchive;

class EpubBuilderTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $file = self::testfile();

        if (file_exists($file)) {
            self::assertTrue(unlink($file), "Failed removing file that shouldn't exist before test");
        }
    }

    public static function tearDownAfterClass(): void
    {
        $file = self::testfile();

        self::assertFileExists($file);
        self::assertTrue(unlink($file), "Failed cleaning up epub test file");
    }

    public function testReadmeExample(): void
    {
        // Step 1: Get your data from somewhere
        $chapters = [
            // Title => Content; Anything goes for Content, as long as it's valid xhtml when added to a <body>
            "The Early Years" => "<h1>The Early Years</h1><p>I was youg and naive (...)"
                . "and that's how I ended up with my best friend.</p>",
            "The One that Got Away" => "<h1>The One that Got Away</h1><p>A lot of people have one,"
                . "(...) I still wonder sometimes, what if...</p>",
            "Death and After" => "<h1>Death and After</h1><p>When I die (...) and that's that.</p>",
        ];

        $lastModified = \DateTimeImmutable::createFromFormat(DATE_ATOM, "2024-09-16T05:31:42+02:00");
        self::assertNotFalse($lastModified); // This line is not in the readme

        // Step 2: Pass your data into an EpubBuilder and ->build() it
        $epub = (new EpubBuilder())
            ->author("Me! Or You!")
            ->description("Life Stories to Learn From; or Not. You decide! This is the story of my life.")
            ->isbn("0-553-10354-7") // Not required but should be valid when provided
            ->identifier("my-awesome-website.com:book:9371") // Optional when ISBN provided
            ->language("en") // IETF language tag
            ->modified($lastModified)
            ->publisher("My Awesome Website")
            ->title("Life Stories to Learn From; or Not.")
            ->chapters($chapters)
            ->build();

        // Step 3: Save somewhere...
        copy($epub->getFileOnDisk(), sys_get_temp_dir() . "/{$epub->getFileName()}"); // Changed from readme

        // Example cut-off

        $file = self::testfile();
        self::assertFileExists($file);

        $zip = new ZipArchive();
        self::assertTrue($zip->open($file, ZipArchive::RDONLY));

        // Number of files: mimetype, meta, 3 chapters, toc, css, container
        self::assertCount(8, $zip);

        // This MUST BE the very first file
        self::assertSame('mimetype', $zip->getNameIndex(0));

        // Sample
        $chapter = 'The One that Got Away';
        self::assertSame(
            ChapterContent::file($chapter, $chapters[$chapter]),
            $zip->getFromName('content/The_One_that_Got_Away.xhtml'),
        );

        $zip->close();
        $headers = $epub->getHttpHeaders();

        // It's 2714 on my machine but I don't know if that's guaranteed: ~10% margin
        self::assertGreaterThan(2450, (int)$headers['Content-Length']);
        self::assertSame('attachment; filename="Life Stories to Learn From; or Not.epub"', $headers['Content-Disposition']);

        $handle = $epub->getFileHandle();
        self::assertIsResource($handle);
        File::close($handle);
    }

    private static function testfile(): string
    {
        return sys_get_temp_dir() . '/Life Stories to Learn From; or Not.epub';
    }
}
