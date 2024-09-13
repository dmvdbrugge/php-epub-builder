<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Tests\Zip;

use DMvdBrugge\EpubBuilder\File\File;
use DMvdBrugge\EpubBuilder\Zip\BadMethodCall;
use DMvdBrugge\EpubBuilder\Zip\BuildFailure;
use DMvdBrugge\EpubBuilder\Zip\ZipWrapper;
use PHPUnit\Framework\TestCase;

class ZipWrapperTest extends TestCase
{
    public function testHappyPath(): void
    {
        $file = File::temp('zip-test-');
        $wrapper = new ZipWrapper();
        $wrapper->start($file);

        self::assertSame($file, $wrapper->getFileName());
        self::assertTrue(File::empty($file));

        $wrapper->addFromString('dir1/file.txt', 'I haz text');
        $wrapper->addFromString('dir2/file1.txt', 'And so do I!');
        $wrapper->addFromString('dir2/info.php', '<?= phpinfo() ?>');
        $wrapper->finish();

        // Finishing shouldn't have changed filename
        self::assertSame($file, $wrapper->getFileName());
        self::assertFileExists($file);

        // It's 368 on my machine but I don't know if that's guaranteed: ~10% margin
        self::assertGreaterThan(330, $wrapper->getFileSize());

        unset($wrapper);

        self::assertFileDoesNotExist($file);
    }

    public function testCallOrderIsEnforced(): void
    {
        $wrapper = new ZipWrapper();

        $this->assertBadMethodCall(
            "Cannot add to an unstarted Epub",
            fn () => $wrapper->addFromString('file', 'content'),
        );

        $this->assertBadMethodCall(
            "Cannot finish an unstarted Epub",
            fn () => $wrapper->finish(),
        );

        $this->assertBadMethodCall(
            "An unstarted Epub has no file name",
            fn () => $wrapper->getFileName(),
        );

        $this->assertBadMethodCall(
            "An unstarted Epub has no file size",
            fn () => $wrapper->getFileSize(),
        );

        $wrapper->start(File::temp('zip-test'));

        $this->assertBadMethodCall(
            "Cannot start an already started Epub",
            fn () => $wrapper->start(''),
        );

        $wrapper->finish();

        $this->assertBadMethodCall(
            "Cannot start an already finished Epub",
            fn () => $wrapper->start(''),
        );

        $this->assertBadMethodCall(
            "Cannot add to a finished Epub",
            fn () => $wrapper->addFromString('file', 'content'),
        );

        $this->assertBadMethodCall(
            "Cannot finish an already finished Epub",
            fn () => $wrapper->finish(),
        );
    }

    public function testZipErrorsResultInBuildFailureWithoutChangingState(): void
    {
        $zipStub = new ZipArchiveStub();
        $wrapper = new ZipWrapper($zipStub, false);

        $zipStub->openReturn = 42;
        $this->assertBuildFailure(
            "Failed opening or creating underlying Zip: error code 42",
            fn () => $wrapper->start('fail-1'),
        );

        $zipStub->openReturn = false;
        $this->assertBuildFailure(
            "Failed opening or creating underlying Zip",
            fn () => $wrapper->start('fail-2'),
        );

        $zipStub->openReturn = true;
        $wrapper->start("succeed");

        $zipStub->addFromStringReturn = false;
        $this->assertBuildFailure(
            "Failed adding content 'failed/file' to underlying Zip",
            fn () => $wrapper->addFromString('failed/file', 'This will not end up in the zip'),
        );

        $zipStub->addFromStringReturn = true;
        $wrapper->addFromString('files/succes-1', 'hurrah');
        $wrapper->addFromString('files/succes-2', 'yay!');

        $zipStub->closeReturn = false;
        $this->assertBuildFailure(
            "Failed closing underlying Zip",
            fn () => $wrapper->finish(),
        );

        $zipStub->closeReturn = true;
        $wrapper->finish();

        self::assertSame("succeed", $wrapper->getFileName());

        // This is actually testing the stub-implementation
        self::assertSame([
            'files/succes-1' => 'hurrah',
            'files/succes-2' => 'yay!',
        ], $zipStub->getContent());
    }

    private function assertBadMethodCall(string $message, callable $fn): void
    {
        $this->expectException(BadMethodCall::class);
        $this->expectExceptionMessage($message);

        $fn();
    }

    private function assertBuildFailure(string $message, callable $fn): void
    {
        $this->expectException(BuildFailure::class);
        $this->expectExceptionMessage($message);

        $fn();
    }
}
