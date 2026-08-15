<?php
declare(strict_types=1);

namespace Crustum\Meta\Tags;

use Cake\Core\Configure;
use Crustum\Meta\Enums\ImageType;
use Crustum\Meta\Enums\Media;
use Crustum\Meta\Rendering\ResolvedHead;
use Crustum\Meta\Rendering\TagRenderer;
use InvalidArgumentException;
use Override;

/**
 * Performance link tag builder (preload, prefetch, preconnect, dns-prefetch).
 *
 * @phpstan-consistent-constructor
 * @phpstan-type LinkAttributes array{href: string, as?: string|null, crossorigin?: bool|string|null, type?: string|null, media?: string|null}
 */
class PerformanceLinks extends GroupedTagBuilder
{
    /**
     * Constructor.
     *
     * @param array<string, LinkAttributes> $preloads Preload links
     * @param array<string, LinkAttributes> $prefetches Prefetch links
     * @param array<string, LinkAttributes> $preconnects Preconnect links
     * @param array<string, array{href: string}> $dnsPrefetches DNS prefetch links
     */
    public function __construct(
        protected array $preloads = [],
        protected array $prefetches = [],
        protected array $preconnects = [],
        protected array $dnsPrefetches = [],
    ) {
    }

    /**
     * The unique name identifying this builder within the head.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'performance';
    }

    /**
     * The dot-notated key this builder occupies in the Head::toArray() result.
     *
     * @return string
     */
    #[Override]
    public static function headArrayKey(): string
    {
        return 'links.performance';
    }

    /**
     * The value used in Head::toArray() when this builder has no data.
     *
     * @return array<string, array<int, mixed>>
     */
    #[Override]
    public static function headArrayDefault(): mixed
    {
        return [
            'preload' => [],
            'prefetch' => [],
            'preconnect' => [],
            'dnsPrefetch' => [],
        ];
    }

    /**
     * The route attribute keys this builder accepts values for.
     *
     * @return array<int, string>
     */
    #[Override]
    public static function routeAttributeKeys(): array
    {
        return ['preload', 'prefetch', 'preconnect', 'dnsPrefetch'];
    }

    /**
     * Create a builder from a route attribute value.
     *
     * @param string $key Route attribute key
     * @param mixed $value Route attribute value
     * @return self|null
     */
    public static function fromRouteAttribute(string $key, mixed $value): ?self
    {
        return match ($key) {
            'preload' => is_string($value) || is_array($value) ? self::fromPreloadAttributes($value) : null,
            'prefetch' => is_string($value) || is_array($value) ? self::fromPrefetchAttributes($value) : null,
            'preconnect' => is_string($value) || is_array($value) ? self::fromPreconnectAttributes($value) : null,
            'dnsPrefetch' => is_string($value) || is_array($value) ? self::fromDnsPrefetchAttributes($value) : null,
            default => null,
        };
    }

    /**
     * Create a builder from preload route attributes.
     *
     * @param array<mixed, mixed>|string $preloads Preload values
     * @return self|null
     */
    public static function fromPreloadAttributes(string|array $preloads): ?self
    {
        $links = new self();

        foreach (self::repeatableLinkAttributes($preloads, ['href', 'as', 'crossorigin', 'type', 'media']) as $href => $attributes) {
            if (!$links->addPreloadRouteAttribute($href, $attributes)) {
                return null;
            }
        }

        return $links;
    }

    /**
     * Create a builder from prefetch route attributes.
     *
     * @param array<mixed, mixed>|string $prefetches Prefetch values
     * @return self|null
     */
    public static function fromPrefetchAttributes(string|array $prefetches): ?self
    {
        $links = new self();

        foreach (self::repeatableLinkAttributes($prefetches, ['href', 'as']) as $href => $attributes) {
            if (!$links->addPrefetchRouteAttribute($href, $attributes)) {
                return null;
            }
        }

        return $links;
    }

    /**
     * Create a builder from preconnect route attributes.
     *
     * @param array<mixed, mixed>|string $preconnects Preconnect values
     * @return self|null
     */
    public static function fromPreconnectAttributes(string|array $preconnects): ?self
    {
        $links = new self();

        foreach (self::repeatableLinkAttributes($preconnects, ['href', 'crossorigin']) as $href => $attributes) {
            if (!$links->addPreconnectRouteAttribute($href, $attributes)) {
                return null;
            }
        }

        return $links;
    }

    /**
     * Create a builder from DNS prefetch route attributes.
     *
     * @param array<mixed>|string $dnsPrefetches DNS prefetch values
     * @return self|null
     */
    public static function fromDnsPrefetchAttributes(string|array $dnsPrefetches): ?self
    {
        $links = new self();

        foreach (self::items($dnsPrefetches) as $href) {
            if (! is_string($href)) {
                return null;
            }

            $links->dnsPrefetch($href);
        }

        return $links;
    }

