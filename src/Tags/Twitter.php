<?php
declare(strict_types=1);

namespace Crustum\Meta\Tags;

use Crustum\Meta\Enums\TwitterCard;
use Crustum\Meta\Rendering\ResolvedHead;
use Crustum\Meta\Rendering\TagRenderer;
use Override;

/**
 * Twitter card meta tag builder.
 *
 * @phpstan-consistent-constructor
 * @phpstan-type ImageAttributes array<string, string>
 */
class Twitter extends GroupedTagBuilder
{
    /**
     * Constructor.
     *
     * @param array<string, string> $properties Twitter card properties
     * @param ImageAttributes|null $image Twitter card image
     */
    public function __construct(
        protected array $properties = [],
        protected ?array $image = null,
    ) {
    }

    /**
     * The unique name identifying this builder within the head.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'twitter';
    }

    /**
     * The route attribute keys this builder accepts values for.
     *
     * @return array<int, string>
     */
    #[Override]
    public static function routeAttributeKeys(): array
    {
        return ['twitter', 'twitterImage'];
    }

    /**
     * Create a Twitter card builder.
     *
     * @param \Crustum\Meta\Enums\TwitterCard|string|null $card Card type
     * @param string|null $site Site handle
     * @param string|null $creator Creator handle
     * @param string|null $title Title
     * @param string|null $description Description
     * @param string|null $image Image URL
     * @return self
     */
    public static function make(
        TwitterCard|string|null $card = null,
        ?string $site = null,
        ?string $creator = null,
        ?string $title = null,
        ?string $description = null,
        ?string $image = null,
    ): self {
        $twitter = new self();

        $twitter->set([
            'card' => $card instanceof TwitterCard ? $card->value : $card,
            'site' => $site,
            'creator' => $creator,
            'title' => $title,
            'description' => $description,
        ]);

        if (! is_null($image)) {
            $twitter->image($image);
        }

        return $twitter;
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
            'twitter' => is_array($value) ? self::fromAttributes($value) : null,
            'twitterImage' => self::fromImageAttributes($value),
            default => null,
        };
    }

    /**
     * Create a Twitter card builder from an array of property values.
     *
     * @param array<mixed, mixed> $values Property values
     * @return self
     */
    public static function fromAttributes(array $values): self
    {
        return self::make(
            card: self::twitterCard($values['card'] ?? null),
            site: self::string($values['site'] ?? null),
            creator: self::string($values['creator'] ?? null),
            title: self::string($values['title'] ?? null),
            description: self::string($values['description'] ?? null),
            image: self::string($values['image'] ?? null),
        );
    }

    /**
     * Create a Twitter card builder from image attributes.
     *
     * @param mixed $image Image value
     * @return self
     */
    public static function fromImageAttributes(mixed $image): self
    {
        $twitter = new self();

        if (is_string($image)) {
            return $twitter->image($image);
        }

        if (is_array($image) && is_string($image['url'] ?? null)) {
            return $twitter->image($image['url'], alt: self::string($image['alt'] ?? null));
        }

        return $twitter;
    }

    /**
     * Set the Twitter card image.
     *
     * @param string $url Image URL
     * @param string|null $alt Alt text
     * @return static
     */
    public function image(string $url, ?string $alt = null): static
    {
        $this->image = array_filter([
            'url' => $url,
            'alt' => $alt,
        ], fn(mixed $value): bool => ! is_null($value));

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
            array_replace($base->properties, $this->properties),
            $this->image ?? $base->image,
        );
    }

    /**
     * Determine if the builder holds no data.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->properties === [] && is_null($this->image);
    }

    /**
     * Get the Twitter card properties, falling back to the page title and description.
     *
     * @param string|null $title Page title
     * @param string|null $description Page description
     * @return array<string, string>
     */
    public function render(?string $title = null, ?string $description = null): array
    {
        $properties = $this->properties;

        if ($title && ! isset($properties['title'])) {
            $properties['title'] = $title;
        }

        if ($description && ! isset($properties['description'])) {
            $properties['description'] = $description;
        }

        return $properties;
    }

    /**
     * Convert this builder into its Head::toArray() value.
     *
     * @param \Crustum\Meta\Rendering\ResolvedHead $head Resolved head
     * @return array<string, mixed>
     */
    public function toHeadArray(ResolvedHead $head): array
    {
        return $this->headArray($head->title(), $head->description(), $head->openGraphImage());
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
        $properties = $this->render($head->title(), $head->description());
        $rendered = array_map(
            fn(string $name, string $content): string => $tags->meta('name', 'twitter:' . $name, $content),
            array_keys($properties),
            $properties,
        );

        $image = $this->headArrayImage($head->openGraphImage());

        if (is_null($image)) {
            return $rendered;
        }

        $rendered[] = $tags->meta('name', 'twitter:image', $image['url']);

        if (isset($image['alt'])) {
            $rendered[] = $tags->meta('name', 'twitter:image:alt', $image['alt']);
        }

        return $rendered;
    }

    /**
     * Resolve the image to render, falling back to the Open Graph image.
     *
     * @param ImageAttributes|null $fallback Fallback image
     * @return ImageAttributes|null
     */
    protected function headArrayImage(?array $fallback = null): ?array
    {
        return $this->image ?? $fallback;
    }

    /**
     * Get the builder's Head::toArray() value.
     *
     * @param string|null $title Page title
     * @param string|null $description Page description
     * @param ImageAttributes|null $fallbackImage Fallback image
     * @return array<string, mixed>
     */
    protected function headArray(?string $title = null, ?string $description = null, ?array $fallbackImage = null): array
    {
        return array_filter([
            ...$this->render($title, $description),
            'image' => $this->headArrayImage($fallbackImage),
        ]);
    }

    /**
     * Set non-null Twitter card properties.
     *
     * @param array<string, string|null> $properties Property values
     * @return void
     */
    protected function set(array $properties): void
    {
        foreach ($properties as $property => $value) {
            if (! is_null($value)) {
                $this->properties[$property] = $value;
            }
        }
    }

    /**
     * Cast a route attribute value to a Twitter card type.
     *
     * @param mixed $value Route attribute value
     * @return \Crustum\Meta\Enums\TwitterCard|string|null
     */
    protected static function twitterCard(mixed $value): TwitterCard|string|null
    {
        return $value instanceof TwitterCard || is_string($value) ? $value : null;
    }
}
