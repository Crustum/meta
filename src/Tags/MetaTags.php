<?php
declare(strict_types=1);

namespace Crustum\Meta\Tags;

use Crustum\Meta\Enums\Media;
use Crustum\Meta\Rendering\ResolvedHead;
use Crustum\Meta\Rendering\TagRenderer;
use Override;

/**
 * Arbitrary meta tag builder.
 *
 * @phpstan-consistent-constructor
 * @phpstan-type MetaAttributes array{key: string, content: string, property?: bool|null, media?: string|null}
 */
class MetaTags extends GroupedTagBuilder
{
    /**
     * Constructor.
     *
     * @param array<string, MetaAttributes> $tags Meta tags
     */
    public function __construct(protected array $tags = [])
    {
    }

    /**
     * The unique name identifying this builder within the head.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'meta';
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
        if ($key === 'webAppCapable' && is_bool($value)) {
            return (new self())->tag(self::aliases()[$key], $value ? 'yes' : 'no');
        }

        if (array_key_exists($key, self::aliases()) && is_string($value)) {
            return (new self())->tag(self::aliases()[$key], $value);
        }

        if ($key !== 'meta' || ! is_array($value)) {
            return null;
        }

        $tags = new self();

        foreach ($value as $metaKey => $meta) {
            if (is_string($metaKey) && is_string($meta)) {
                $tags->tag($metaKey, $meta);
            }

            if (is_array($meta) && is_string($meta['key'] ?? null) && is_string($meta['content'] ?? null)) {
                $tags->tag(
                    $meta['key'],
                    $meta['content'],
                    property: self::bool($meta['property'] ?? null),
                    media: self::stringOrBackedEnum($meta['media'] ?? null),
                );
            }
        }

        return $tags;
    }

    /**
     * The route attribute keys this builder accepts values for.
     *
     * @return array<int, string>
     */
    #[Override]
    public static function routeAttributeKeys(): array
    {
        return ['meta', ...array_keys(self::aliases())];
    }

    /**
     * Add a meta tag.
     *
     * @param string $key Meta key
     * @param string $content Meta content
     * @param bool|null $property Key under the property attribute
     * @param \Crustum\Meta\Enums\Media|string|null $media Media query
     * @return static
     */
    public function tag(string $key, string $content, ?bool $property = null, Media|string|null $media = null): static
    {
        $media = $media instanceof Media ? $media->value : $media;

        $this->tags[$this->tagKey($key, $property, $media)] = array_filter([
            'key' => $key,
            'content' => $content,
            'property' => $property,
            'media' => $media,
        ], fn($value): bool => ! is_null($value));

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

        return new static(array_replace($base->tags, $this->tags));
    }

    /**
     * Determine if the builder holds no data.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->tags === [];
    }

    /**
     * Convert this builder into its Head::toArray() value.
     *
     * @param \Crustum\Meta\Rendering\ResolvedHead $head Resolved head
     * @return array<int, MetaAttributes>
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
        return array_map(function (array $meta) use ($tags): string {
            $attribute = $this->resolveAttribute($meta['key'], $meta['property'] ?? null);

            return $tags->metaWithAttributes($attribute, $meta['key'], [
                'content' => $meta['content'],
                'media' => $meta['media'] ?? null,
            ]);
        }, $this->headArray());
    }

    /**
     * Get the builder's Head::toArray() value.
     *
     * @return array<int, MetaAttributes>
     */
    protected function headArray(): array
    {
        return array_values($this->tags);
    }

    /**
     * Determine if a meta key uses an RDFa property namespace.
     *
     * @param string $key Meta key
     * @return bool
     */
    protected function isRdfaProperty(string $key): bool
    {
        return array_any(['og:', 'article:', 'book:', 'profile:', 'music:', 'video:', 'fb:', 'product:'], fn(string $prefix): bool => str_starts_with($key, $prefix));
    }

    /**
     * Resolve the HTML attribute a meta tag is keyed under, honoring an explicit
     * property flag and falling back to RDFa namespace detection.
     *
     * @param string $key Meta key
     * @param bool|null $property Explicit property flag
     * @return string
     */
    protected function resolveAttribute(string $key, ?bool $property): string
    {
        return $property ?? $this->isRdfaProperty($key) ? 'property' : 'name';
    }

    /**
     * The identity a meta tag is deduplicated by: attribute, key, and media query.
     *
     * @param string $key Meta key
     * @param bool|null $property Explicit property flag
     * @param string|null $media Media query
     * @return string
     */
    protected function tagKey(string $key, ?bool $property, ?string $media): string
    {
        return implode('|', [
            $this->resolveAttribute($key, $property),
            $key,
            $media ?? '',
        ]);
    }

    /**
     * Route attribute aliases keyed by their meta name.
     *
     * @return array<string, string>
     */
    protected static function aliases(): array
    {
        return [
            'themeColor' => 'theme-color',
            'applicationName' => 'application-name',
            'colorScheme' => 'color-scheme',
            'referrer' => 'referrer',
            'viewport' => 'viewport',
            'appleWebAppTitle' => 'apple-mobile-web-app-title',
            'webAppCapable' => 'mobile-web-app-capable',
            'appleWebAppStatusBarStyle' => 'apple-mobile-web-app-status-bar-style',
        ];
    }
}
