<?php
declare(strict_types=1);

use Crustum\Meta\HeadBuilder;
use Crustum\Meta\HeadData;
use Crustum\Meta\HeadManager;
use Crustum\Meta\Schema\SchemaFactory;
use Crustum\Meta\Schema\SchemaObject;
use Crustum\Meta\Tags\Schemas;
use Crustum\Meta\Tags\Title;

it('builds head data without the container', function (): void {
    $data = new HeadData();

    (new HeadBuilder($data))
        ->title('About')
        ->description('About Acme.')
        ->schema(fn(SchemaFactory $schema): SchemaObject => $schema->make('webPage')->set('name', 'About'));

    expect($data->isEmpty())->toBeFalse();
    expect($data->get(Title::class))->not->toBeNull();
    expect($data->get(Schemas::class))->not->toBeNull();
});

it('applies conditional definitions with when and unless', function (): void {
    $data = new HeadData();

    (new HeadBuilder($data))
        ->when(true, fn(HeadBuilder $head): HeadBuilder => $head->title('Conditional'))
        ->unless(true, fn(HeadBuilder $head): HeadBuilder => $head->description('Skipped'));

    expect($data->get(Title::class))->not->toBeNull();
    expect($data->isEmpty())->toBeFalse();
});

it('supports conditional runtime definitions through the manager', function (): void {
    $head = $this->head();

    $head->when(true, fn(HeadManager $head): HeadManager => $head->title('Conditional'))
        ->unless(true, fn(HeadManager $head): HeadManager => $head->description('Skipped'));

    expect($head->toHtml())
        ->toContain('<title>Conditional</title>')
        ->not->toContain('Skipped');
});

it('receives a head builder in defaults callbacks', function (): void {
    $head = $this->head();

    $head->defaults(function (HeadBuilder $head): void {
        $head->title('Acme', suffix: ' - Acme');
    });

    $head->title('About');

    expect($head->toHtml())->toContain('<title>About - Acme</title>');
});

it('chains runtime definitions into rendering', function (): void {
    $head = $this->head();

    $html = $head->title('About')
        ->description('About Acme.')
        ->toHtml();

    expect($html)
        ->toContain('<title>About</title>')
        ->toContain('<meta name="description" content="About Acme.">');
});
