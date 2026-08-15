<?php
declare(strict_types=1);

use Crustum\Meta\Enums\ImageType;
use Crustum\Meta\Enums\OgType;
use Crustum\Meta\Enums\TwitterCard;
use Crustum\Meta\Routing\RouteAttributeParser;

it('parses route head data using the fluent field shapes', function (): void {
    $head = $this->head();

    $this->route('/product', RouteAttributeParser::metadata([
        'title' => ['value' => 'Product', 'suffix' => ' - Store'],
        'canonical' => ['auto' => true, 'forceHttps' => false],
        'og' => ['type' => OgType::Website, 'image' => 'https://example.com/og.jpg'],
        'ogImage' => [
            ['url' => 'https://example.com/structured.jpg', 'alt' => 'Structured image', 'width' => 1200, 'type' => ImageType::Jpeg],
        ],
        'twitter' => ['card' => TwitterCard::SummaryWithLargeImage],
        'twitterImage' => ['url' => 'https://example.com/twitter.jpg', 'alt' => 'Twitter image'],
        'meta' => ['product:price:amount' => '99.00'],
        'link' => [
            ['rel' => 'manifest', 'href' => '/manifest.json'],
        ],
    ]));

    expect($head->toHtml())
        ->toContain('<title>Product - Store</title>')
        ->toContain('<link rel="canonical" href="http://localhost/product">')
        ->toContain('<meta property="og:type" content="website">')
        ->toContain('property="og:image" content="https://example.com/og.jpg">')
        ->toContain('property="og:image:alt" content="Structured image">')
        ->toContain('property="og:image:type" content="image/jpeg">')
        ->toContain('property="og:image:width" content="1200">')
        ->toContain('<meta name="twitter:card" content="summary_large_image">')
        ->toContain('<meta name="twitter:image:alt" content="Twitter image">')
        ->toContain('<meta property="product:price:amount" content="99.00">')
        ->toContain('rel="manifest" href="/manifest.json">');
});

it('does not accept snake case route head data aliases', function (): void {
    $head = $this->head();

    $this->route('/legacy-inputs', RouteAttributeParser::metadata([
        'canonical' => ['auto' => true, 'force_https' => false],
        'og' => ['title' => 'Legacy', 'site_name' => 'Legacy'],
        'ogImage' => [
            ['url' => 'https://example.com/image.jpg', 'secure_url' => 'https://secure.example.com/image.jpg'],
        ],
    ]));

    expect($head->toHtml())
        ->toContain('<link rel="canonical" href="https://localhost/legacy-inputs">')
        ->toContain('property="og:image" content="https://example.com/image.jpg">')
        ->not->toContain('<meta property="og:site_name" content="Legacy">')
        ->not->toContain('property="og:image:secure_url" content="https://secure.example.com/image.jpg">');
});

it('parses single repeatable route head data values', function (): void {
    $head = $this->head();

    $this->route('/assets', RouteAttributeParser::metadata([
        'preload' => ['href' => '/fonts/inter.woff2', 'as' => 'font', 'crossorigin' => true],
        'prefetch' => '/images/next.webp',
        'preconnect' => ['href' => 'https://fonts.example.com', 'crossorigin' => true],
        'dnsPrefetch' => 'https://analytics.example.com',
        'feed' => ['href' => '/feed.atom', 'title' => 'Acme Atom', 'type' => 'atom'],
    ]));

    expect($head->toHtml())
        ->toContain('rel="preload" href="/fonts/inter.woff2" as="font" crossorigin>')
        ->toContain('rel="prefetch" href="/images/next.webp">')
        ->toContain('rel="preconnect" href="https://fonts.example.com" crossorigin>')
        ->toContain('rel="dns-prefetch" href="https://analytics.example.com">')
        ->toContain('rel="alternate" type="application/atom+xml" title="Acme Atom" href="/feed.atom">');
});

it('cascades layered route metadata onto the head', function (): void {
    $head = $this->head();

    $this->route('/admin/dashboard', [
        'head' => [
            'layer-0' => ['description' => 'Admin description.', 'robots' => 'noindex, nofollow'],
            'layer-1' => ['title' => 'Dashboard'],
        ],
    ]);

    expect($head->toHtml())
        ->toContain('<title>Dashboard</title>')
        ->toContain('<meta name="description" content="Admin description.">')
        ->toContain('<meta name="robots" content="noindex, nofollow">');
});

it('throws for unknown route head data keys', function (): void {
    $head = $this->head();

    $this->route('/unknown-head-key', RouteAttributeParser::metadata(['heading' => 'Dashboard']));

    $head->toHtml();
})->throws(InvalidArgumentException::class, 'Unknown route head attribute [heading].');

it('throws for invalid values on known route head data keys', function (): void {
    $head = $this->head();

    $this->route('/invalid-head-value', RouteAttributeParser::metadata(['ogImage' => ['alt' => 'Missing URL']]));

    $head->toHtml();
})->throws(InvalidArgumentException::class, 'Invalid value for route head attribute [ogImage].');

it('throws when route head data tries to suppress canonical URLs', function (): void {
    $head = $this->head();

    $this->route('/invalid-canonical', RouteAttributeParser::metadata(['canonical' => false]));

    $head->toHtml();
})->throws(InvalidArgumentException::class, 'Invalid value for route head attribute [canonical].');

it('throws when route canonical data uses the removed none option', function (): void {
    $head = $this->head();

    $this->route('/invalid-canonical-none', RouteAttributeParser::metadata(['canonical' => ['none' => true]]));

    $head->toHtml();
})->throws(InvalidArgumentException::class, 'Invalid value for route head attribute [canonical].');
