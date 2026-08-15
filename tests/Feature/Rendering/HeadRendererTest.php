<?php
declare(strict_types=1);

use Cake\Routing\Router;
use Crustum\Meta\Enums\OgType;
use Crustum\Meta\Enums\TwitterCard;
use Crustum\Meta\HeadBuilder;
use Crustum\Meta\Schema\SchemaFactory;

it('renders resolved head tags in builder order', function (): void {
    $head = $this->head();
    $schema = new SchemaFactory();

    $head->defaults(function (HeadBuilder $head): void {
        $head
            ->title('Acme', suffix: ' - Acme')
            ->description('Build something great.')
            ->canonical()
            ->og(type: OgType::Website, siteName: 'Acme')
            ->twitter(card: TwitterCard::SummaryWithLargeImage)
            ->preconnect('https://fonts.example.com');
    });

    $head->title('About')
        ->description('About Acme.')
        ->canonical('http://example.com/about/')
        ->preload('/fonts/inter.woff2', as: 'font', crossorigin: true)
        ->prefetch('/images/next.webp')
        ->dnsPrefetch('https://analytics.example.com')
        ->paginate($this->paginator())
        ->alternates(['en' => 'https://example.com/about', 'fr' => 'https://example.com/fr/about'])
        ->feed('/feed', title: 'Acme RSS')
        ->schema($schema->webPage()->name('About')->url('https://example.com/about'));

    $html = $head->toHtml();

    expect($html)
        ->toContain('<title>About - Acme</title>')
        ->toContain('<meta name="description" content="About Acme.">')
        ->toContain('<link rel="canonical" href="https://example.com/about">')
        ->toContain('<meta property="og:site_name" content="Acme">')
        ->toContain('<meta name="twitter:card" content="summary_large_image">')
        ->toContain('rel="preload" href="/fonts/inter.woff2" as="font" crossorigin>')
        ->toContain('rel="prefetch" href="/images/next.webp">')
        ->toContain('rel="preconnect" href="https://fonts.example.com">')
        ->toContain('rel="dns-prefetch" href="https://analytics.example.com">')
        ->toContain('rel="prev" href="/posts">')
        ->toContain('rel="next" href="/posts?page=3">')
        ->toContain('rel="alternate" hreflang="fr" href="https://example.com/fr/about">')
        ->toContain('rel="alternate" type="application/rss+xml" title="Acme RSS" href="/feed">')
        ->toContain('"@type":"WebPage"');
});

it('does not render inertia keys in html output', function (): void {
    $head = $this->head();

    $head->title('Dashboard')
        ->description('Dashboard overview.');

    expect($head->toHtml())
        ->toContain('<title>Dashboard</title>')
        ->toContain('<meta name="description" content="Dashboard overview.">')
        ->not->toContain('data-inertia');
});

it('builds pagination links from a paginated result set in the controller path', function (): void {
    $request = $this->boundRequest();
    Router::setRequest($request);

    $head = $this->head();
    $head->paginate($this->paginated());

    $html = $head->toHtml();

    expect($html)
        ->toContain('rel="prev" href="/posts">')
        ->toContain('rel="next" href="/posts?page=3">');
});

it('preserves sort and limit query params when building links from a result set', function (): void {
    $request = $this->boundRequest('/posts', ['controller' => 'Posts', 'action' => 'index'], [
        'sort' => 'title',
        'direction' => 'asc',
        'limit' => '20',
    ]);
    Router::setRequest($request);

    $head = $this->head();
    $head->paginate($this->paginated());

    $html = $head->toHtml();

    expect($html)
        ->toContain('rel="prev" href="/posts?sort=title&amp;direction=asc&amp;limit=20">')
        ->toContain('rel="next" href="/posts?sort=title&amp;direction=asc&amp;limit=20&amp;page=3">');
});

it('groups link builders under the links key when serialized to an array', function (): void {
    $head = $this->head();

    $head->title('About')
        ->preload('/fonts/inter.woff2', as: 'font')
        ->paginate($this->paginator())
        ->alternates(['fr' => 'https://example.com/fr/about'])
        ->feed('/feed', title: 'Acme RSS')
        ->link('manifest', '/site.webmanifest');

    $array = $head->toArray();

    expect($array['title'])->toBe('About')
        ->and($array['openGraph'])->toBe([])
        ->and(array_keys($array['links']))->toEqualCanonicalizing([
            'performance', 'pagination', 'alternates', 'feeds', 'generic',
        ])
        ->and($array['links']['performance']['preload'][0]['href'])->toBe('/fonts/inter.woff2')
        ->and($array['links']['pagination'])->toBe(['prev' => '/posts', 'next' => '/posts?page=3'])
        ->and($array['links']['alternates'])->toBe(['fr' => 'https://example.com/fr/about']);
});
