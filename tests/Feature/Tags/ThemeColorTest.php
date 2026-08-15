<?php
declare(strict_types=1);

use Crustum\Meta\Enums\Media;
use Crustum\Meta\Routing\RouteAttributeParser;

it('renders theme color tags', function (): void {
    $head = $this->head();

    $head->themeColor('#0f172a');

    expect($head->toHtml())->toContain('<meta name="theme-color" content="#0f172a">');
});

it('renders media-specific theme colors from media enum values', function (): void {
    $head = $this->head();

    $head->themeColor('#ffffff', media: Media::Light)
        ->themeColor('#111827', media: Media::Dark);

    expect($head->toHtml())
        ->toContain('<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">')
        ->toContain('<meta name="theme-color" content="#111827" media="(prefers-color-scheme: dark)">');
});

it('provides common media query values', function (): void {
    expect(Media::Light->value)->toBe('(prefers-color-scheme: light)')
        ->and(Media::Dark->value)->toBe('(prefers-color-scheme: dark)')
        ->and(Media::Portrait->value)->toBe('(orientation: portrait)')
        ->and(Media::Landscape->value)->toBe('(orientation: landscape)');
});

it('resolves theme colors from route attributes', function (): void {
    $head = $this->head();

    $this->route('/product', RouteAttributeParser::metadata([
        'themeColor' => '#0f172a',
    ]));

    expect($head->toHtml())->toContain('<meta name="theme-color" content="#0f172a">');
});
