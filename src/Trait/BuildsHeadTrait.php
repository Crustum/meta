<?php
declare(strict_types=1);

namespace Crustum\Meta\Trait;

use Cake\Datasource\Paging\PaginatedInterface;
use Cake\View\Helper\PaginatorHelper;
use Crustum\Meta\Enums\ImageType;
use Crustum\Meta\Enums\Media;
use Crustum\Meta\Enums\OgType;
use Crustum\Meta\Enums\RobotsRule;
use Crustum\Meta\Enums\TwitterCard;
use Crustum\Meta\HeadData;
use Crustum\Meta\Schema\SchemaFactory;
use Crustum\Meta\Schema\SchemaObject;
use Crustum\Meta\Tags\AlternateLinks;
use Crustum\Meta\Tags\Canonical;
use Crustum\Meta\Tags\Description;
use Crustum\Meta\Tags\FeedLinks;
use Crustum\Meta\Tags\GenericLinks;
use Crustum\Meta\Tags\MetaTags;
use Crustum\Meta\Tags\OpenGraph;
use Crustum\Meta\Tags\PaginationLinks;
use Crustum\Meta\Tags\PerformanceLinks;
use Crustum\Meta\Tags\Robots;
use Crustum\Meta\Tags\Schemas;
use Crustum\Meta\Tags\Title;
use Crustum\Meta\Tags\Twitter;

/**
 * Fluent API for building head data.
 *
 * Classes using this trait must expose their head data and schema factory so
 * the fluent methods can write through to them.
 */
trait BuildsHeadTrait
{
    /**
     * Get the head data instance the fluent methods should write to.
     *
     * @return \Crustum\Meta\HeadData
     */
    abstract protected function headData(): HeadData;

    /**
     * Get the schema factory used to resolve schema callbacks.
     *
     * @return \Crustum\Meta\Schema\SchemaFactory
     */
    abstract protected function schemaFactory(): SchemaFactory;

    /**
     * Set the title, using $exact to render without inherited prefix or suffix.
     *
     * @param string $title Title
     * @param string|null $prefix Title prefix
     * @param string|null $suffix Title suffix
     * @param bool|null $exact Render exactly, skipping prefix and suffix
     * @return static
     */
    public function title(string $title, ?string $prefix = null, ?string $suffix = null, ?bool $exact = null): static
    {
        $this->headData()->overlayBuilder(Title::make($title, prefix: $prefix, suffix: $suffix, exact: $exact));

        return $this;
    }

    /**
     * Set the meta description.
     *
     * @param string $description Description
     * @return static
     */
    public function description(string $description): static
    {
        $this->headData()->overlayBuilder(Description::make($description));

        return $this;
    }

    /**
     * Set the theme color.
     *
     * @param string $color Color
     * @param \Crustum\Meta\Enums\Media|string|null $media Media condition
     * @return static
     */
    public function themeColor(string $color, Media|string|null $media = null): static
    {
        return $this->meta('theme-color', $color, media: $media);
    }

    /**
     * Set the application name.
     *
     * @param string $name Application name
     * @return static
     */
    public function applicationName(string $name): static
    {
        return $this->meta('application-name', $name);
    }

    /**
     * Set the color scheme.
     *
     * @param string $scheme Color scheme
     * @return static
     */
    public function colorScheme(string $scheme): static
    {
        return $this->meta('color-scheme', $scheme);
    }

    /**
     * Set the referrer policy.
     *
     * @param string $policy Referrer policy
     * @return static
     */
    public function referrer(string $policy): static
    {
        return $this->meta('referrer', $policy);
    }

    /**
     * Set the viewport.
     *
     * @param string $content Viewport content
     * @return static
     */
    public function viewport(string $content): static
    {
        return $this->meta('viewport', $content);
    }

    /**
     * Set the Apple web app title.
     *
     * @param string $title Title
     * @return static
     */
    public function appleWebAppTitle(string $title): static
    {
        return $this->meta('apple-mobile-web-app-title', $title);
    }