    /**
     * Add a preload link.
     *
     * @param string $href Link href
     * @param string|null $as Resource destination
     * @param string|bool|null $crossorigin Cross-origin policy
     * @param \Crustum\Meta\Enums\ImageType|string|null $type Resource type
     * @param \Crustum\Meta\Enums\Media|string|null $media Media query
     * @return static
     */
    public function preload(string $href, ?string $as = null, bool|string|null $crossorigin = null, ImageType|string|null $type = null, Media|string|null $media = null): static
    {
        $this->preloads[$href] = array_filter([
            'href' => $href,
            'as' => $as,
            'crossorigin' => $crossorigin,
            'type' => $type instanceof ImageType ? $type->value : $type,
            'media' => $media instanceof Media ? $media->value : $media,
        ], fn($value): bool => ! is_null($value));

        return $this;
    }

    /**
     * Add a preload link for a bundled asset, detecting the destination from the extension.
     *
     * @param string $path Asset path
     * @param string|null $as Resource destination
     * @param string|bool|null $crossorigin Cross-origin policy
     * @param \Crustum\Meta\Enums\ImageType|string|null $type Resource type
     * @param \Crustum\Meta\Enums\Media|string|null $media Media query
     * @return static
     * @throws \InvalidArgumentException When the destination cannot be detected
     */
    public function preloadAsset(string $path, ?string $as = null, bool|string|null $crossorigin = null, ImageType|string|null $type = null, Media|string|null $media = null): static
    {
        $as ??= self::detectAs($path) ?? throw new InvalidArgumentException(
            "Unable to detect a preload [as] attribute for asset [{$path}]. Pass one explicitly.",
        );

        if ($as === 'font') {
            $crossorigin ??= true;
        }

        return $this->preload(self::assetUrl($path), as: $as, crossorigin: $crossorigin, type: $type, media: $media);
    }

    /**
     * Add a prefetch link.
     *
     * @param string $href Link href
     * @param string|null $as Resource destination
     * @return static
     */
    public function prefetch(string $href, ?string $as = null): static
    {
        $this->prefetches[$href] = array_filter([
            'href' => $href,
            'as' => $as,
        ], fn($value): bool => ! is_null($value));

        return $this;
    }

    /**
     * Add a prefetch link for a bundled asset, detecting the destination from the extension.
     *
     * @param string $path Asset path
     * @param string|null $as Resource destination
     * @return static
     */
    public function prefetchAsset(string $path, ?string $as = null): static
    {
        return $this->prefetch(self::assetUrl($path), as: $as ?? self::detectAs($path));
    }

    /**
     * Add a preconnect link.
     *
     * @param string $href Link href
     * @param string|bool|null $crossorigin Cross-origin policy
     * @return static
     */
    public function preconnect(string $href, bool|string|null $crossorigin = null): static
    {
        $this->preconnects[$href] = array_filter([
            'href' => $href,
            'crossorigin' => $crossorigin,
        ], fn($value): bool => ! is_null($value));

        return $this;
    }

    /**
     * Add a DNS prefetch link.
     *
     * @param string $href Link href
     * @return static
     */
    public function dnsPrefetch(string $href): static
    {
        $this->dnsPrefetches[$href] = ['href' => $href];

        return $this;
    }

    /**
     * Merge this builder over the given base builder.
     *
     * @param \Crustum\Meta\Tags\TagBuilder|null $base Base builder
     * @return static
     */
    public function overlayOn(?TagBuilder $base): static
    {
        if (!$base instanceof self) {
            return $this;
        }

        return new static(
            array_replace($base->preloads, $this->preloads),
            array_replace($base->prefetches, $this->prefetches),
            array_replace($base->preconnects, $this->preconnects),
            array_replace($base->dnsPrefetches, $this->dnsPrefetches),
        );
    }

    /**
     * Determine if the builder holds no data.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->preloads === []
            && $this->prefetches === []
            && $this->preconnects === []
            && $this->dnsPrefetches === [];
    }

    /**
     * Convert this builder into its Head::toArray() value.
     *
     * @param \Crustum\Meta\Rendering\ResolvedHead $head Resolved head
     * @return array{preload: array<int, LinkAttributes>, prefetch: array<int, LinkAttributes>, preconnect: array<int, LinkAttributes>, dnsPrefetch: array<int, array{href: string}>}
     */
    public function toHeadArray(ResolvedHead $head): array
    {
        return $this->headArray();
    }

