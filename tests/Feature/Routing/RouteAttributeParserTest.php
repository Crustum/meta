<?php
declare(strict_types=1);

use Cake\Routing\Route\Route;
use Crustum\Meta\HeadData;
use Crustum\Meta\Routing\RouteAttributeParser;
use Crustum\Meta\TagRegistry;
use Crustum\Meta\Tags\Title;

it('unwraps a single array argument', function (): void {
    expect(RouteAttributeParser::arguments([['title' => 'About']]))->toBe(['title' => 'About']);
});

it('keeps multiple arguments as-is', function (): void {
    expect(RouteAttributeParser::arguments(['title' => 'About', 'description' => 'About Acme.']))
        ->toBe(['title' => 'About', 'description' => 'About Acme.']);
});

it('wraps head attributes in a metadata layer', function (): void {
    $metadata = RouteAttributeParser::metadata(['title' => 'About']);

    expect($metadata)->toHaveKey('head');
    expect(array_values($metadata['head']))->toBe([['title' => 'About']]);
});

it('wraps empty attributes into empty metadata', function (): void {
    expect(RouteAttributeParser::metadata([]))->toBe([]);
});

it('builds metadata from named arguments, discarding nulls', function (): void {
    $metadata = RouteAttributeParser::metadataFromArguments([
        'title' => 'About',
        'description' => null,
    ]);

    expect(array_values($metadata['head']))->toBe([['title' => 'About']]);
});

it('merges extension arguments into metadata', function (): void {
    $metadata = RouteAttributeParser::metadataFromArguments([
        'title' => 'Article',
        'extensions' => ['readingTime' => 4],
    ]);

    expect(array_values($metadata['head']))->toBe([['title' => 'Article', 'readingTime' => 4]]);
});

it('reads nothing from routes without head metadata', function (): void {
    $route = new Route('/about');

    expect(RouteAttributeParser::routeMetadata($route))->toBe([]);
});

it('reads layer-keyed metadata from a route', function (): void {
    $route = new Route('/about', [], ['head' => ['layer-0' => ['title' => 'About']]]);

    expect(RouteAttributeParser::routeMetadata($route))->toBe([['title' => 'About']]);
});

it('reads list metadata from a route', function (): void {
    $route = new Route('/about', [], ['head' => [['title' => 'About'], ['description' => 'About Acme.']]]);

    expect(RouteAttributeParser::routeMetadata($route))->toBe([
        ['title' => 'About'],
        ['description' => 'About Acme.'],
    ]);
});

it('reads a plain metadata map as a single layer', function (): void {
    $route = new Route('/about', [], ['head' => ['title' => 'About']]);

    expect(RouteAttributeParser::routeMetadata($route))->toBe([['title' => 'About']]);
});

it('applies named head attributes to head data', function (): void {
    $head = new HeadData();
    $registry = new TagRegistry();

    $head = RouteAttributeParser::apply($head, ['title' => 'About'], $registry);

    expect($head->get(Title::class))->not->toBeNull();
});

it('throws for unknown route head attribute keys', function (): void {
    $head = new HeadData();
    $registry = new TagRegistry();

    expect(fn(): HeadData => RouteAttributeParser::apply($head, ['heading' => 'Dashboard'], $registry))
        ->toThrow(InvalidArgumentException::class, 'Unknown route head attribute [heading].');
});

it('throws for invalid route head attribute values', function (): void {
    $head = new HeadData();
    $registry = new TagRegistry();

    expect(fn(): HeadData => RouteAttributeParser::apply($head, ['canonical' => false], $registry))
        ->toThrow(InvalidArgumentException::class, 'Invalid value for route head attribute [canonical].');
});