    /**
     * Set whether the web app is standalone capable.
     *
     * @param bool $capable Whether the app is capable
     * @return static
     */
    public function webAppCapable(bool $capable = true): static
    {
        return $this->meta('mobile-web-app-capable', $capable ? 'yes' : 'no');
    }

    /**
     * Set the Apple web app status bar style.
     *
     * @param string $style Status bar style
     * @return static
     */
    public function appleWebAppStatusBarStyle(string $style): static
    {
        return $this->meta('apple-mobile-web-app-status-bar-style', $style);
    }

    /**
     * Set the canonical URL.
     *
     * @param string|null $url Canonical URL
     * @param bool|null $forceHttps Force HTTPS scheme
     * @param bool|null $trailingSlash Normalize a trailing slash
     * @return static
     */
    public function canonical(?string $url = null, ?bool $forceHttps = null, ?bool $trailingSlash = null): static
    {
        $this->headData()->overlayBuilder(Canonical::make($url, forceHttps: $forceHttps, trailingSlash: $trailingSlash));

        return $this;
    }

    /**
     * Set the robots meta directive.
     *
     * @param \Crustum\Meta\Enums\RobotsRule|array<int, string|\Crustum\Meta\Enums\RobotsRule>|string $directives Robots directives
     * @return static
     */
    public function robots(string|RobotsRule|array $directives): static
    {
        $this->headData()->overlayBuilder(Robots::make($directives));

        return $this;
    }

    /**
     * Allow search engines to index the page.
     *
     * @return static
     */
    public function searchableByRobots(): static
    {
        return $this->robots(RobotsRule::All);
    }

    /**
     * Prevent search engines from indexing the page.
     *
     * @return static
     */
    public function hiddenFromRobots(): static
    {
        return $this->robots(RobotsRule::None);
    }

    /**
     * Set Open Graph metadata.
     *
     * @param \Crustum\Meta\Enums\OgType|string|null $type Object type
     * @param string|null $title Title
     * @param string|null $description Description
     * @param string|null $url URL
     * @param string|null $image Image URL
     * @param string|null $video Video URL
     * @param string|null $audio Audio URL
     * @param string|null $siteName Site name
     * @param string|null $locale Locale
     * @param string|null $determiner Determiner
     * @return static
     */
    public function og(
        OgType|string|null $type = null,
        ?string $title = null,
        ?string $description = null,
        ?string $url = null,
        ?string $image = null,
        ?string $video = null,
        ?string $audio = null,
        ?string $siteName = null,
        ?string $locale = null,
        ?string $determiner = null,
    ): static {
        $this->headData()->overlayBuilder(OpenGraph::make(
            type: $type,
            title: $title,
            description: $description,
            url: $url,
            image: $image,
            video: $video,
            audio: $audio,
            siteName: $siteName,
            locale: $locale,
            determiner: $determiner,
        ));

        return $this;
    }

    /**
     * Add an Open Graph image.
     *
     * @param string $url Image URL
     * @param string|null $alt Alt text
     * @param int|null $width Width
     * @param int|null $height Height
     * @param \Crustum\Meta\Enums\ImageType|string|null $type Image type
     * @param string|null $secureUrl Secure URL
     * @return static
     */
    public function ogImage(
        string $url,
        ?string $alt = null,
        ?int $width = null,
        ?int $height = null,
        ImageType|string|null $type = null,
        ?string $secureUrl = null,
    ): static {
        $this->headData()->builder(OpenGraph::class)->image($url, alt: $alt, width: $width, height: $height, type: $type, secureUrl: $secureUrl);

        return $this;
    }