    /**
     * Convert this builder into the HTML tags rendered by @head.
     *
     * @param \Crustum\Meta\Rendering\ResolvedHead $head Resolved head
     * @param \Crustum\Meta\Rendering\TagRenderer $tags Tag renderer
     * @return array<int, string>
     */
    #[Override]
    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return [
            ...array_map(fn(array $attributes): string => $tags->linkWithAttributes('preload', $attributes), array_values($this->preloads)),
            ...array_map(fn(array $attributes): string => $tags->linkWithAttributes('prefetch', $attributes), array_values($this->prefetches)),
            ...array_map(fn(array $attributes): string => $tags->linkWithAttributes('preconnect', $attributes), array_values($this->preconnects)),
            ...array_map(fn(array $attributes): string => $tags->linkWithAttributes('dns-prefetch', $attributes), array_values($this->dnsPrefetches)),
        ];
    }

    /**
     * Get the builder's Head::toArray() value.
     *
     * @return array{preload: array<int, LinkAttributes>, prefetch: array<int, LinkAttributes>, preconnect: array<int, LinkAttributes>, dnsPrefetch: array<int, array{href: string}>}
     */
    protected function headArray(): array
    {
        return [
            'preload' => array_values($this->preloads),
            'prefetch' => array_values($this->prefetches),
            'preconnect' => array_values($this->preconnects),
            'dnsPrefetch' => array_values($this->dnsPrefetches),
        ];
    }

    /**
     * Detect the resource destination from an asset path extension.
     *
     * @param string $path Asset path
     * @return string|null
     */
    protected static function detectAs(string $path): ?string
    {
        [$path] = explode('#', $path, 2);
        [$path] = explode('?', $path, 2);

        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'woff2', 'woff', 'ttf', 'otf', 'eot' => 'font',
            'css' => 'style',
            'js', 'mjs' => 'script',
            'avif', 'webp', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico' => 'image',
            'mp4', 'webm', 'ogv' => 'video',
            'mp3', 'ogg', 'wav', 'flac', 'aac', 'm4a' => 'audio',
            'vtt' => 'track',
            'json' => 'fetch',
            default => null,
        };
    }

    /**
     * Resolve the absolute URL for a bundled asset.
     *
     * @param string $path Asset path
     * @return string
     */
    protected static function assetUrl(string $path): string
    {
        return rtrim((string)Configure::read('App.fullBaseUrl'), '/') . '/' . ltrim($path, '/');
    }

    /**
     * Cast a route attribute value to a boolean or string.
     *
     * @param mixed $value Route attribute value
     * @return string|bool|null
     */
    protected static function boolOrString(mixed $value): bool|string|null
    {
        return is_bool($value) || is_string($value) ? $value : null;
    }

    /**
     * Add a preload link from a route attribute definition.
     *
     * @param mixed $href Link href
     * @param mixed $attributes Link attributes
     * @return bool
     */
    private function addPreloadRouteAttribute(mixed $href, mixed $attributes): bool
    {
        if (is_string($attributes)) {
            $this->preload($attributes);

            return true;
        }

        $url = $this->routeAttributeHref($href, $attributes);

        if (! is_array($attributes) || is_null($url)) {
            return false;
        }

        $this->preload(
            $url,
            as: self::string($attributes['as'] ?? null),
            crossorigin: self::boolOrString($attributes['crossorigin'] ?? null),
            type: self::stringOrBackedEnum($attributes['type'] ?? null),
            media: self::stringOrBackedEnum($attributes['media'] ?? null),
        );

        return true;
    }

    /**
     * Add a prefetch link from a route attribute definition.
     *
     * @param mixed $href Link href
     * @param mixed $attributes Link attributes
     * @return bool
     */
    private function addPrefetchRouteAttribute(mixed $href, mixed $attributes): bool
    {
        if (is_string($attributes)) {
            $this->prefetch($attributes);

            return true;
        }

        $url = $this->routeAttributeHref($href, $attributes);

        if (! is_array($attributes) || is_null($url)) {
            return false;
        }

        $this->prefetch($url, as: self::string($attributes['as'] ?? null));

        return true;
    }

    /**
     * Add a preconnect link from a route attribute definition.
     *
     * @param mixed $href Link href
     * @param mixed $attributes Link attributes
     * @return bool
     */
    private function addPreconnectRouteAttribute(mixed $href, mixed $attributes): bool
    {
        if (is_string($attributes)) {
            $this->preconnect($attributes);

            return true;
        }

        $url = $this->routeAttributeHref($href, $attributes);

        if (! is_array($attributes) || is_null($url)) {
            return false;
        }

        $this->preconnect($url, crossorigin: self::boolOrString($attributes['crossorigin'] ?? null));

        return true;
    }

    /**
     * Normalize a route attribute value into a list of link definitions,
     * treating an attribute map containing any single-link key as one link.
     *
     * @param array<mixed, mixed>|string $value Route attribute value
     * @param array<int, string> $singleAttributeKeys Keys identifying a single link
     * @return array<mixed, mixed>
     */
    private static function repeatableLinkAttributes(string|array $value, array $singleAttributeKeys): array
    {
        if (is_string($value)) {
            return [$value];
        }

        if (array_is_list($value)) {
            return $value;
        }

        foreach ($singleAttributeKeys as $key) {
            if (array_key_exists($key, $value)) {
                return [$value];
            }
        }

        return $value;
    }

    /**
     * Resolve the link href from a route attribute definition.
     *
     * @param mixed $href Link href
     * @param array<mixed, mixed> $attributes Link attributes
     * @return string|null
     */
    private function routeAttributeHref(mixed $href, array $attributes): ?string
    {
        return self::string($attributes['href'] ?? null) ?? self::string($href);
    }
}
