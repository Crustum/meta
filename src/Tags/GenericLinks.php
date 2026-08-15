<?php
declare(strict_types=1);

namespace Crustum\Meta\Tags;

use BackedEnum;
use Crustum\Meta\Rendering\ResolvedHead;
use Crustum\Meta\Rendering\TagRenderer;
use Override;

/**
 * Generic link tag builder.
 *
 * @phpstan-consistent-constructor
 * @phpstan-type LinkAttributes array{rel: string, href: string, attributes: array<string, bool|float|int|string|null>}
 */
class GenericLinks extends GroupedTagBuilder
{
    /**
     * Constructor.
     *
     * @param array<string, LinkAttributes> $links Links
     */
    public function __construct(protected array $links = [])
    {
    }

    /**
     * The unique name identifying this builder within the head.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'links';
    }

    /**
     * The dot-notated key this builder occupies in the Head::toArray() result.
     *
     * @return string
     */
    #[Override]
    public static function headArrayKey(): string
    {
        return 'links.generic';
    }

    /**
     * The route attribute keys this builder accepts values for.
     *
     * @return array<int, string>
     */
    #[Override]
    public static function routeAttributeKeys(): array
    {
        return ['link', 'icon', 'favicon', 'appleTouchIcon', 'maskIcon', 'manifest', 'appleTouchStartupImage'];
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
        if ($key !== 'link') {
            return self::fromAliasRouteAttribute($key, $value);
        }

        if (! is_array($value)) {
            return null;
        }

        $links = new self();

        foreach (self::items($value) as $link) {
            $links->addRouteAttributeLink($link);
        }

        return $links;
    }

    /**
     * Create a builder from an alias route attribute value.
     *
     * @param string $key Route attribute key
     * @param mixed $value Route attribute value
     * @return self|null
     */
    protected static function fromAliasRouteAttribute(string $key, mixed $value): ?self
    {
        if (! in_array($key, ['icon', 'favicon', 'appleTouchIcon', 'maskIcon', 'manifest', 'appleTouchStartupImage'], true)) {
            return null;
        }

        $links = new self();

        foreach (self::items($value) as $link) {
            $links->addAliasRouteAttributeLink($key, $link);
        }

        return $links;
    }

    /**
     * Add a link.
     *
     * @param string $rel Link relation
     * @param string $href Link href
     * @param array<string, \BackedEnum|bool|float|int|string|null> $attributes Link attributes
     * @return static
     */
    public function link(string $rel, string $href, array $attributes = []): static
    {
        unset($attributes['rel'], $attributes['href']);
        $attributes = self::attributes($attributes);

        $this->links[$rel . ' ' . $href] = [
            'rel' => $rel,
            'href' => $href,
            'attributes' => $attributes,
        ];

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

        return new static(array_replace($base->links, $this->links));
    }

    /**
     * Determine if the builder holds no data.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->links === [];
    }

    /**
     * Convert this builder into its Head::toArray() value.
     *
     * @param \Crustum\Meta\Rendering\ResolvedHead $head Resolved head
     * @return array<int, LinkAttributes>
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
        return array_map(fn(array $link): string => $tags->linkWithAttributes($link['rel'], ['href' => $link['href'], ...$link['attributes']]), $this->headArray());
    }

    /**
     * Get the builder's Head::toArray() value.
     *
     * @return array<int, LinkAttributes>
     */
    protected function headArray(): array
    {
        return array_values($this->links);
    }

    /**
     * Add a link from a route attribute definition.
     *
     * @param mixed $link Link value
     * @return void
     */
    private function addRouteAttributeLink(mixed $link): void
    {
        if (! is_array($link) || ! is_string($link['rel'] ?? null) || ! is_string($link['href'] ?? null)) {
            return;
        }

        $attributes = self::named($link);
        unset($attributes['rel'], $attributes['href']);

        $this->link($link['rel'], $link['href'], self::attributes($attributes));
    }

    /**
     * Add a link from an alias route attribute definition.
     *
     * @param string $key Route attribute key
     * @param mixed $link Link value
     * @return void
     */
    private function addAliasRouteAttributeLink(string $key, mixed $link): void
    {
        $attributes = is_array($link) ? self::named($link) : [];
        $href = $this->routeAttributeHref($link, $attributes, $key === 'manifest' ? '/site.webmanifest' : null);

        if (is_null($href)) {
            return;
        }

        match ($key) {
            'icon', 'favicon' => $this->link('icon', $href, self::attributes([
                'type' => self::stringOrBackedEnum($attributes['type'] ?? null),
                'sizes' => $attributes['sizes'] ?? null,
                'media' => $attributes['media'] ?? null,
            ])),
            'appleTouchIcon' => $this->link('apple-touch-icon', $href, self::attributes([
                'sizes' => $attributes['sizes'] ?? null,
            ])),
            'maskIcon' => $this->link('mask-icon', $href, self::attributes([
                'color' => $attributes['color'] ?? null,
            ])),
            'manifest' => $this->link('manifest', $href, self::attributes([
                'crossorigin' => $attributes['crossorigin'] ?? null,
            ])),
            'appleTouchStartupImage' => $this->link('apple-touch-startup-image', $href, self::attributes([
                'media' => $attributes['media'] ?? null,
            ])),
            default => null,
        };
    }

    /**
     * Resolve the href from a route attribute definition.
     *
     * @param mixed $value Route attribute value
     * @param array<string, mixed> $attributes Route attribute values
     * @param string|null $default Default href
     * @return string|null
     */
    private function routeAttributeHref(mixed $value, array $attributes, ?string $default = null): ?string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_string($attributes['href'] ?? null)) {
            return $attributes['href'];
        }

        if ($value === true || is_array($value)) {
            return $default;
        }

        return null;
    }

    /**
     * Normalize route attribute values into renderable link attributes.
     *
     * @param array<string, mixed> $values Attribute values
     * @return array<string, bool|float|int|string|null>
     */
    protected static function attributes(array $values): array
    {
        $attributes = [];

        foreach ($values as $key => $value) {
            if ($value instanceof BackedEnum) {
                $value = $value->value;
            }

            if (is_null($value) || is_bool($value) || is_float($value) || is_int($value) || is_string($value)) {
                $attributes[$key] = $value;
            }
        }

        return $attributes;
    }
}