    /**
     * Add an Open Graph video.
     *
     * @param string $url Video URL
     * @param string|null $alt Alt text
     * @param int|null $width Width
     * @param int|null $height Height
     * @param string|null $type Video type
     * @param string|null $secureUrl Secure URL
     * @return static
     */
    public function ogVideo(
        string $url,
        ?string $alt = null,
        ?int $width = null,
        ?int $height = null,
        ?string $type = null,
        ?string $secureUrl = null,
    ): static {
        $this->headData()->builder(OpenGraph::class)->video($url, alt: $alt, width: $width, height: $height, type: $type, secureUrl: $secureUrl);

        return $this;
    }

    /**
     * Add an Open Graph audio file.
     *
     * @param string $url Audio URL
     * @param string|null $type Audio type
     * @param string|null $secureUrl Secure URL
     * @return static
     */
    public function ogAudio(string $url, ?string $type = null, ?string $secureUrl = null): static
    {
        $this->headData()->builder(OpenGraph::class)->audio($url, type: $type, secureUrl: $secureUrl);

        return $this;
    }

    /**
     * Set Twitter card metadata.
     *
     * @param \Crustum\Meta\Enums\TwitterCard|string|null $card Card type
     * @param string|null $site Site handle
     * @param string|null $creator Creator handle
     * @param string|null $title Title
     * @param string|null $description Description
     * @param string|null $image Image URL
     * @return static
     */
    public function twitter(
        TwitterCard|string|null $card = null,
        ?string $site = null,
        ?string $creator = null,
        ?string $title = null,
        ?string $description = null,
        ?string $image = null,
    ): static {
        $this->headData()->overlayBuilder(Twitter::make(
            card: $card,
            site: $site,
            creator: $creator,
            title: $title,
            description: $description,
            image: $image,
        ));

        return $this;
    }

    /**
     * Add a Twitter card image.
     *
     * @param string $url Image URL
     * @param string|null $alt Alt text
     * @return static
     */
    public function twitterImage(string $url, ?string $alt = null): static
    {
        $this->headData()->builder(Twitter::class)->image($url, alt: $alt);

        return $this;
    }

    /**
     * Add a preload performance link.
     *
     * @param string $href Href
     * @param string|null $as Destination kind
     * @param string|bool|null $crossorigin Crossorigin mode
     * @param \Crustum\Meta\Enums\ImageType|string|null $type Resource type
     * @param \Crustum\Meta\Enums\Media|string|null $media Media condition
     * @return static
     */
    public function preload(string $href, ?string $as = null, bool|string|null $crossorigin = null, ImageType|string|null $type = null, Media|string|null $media = null): static
    {
        $this->headData()->builder(PerformanceLinks::class)->preload($href, as: $as, crossorigin: $crossorigin, type: $type, media: $media);

        return $this;
    }

    /**
     * Add a preload link for a local asset.
     *
     * @param string $path Asset path
     * @param string|null $as Destination kind
     * @param string|bool|null $crossorigin Crossorigin mode
     * @param \Crustum\Meta\Enums\ImageType|string|null $type Resource type
     * @param \Crustum\Meta\Enums\Media|string|null $media Media condition
     * @return static
     */
    public function preloadAsset(string $path, ?string $as = null, bool|string|null $crossorigin = null, ImageType|string|null $type = null, Media|string|null $media = null): static
    {
        $this->headData()->builder(PerformanceLinks::class)->preloadAsset($path, as: $as, crossorigin: $crossorigin, type: $type, media: $media);

        return $this;
    }

    /**
     * Add a prefetch performance link.
     *
     * @param string $href Href
     * @param string|null $as Destination kind
     * @return static
     */
    public function prefetch(string $href, ?string $as = null): static
    {
        $this->headData()->builder(PerformanceLinks::class)->prefetch($href, as: $as);

        return $this;
    }

    /**
     * Add a prefetch link for a local asset.
     *
     * @param string $path Asset path
     * @param string|null $as Destination kind
     * @return static
     */
    public function prefetchAsset(string $path, ?string $as = null): static
    {
        $this->headData()->builder(PerformanceLinks::class)->prefetchAsset($path, as: $as);

        return $this;
    }

