<?php
declare(strict_types=1);

use Crustum\Meta\Enums\OfferAvailability;
use Crustum\Meta\Schema\SchemaFactory;
use Crustum\Meta\Test\Fixtures\JobPosting;

it('renders built in schema objects as JSON LD', function (): void {
    $head = $this->head();
    $schema = new SchemaFactory();

    $head->schema(
        $schema->article()
            ->headline('Introducing Crustum Meta')
            ->description('A fluent API for the document head.')
            ->author($schema->person()->name('Evgeny Tomenko'))
            ->publishedAt('2026-05-13')
            ->modifiedAt('2026-05-14'),
    );

    expect($head->toHtml())
        ->toContain('<script type="application/ld+json">')
        ->toContain('"@context":"https://schema.org"')
        ->toContain('"@type":"Article"')
        ->toContain('"description":"A fluent API for the document head."')
        ->toContain('"@type":"Person"')
        ->toContain('"datePublished":"2026-05-13"')
        ->toContain('"dateModified":"2026-05-14"');
});

it('escapes html sensitive characters in JSON LD to prevent script breakout', function (): void {
    $head = $this->head();
    $schema = new SchemaFactory();

    $head->schema($schema->article()->headline('</script><script>alert(1)</script>'));

    expect($head->toHtml())
        ->not->toContain('</script><script>alert(1)</script>')
        ->toContain('\\u003C/script\\u003E\\u003Cscript\\u003Ealert(1)\\u003C/script\\u003E');
});

it('resolves factory methods for classes whose schema type differs from the class name', function (): void {
    $head = $this->head();
    $schema = new SchemaFactory();

    $head->schema(
        $schema->breadcrumbs()
            ->item('Home', 'https://example.com')
            ->item('Shop', 'https://example.com/shop'),
    )->schema(
        $schema->faq()->question('What is Crustum Meta?', 'A fluent API for managing the document head.'),
    );

    expect($head->toHtml())
        ->toContain('"@type":"BreadcrumbList"')
        ->toContain('"itemListElement"')
        ->toContain('"item":"https://example.com/shop"')
        ->toContain('"@type":"FAQPage"')
        ->toContain('"@type":"Question"')
        ->toContain('"@type":"Answer"');
});

it('sets breadcrumb items in bulk with sequential positions', function (): void {
    $head = $this->head();
    $schema = new SchemaFactory();

    $head->schema(
        $schema->breadcrumbs()
            ->item('Home', 'https://example.com')
            ->items([
                'Shop' => 'https://example.com/shop',
                'Shoes' => 'https://example.com/shop/shoes',
            ]),
    );

    expect($head->toHtml())
        ->toContain('"@type":"BreadcrumbList"')
        ->toContain('"position":1,"name":"Home"')
        ->toContain('"position":2,"name":"Shop"')
        ->toContain('"position":3,"name":"Shoes"')
        ->toContain('"item":"https://example.com/shop/shoes"');
});

it('sets faq questions in bulk', function (): void {
    $head = $this->head();
    $schema = new SchemaFactory();

    $head->schema($schema->faq()->questions([
        'What is Crustum Meta?' => 'A fluent API for managing the document head.',
        'Is it free?' => 'Yes, it is open source.',
    ]));

    expect($head->toHtml())
        ->toContain('"@type":"FAQPage"')
        ->toContain('"name":"What is Crustum Meta?"')
        ->toContain('"name":"Is it free?"')
        ->toContain('"text":"Yes, it is open source."');
});

it('registers custom schema types as first class factory methods', function (): void {
    $head = $this->head();
    $schema = new SchemaFactory();

    $schema->register(JobPosting::class);

    $head->schema(
        $schema->jobPosting()
            ->title('Senior CakePHP Developer')
            ->datePosted('2026-05-13'),
    );

    expect($head->toHtml())
        ->toContain('"@type":"JobPosting"')
        ->toContain('"title":"Senior CakePHP Developer"')
        ->toContain('"datePosted":"2026-05-13"');
});

it('renders the event schema object as JSON LD', function (): void {
    $head = $this->head();
    $schema = new SchemaFactory();

    $head->schema(
        $schema->event()
            ->name('Crustum Meta Launch')
            ->description('Live product launch event.')
            ->startDate('2026-09-01T19:00:00+01:00')
            ->endDate('2026-09-01T21:00:00+01:00')
            ->organizer($schema->organization()->name('CakePHP'))
            ->offers($schema->offer()->price(0)->currency('USD')),
    );

    expect($head->toHtml())
        ->toContain('"@type":"Event"')
        ->toContain('"name":"Crustum Meta Launch"')
        ->toContain('"startDate":"2026-09-01T19:00:00+01:00"')
        ->toContain('"endDate":"2026-09-01T21:00:00+01:00"')
        ->toContain('"@type":"Organization"')
        ->toContain('"@type":"Offer"');
});

it('sets offer availability from schema org enum values', function (): void {
    $head = $this->head();
    $schema = new SchemaFactory();

    $head->schema(
        $schema->product()
            ->name('Desk')
            ->brand($schema->brand()->name('Acme'))
            ->offers(
                $schema->offer()
                    ->price(125)
                    ->currency('USD')
                    ->availability(OfferAvailability::InStock),
            ),
    );

    expect($head->toHtml())
        ->toContain('"brand":{"@type":"Brand","name":"Acme"}')
        ->toContain('"priceCurrency":"USD"')
        ->toContain('"availability":"https://schema.org/InStock"');
});

it('includes all schema org item availability values', function (): void {
    expect(OfferAvailability::MadeToOrder->url())->toBe('https://schema.org/MadeToOrder')
        ->and(OfferAvailability::Reserved->url())->toBe('https://schema.org/Reserved');
});

it('throws when a magic schema property setter receives multiple arguments', function (): void {
    (new SchemaFactory())->make('videoObject')->name('Introducing Crustum Meta', 'https://example.com/video');
})->throws(
    BadMethodCallException::class,
    'Magic schema property setters accept a single value, and [Crustum\Meta\Schema\GenericSchemaObject] does not define a [name] method.',
);

it('makes schema objects directly from class names without registration', function (): void {
    $head = $this->head();
    $schema = new SchemaFactory();

    $head->schema(
        $schema->make(JobPosting::class)
            ->title('Senior CakePHP Developer')
            ->datePosted('2026-05-13'),
    );

    expect($head->toHtml())
        ->toContain('"@type":"JobPosting"')
        ->toContain('"title":"Senior CakePHP Developer"');
});
