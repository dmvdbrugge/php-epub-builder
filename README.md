# EPUB Builder

Easily transform your own content into EPUB files for reading on ereaders and book apps.

## Basic Usage

```php
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

// Step 2: Pass your data into an EpubBuilder and ->build() it
$epub = (new \DMvdBrugge\EpubBuilder\EpubBuilder())
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
copy($epub->getFileOnDisk(), "/home/user/books/{$epub->getFileName()}");

// ...or offer as download
// - Example 1: direct output
foreach ($epub->getHttpHeaders() as $header => $value) {
    header("{$header}: {$value}");
}

readfile($epub->getFileOnDisk());

// - Example 2: PSR-7 (f.e. Slim, $response exists)
foreach ($epub->getHttpHeaders() as $header => $value) {
    $response = $response->withHeader($header, $value);
}

$response = $response->withBody(new \Slim\Psr7\Stream($epub->getFileHandle()));

// Step 4: Go read your new EPUB
```

## Installation and Requirements

Installation is just a [composer](https://getcomposer.org/) call away.

```
composer require dmvdbrugge/epub-builder
```

**Notable requirements:** `ext-zip`, in the future probably also `ext-simplexml` to ensure valid xhtml.  
As with any composer project, [composer.json](composer.json) has the full requirements.

## Documentation

Full documentation, among other things, is still [TODO](TODO.md).

## License

[MIT License](LICENSE)

Copyright (c) 2024 Dave van der Brugge
