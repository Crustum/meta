<?php
declare(strict_types=1);

use Crustum\Meta\Enums\TwitterCard;
use Crustum\Meta\HeadBuilder;

it('renders twitter image tags', function (): void {
    $head = $this->head();

    $head->twitter(card: TwitterCard::SummaryWithLargeImage, image: 'https://example.com/twitter.jpg')
        ->twitterImage('https://example.com/twitter-alt.jpg', alt: 'Twitter alt');

    expect($head->toHtml())
        ->toContain('<meta name="twitter:card" content="summary_large_image">')
        ->toContain('<meta name="twitter:image" content="https://example.com/twitter-alt.jpg">')
        ->toContain('<meta name="twitter:image:alt" content="Twitter alt">');
});

it('does not render twitter fallback tags by default', function (): void {
    $head = $this->head();

    $head->title('Gallery')
        ->description('Gallery description.')
        ->ogImage('https://example.com/gallery.jpg', alt: 'Gallery image');

    expect($head->toHtml())
        ->not->toContain('name="twitter:title"')
        ->not->toContain('name="twitter:description"')
        ->not->toContain('name="twitter:image"');

    expect($head->toArray()['twitter'])->toBe([]);
});

it('falls back from document tags to twitter tags when configured', function (): void {
    $head = $this->head();

    $head->defaults(fn(HeadBuilder $head): HeadBuilder => $head->twitter());

    $head->title('Gallery')
        ->description('Gallery description.')
        ->ogImage('https://example.com/gallery.jpg', alt: 'Gallery image');

    expect($head->toHtml())
        ->toContain('<meta name="twitter:title" content="Gallery">')
        ->toContain('<meta name="twitter:description" content="Gallery description.">')
        ->toContain('<meta name="twitter:image" content="https://example.com/gallery.jpg">')
        ->toContain('<meta name="twitter:image:alt" content="Gallery image">');

    expect($head->toArray()['twitter']['image'])->toBe([
        'url' => 'https://example.com/gallery.jpg',
        'alt' => 'Gallery image',
    ]);
});

it('prefers explicit twitter image tags over open graph image tags', function (): void {
    $head = $this->head();

    $head->ogImage('https://example.com/gallery.jpg', alt: 'Gallery image')
        ->twitterImage('https://example.com/twitter.jpg', alt: 'Twitter image');

    expect($head->toHtml())
        ->toContain('<meta name="twitter:image" content="https://example.com/twitter.jpg">')
        ->toContain('<meta name="twitter:image:alt" content="Twitter image">')
        ->not->toContain('<meta name="twitter:image" content="https://example.com/gallery.jpg">');

    expect($head->toArray()['twitter']['image'])->toBe([
        'url' => 'https://example.com/twitter.jpg',
        'alt' => 'Twitter image',
    ]);
});
