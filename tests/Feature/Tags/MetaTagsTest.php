<?php
declare(strict_types=1);

use Crustum\Meta\Enums\Media;
use Crustum\Meta\Routing\RouteAttributeParser;

it('renders generic meta tags', function (): void {
    $head = $this->head();

    $head->meta('format-detection', 'telephone=no')
        ->meta('article:author', 'Taylor Otwell')
        ->meta('weird:namespace', 'Value', property: true);

    expect($head->toHtml())
        ->toContain('<meta name="format-detection" content="telephone=no">')
        ->toContain('<meta property="article:author" content="Taylor Otwell">')
        ->toContain('<meta property="weird:namespace" content="Value">');
});

it('renders meta aliases', function (): void {
    $head = $this->head();

    $head->applicationName('Acme')
        ->colorScheme('light dark')
        ->referrer('strict-origin-when-cross-origin')
        ->viewport('width=device-width, initial-scale=1')
        ->appleWebAppTitle('Acme')
        ->webAppCapable()
        ->appleWebAppStatusBarStyle('black');

    expect($head->toHtml())
        ->toContain('<meta name="application-name" content="Acme">')
        ->toContain('<meta name="color-scheme" content="light dark">')
        ->toContain('<meta name="referrer" content="strict-origin-when-cross-origin">')
        ->toContain('<meta name="viewport" content="width=device-width, initial-scale=1">')
        ->toContain('<meta name="apple-mobile-web-app-title" content="Acme">')
        ->toContain('<meta name="mobile-web-app-capable" content="yes">')
        ->toContain('<meta name="apple-mobile-web-app-status-bar-style" content="black">');
});

it('renders media-specific meta tags', function (): void {
    $head = $this->head();

    $head->meta('theme-color', '#ffffff', media: '(prefers-color-scheme: light)')
        ->meta('theme-color', '#111827', media: '(prefers-color-scheme: dark)');

    expect($head->toHtml())
        ->toContain('<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">')
        ->toContain('<meta name="theme-color" content="#111827" media="(prefers-color-scheme: dark)">');
});

it('overlays meta tags by key property and media query', function (): void {
    $head = $this->head();

    $head->meta('theme-color', '#ffffff')
        ->meta('theme-color', '#111827', media: '(prefers-color-scheme: dark)')
        ->meta('theme-color', '#f8fafc');

    expect($head->toHtml())
        ->toContain('<meta name="theme-color" content="#f8fafc">')
        ->toContain('<meta name="theme-color" content="#111827" media="(prefers-color-scheme: dark)">')
        ->not->toContain('<meta name="theme-color" content="#ffffff">');
});

it('serializes media-specific meta tags to the head array', function (): void {
    $head = $this->head();

    $head->meta('theme-color', '#ffffff')
        ->meta('theme-color', '#111827', media: '(prefers-color-scheme: dark)');

    expect($head->toArray()['meta'])->toBe([
        ['key' => 'theme-color', 'content' => '#ffffff'],
        ['key' => 'theme-color', 'content' => '#111827', 'media' => '(prefers-color-scheme: dark)'],
    ]);
});

it('resolves media-specific meta tags from route attributes', function (): void {
    $head = $this->head();

    $this->route('/dashboard', RouteAttributeParser::metadata([
        'meta' => [
            ['key' => 'theme-color', 'content' => '#ffffff', 'media' => '(prefers-color-scheme: light)'],
            ['key' => 'theme-color', 'content' => '#111827', 'media' => '(prefers-color-scheme: dark)'],
        ],
    ]));

    expect($head->toHtml())
        ->toContain('<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">')
        ->toContain('<meta name="theme-color" content="#111827" media="(prefers-color-scheme: dark)">');
});

it('resolves media enum values from route attributes', function (): void {
    $head = $this->head();

    $this->route('/dashboard', RouteAttributeParser::metadata([
        'meta' => [
            ['key' => 'theme-color', 'content' => '#ffffff', 'media' => Media::Light],
            ['key' => 'theme-color', 'content' => '#111827', 'media' => Media::Dark],
        ],
    ]));

    expect($head->toHtml())
        ->toContain('<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">')
        ->toContain('<meta name="theme-color" content="#111827" media="(prefers-color-scheme: dark)">');
});

it('resolves meta aliases from route attributes', function (): void {
    $head = $this->head();

    $this->route('/settings', RouteAttributeParser::metadata([
        'applicationName' => 'Acme',
        'colorScheme' => 'light dark',
        'referrer' => 'strict-origin',
        'viewport' => 'width=device-width, initial-scale=1',
        'appleWebAppTitle' => 'Acme',
        'webAppCapable' => true,
        'appleWebAppStatusBarStyle' => 'black-translucent',
    ]));

    expect($head->toHtml())
        ->toContain('<meta name="application-name" content="Acme">')
        ->toContain('<meta name="color-scheme" content="light dark">')
        ->toContain('<meta name="referrer" content="strict-origin">')
        ->toContain('<meta name="viewport" content="width=device-width, initial-scale=1">')
        ->toContain('<meta name="apple-mobile-web-app-title" content="Acme">')
        ->toContain('<meta name="mobile-web-app-capable" content="yes">')
        ->toContain('<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">');
});