    /**
     * Add a preconnect performance link.
     *
     * @param string $href Href
     * @param string|bool|null $crossorigin Crossorigin mode
     * @return static
     */
    public function preconnect(string $href, bool|string|null $crossorigin = null): static
    {
        $this->headData()->builder(PerformanceLinks::class)->preconnect($href, crossorigin: $crossorigin);

        return $this;
    }

    /**
     * Add a DNS prefetch performance link.
     *
     * @param string $href Href
     * @return static
     */
    public function dnsPrefetch(string $href): static
    {
        $this->headData()->builder(PerformanceLinks::class)->dnsPrefetch($href);

        return $this;
    }

    /**
     * Add rel=prev/next links from a CakePHP paginator helper or result set.
     *
     * Controllers may pass the `PaginatedResultSet` returned by
     * `$this->paginate()`; templates may pass the view's `PaginatorHelper`.
     *
     * @param \Cake\View\Helper\PaginatorHelper|\Cake\Datasource\Paging\PaginatedInterface<array-key, mixed> $paginator Paginator helper or result set
     * @return static
     */
    public function paginate(PaginatorHelper|PaginatedInterface $paginator): static
    {
        $links = $paginator instanceof PaginatorHelper
            ? PaginationLinks::fromPaginator($paginator)
            : PaginationLinks::fromPaginated($paginator);

        $this->headData()->overlayBuilder($links);

        return $this;
    }

    /**
     * Add localized alternate URLs.
     *
     * @param array<string, string> $alternates Locale-to-URL map
     * @return static
     */
    public function alternates(array $alternates): static
    {
        $this->headData()->overlayBuilder(new AlternateLinks($alternates));

        return $this;
    }

    /**
     * Add a feed link.
     *
     * @param string $href Feed URL
     * @param string $title Feed title
     * @param string $type Feed type
     * @return static
     */
    public function feed(string $href, string $title, string $type = 'rss'): static
    {
        $this->headData()->builder(FeedLinks::class)->feed($href, $title, $type);

        return $this;
    }

    /**
     * Add a JSON-LD schema object to the page.
     *
     * @param \Crustum\Meta\Schema\SchemaObject|callable(\Crustum\Meta\Schema\SchemaFactory): (\Crustum\Meta\Schema\SchemaObject|array<string, mixed>)|array<string, mixed> $schema Schema object, array, or factory callback
     * @return static
     */
    public function schema(SchemaObject|array|callable $schema): static
    {
        if (is_callable($schema)) {
            $schema = $schema($this->schemaFactory());
        }

        $this->headData()->builder(Schemas::class)->schema($schema);

        return $this;
    }

    /**
     * Add a meta tag.
     *
     * @param string $key Meta key
     * @param string $content Content
     * @param bool|null $property Render as an Open Graph property
     * @param \Crustum\Meta\Enums\Media|string|null $media Media condition
     * @return static
     */
    public function meta(string $key, string $content, ?bool $property = null, Media|string|null $media = null): static
    {
        $this->headData()->builder(MetaTags::class)->tag($key, $content, $property, $media);

        return $this;
    }

    /**
     * Add a generic link.
     *
     * @param string $rel Link relation
     * @param string $href Link href
     * @param array<string, \BackedEnum|bool|float|int|string|null> $attributes Link attributes
     * @return static
     */
    public function link(string $rel, string $href, array $attributes = []): static
    {
        $this->headData()->builder(GenericLinks::class)->link($rel, $href, $attributes);

        return $this;
    }

    /**
     * Add an icon link.
     *
     * @param string $href Icon URL
     * @param \Crustum\Meta\Enums\ImageType|string|null $type Icon type
     * @param string|null $sizes Icon sizes
     * @param \Crustum\Meta\Enums\Media|string|null $media Media condition
     * @return static
     */
    public function icon(string $href, ImageType|string|null $type = null, ?string $sizes = null, Media|string|null $media = null): static
    {
        return $this->link('icon', $href, static::linkAttributes([
            'type' => $type instanceof ImageType ? $type->value : $type,
            'sizes' => $sizes,
            'media' => $media instanceof Media ? $media->value : $media,
        ]));
    }

    /**
     * Add a favicon link.
     *
     * @param string $href Icon URL
     * @param \Crustum\Meta\Enums\ImageType|string|null $type Icon type
     * @param string|null $sizes Icon sizes
     * @param \Crustum\Meta\Enums\Media|string|null $media Media condition
     * @return static
     */
    public function favicon(string $href, ImageType|string|null $type = null, ?string $sizes = null, Media|string|null $media = null): static
    {
        return $this->icon($href, type: $type, sizes: $sizes, media: $media);
    }

    /**
     * Add an Apple touch icon link.
     *
     * @param string $href Icon URL
     * @param string|null $sizes Icon sizes
     * @return static
     */
    public function appleTouchIcon(string $href, ?string $sizes = null): static
    {
        return $this->link('apple-touch-icon', $href, static::linkAttributes([
            'sizes' => $sizes,
        ]));
    }

    /**
     * Add a mask icon link.
     *
     * @param string $href Icon URL
     * @param string|null $color Mask color
     * @return static
     */
    public function maskIcon(string $href, ?string $color = null): static
    {
        return $this->link('mask-icon', $href, static::linkAttributes([
            'color' => $color,
        ]));
    }

    /**
     * Add a manifest link.
     *
     * @param string $href Manifest URL
     * @param string|bool|null $crossorigin Crossorigin mode
     * @return static
     */
    public function manifest(string $href = '/site.webmanifest', bool|string|null $crossorigin = null): static
    {
        return $this->link('manifest', $href, static::linkAttributes([
            'crossorigin' => $crossorigin,
        ]));
    }

    /**
     * Add an Apple touch startup image link.
     *
     * @param string $href Image URL
     * @param \Crustum\Meta\Enums\Media|string|null $media Media condition
     * @return static
     */
    public function appleTouchStartupImage(string $href, Media|string|null $media = null): static
    {
        return $this->link('apple-touch-startup-image', $href, static::linkAttributes([
            'media' => $media instanceof Media ? $media->value : $media,
        ]));
    }

    /**
     * Configure progressive web app metadata.
     *
     * @param string $name App name
     * @param string $manifest Manifest URL
     * @param string|null $themeColor Theme color
     * @param string|null $appleTouchIcon Apple touch icon URL
     * @param string|null $appleTouchIconSizes Apple touch icon sizes
     * @param string|null $appleWebAppStatusBarStyle Status bar style
     * @return static
     */
    public function pwa(
        string $name,
        string $manifest = '/site.webmanifest',
        ?string $themeColor = null,
        ?string $appleTouchIcon = null,
        ?string $appleTouchIconSizes = '180x180',
        ?string $appleWebAppStatusBarStyle = null,
    ): static {
        $this->applicationName($name)
            ->appleWebAppTitle($name)
            ->webAppCapable()
            ->manifest($manifest);

        if (! is_null($themeColor)) {
            $this->themeColor($themeColor);
        }

        if (! is_null($appleTouchIcon)) {
            $this->appleTouchIcon($appleTouchIcon, sizes: $appleTouchIconSizes);
        }

        if (! is_null($appleWebAppStatusBarStyle)) {
            $this->appleWebAppStatusBarStyle($appleWebAppStatusBarStyle);
        }

        return $this;
    }

    /**
     * Remove null-valued link attributes.
     *
     * @param array<string, bool|float|int|string|null> $attributes Link attributes
     * @return array<string, bool|float|int|string|null>
     */
    protected static function linkAttributes(array $attributes): array
    {
        return array_filter($attributes, fn($value): bool => ! is_null($value));
    }
}
