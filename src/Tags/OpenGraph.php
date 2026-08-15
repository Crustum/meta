<?php
declare(strict_types=1);

namespace Crustum\Meta\Tags;

use Crustum\Meta\Enums\ImageType;
use Crustum\Meta\Enums\OgType;
use Crustum\Meta\Rendering\ResolvedHead;
use Crustum\Meta\Rendering\TagRenderer;
use Override;

/**
 * Open Graph meta tag builder.
 *
 * @phpstan-consistent-constructor
 * @phpstan-type MediaAttributes array{url: string, alt?: string|null, width?: int|null, height?: int|null, type?: string|null, secureUrl?: string|null}
 */
class OpenGraph extends GroupedTagBuilder
{
    /**
     * Whether the properties belong to the defaults layer.
     *
     * @var bool
     */
    protected bool $defaults = false;

    /**
     * Constructor.
     *
     * @param array<string, string> $properties Open Graph properties
     * @param array<string, MediaAttributes> $images Media images
     * @param array<string, MediaAttributes> $videos Media videos
     * @param array<string, MediaAttributes> $audios Media audios
     */
    public function __construct(
        protected array $properties = [],
        protected array $images = [],
        protected array $videos = [],
        protected array $audios = [],
    ) {
    }

    /**
     * The unique name identifying this builder within the head.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'openGraph';
    }

    /**
     * The route attribute keys this builder accepts values for.
     *
     * @return array<int, string>
     */
    #[Override]
    public static function routeAttributeKeys(): array
    {
        return ['og', 'ogImage', 'ogVideo', 'ogAudio'];
    }

    /**
     * Create an Open Graph builder.
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
     * @return self
     */
    public static function make(
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
    ): self {
        $openGraph = new self();

        $openGraph->set([
            'type' => $type instanceof OgType ? $type->value : $type,
            'title' => $title,
            'description' => $description,
            'url' => $url,
            'site_name' => $siteName,
            'locale' => $locale,
            'determiner' => $determiner,
        ]);

        if (! is_null($image)) {
            $openGraph->image($image);
        }

        if (! is_null($video)) {
            $openGraph->video($video);
        }

        if (! is_null($audio)) {
            $openGraph->audio($audio);
        }

        return $openGraph;
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
            'og' => is_array($value) ? self::fromAttributes($value) : null,
            'ogImage' => self::fromImageAttributes($value),
            'ogVideo' => self::fromVideoAttributes($value),
            'ogAudio' => self::fromAudioAttributes($value),
            default => null,
        };
    }

    /**
     * Create an Open Graph builder from an array of property values.
     *
     * @param array<mixed, mixed> $values Property values
     * @return self
     */
    public static function fromAttributes(array $values): self
    {
        return self::make(
            type: self::ogType($values['type'] ?? null),
            title: self::string($values['title'] ?? null),
            description: self::string($values['description'] ?? null),
            url: self::string($values['url'] ?? null),
            image: self::string($values['image'] ?? null),
            video: self::string($values['video'] ?? null),
            audio: self::string($values['audio'] ?? null),
            siteName: self::string($values['siteName'] ?? null),
            locale: self::string($values['locale'] ?? null),
            determiner: self::string($values['determiner'] ?? null),
        );
    }

    /**
     * Create an Open Graph builder from image attributes.
     *
     * @param mixed $images Image values
     * @return self
     */
    public static function fromImageAttributes(mixed $images): self
    {
        $openGraph = new self();

        foreach (self::items($images) as $image) {
            if (is_string($image)) {
                $openGraph->image($image);
            }

            if (is_array($image) && is_string($image['url'] ?? null)) {
                $openGraph->image(
                    $image['url'],
                    alt: self::string($image['alt'] ?? null),
                    width: self::int($image['width'] ?? null),
                    height: self::int($image['height'] ?? null),
                    type: self::imageType($image['type'] ?? null),
                    secureUrl: self::string($image['secureUrl'] ?? null),
                );
            }
        }

        return $openGraph;
    }

    /**
     * Create an Open Graph builder from video attributes.
     *
     * @param mixed $videos Video values
     * @return self
     */
    public static function fromVideoAttributes(mixed $videos): self
    {
        $openGraph = new self();

        foreach (self::items($videos) as $video) {
            if (is_string($video)) {
                $openGraph->video($video);
            }

            if (is_array($video) && is_string($video['url'] ?? null)) {
                $openGraph->video(
                    $video['url'],
                    alt: self::string($video['alt'] ?? null),
                    width: self::int($video['width'] ?? null),
                    height: self::int($video['height'] ?? null),
                    type: self::string($video['type'] ?? null),
                    secureUrl: self::string($video['secureUrl'] ?? null),
                );
            }
        }

        return $openGraph;
    }

    /**
     * Create an Open Graph builder from audio attributes.
     *
     * @param mixed $audios Audio values
     * @return self
     */
    public static function fromAudioAttributes(mixed $audios): self
    {
        $openGraph = new self();

        foreach (self::items($audios) as $audio) {
            if (is_string($audio)) {
                $openGraph->audio($audio);
            }

            if (is_array($audio) && is_string($audio['url'] ?? null)) {
                $openGraph->audio(
                    $audio['url'],
                    type: self::string($audio['type'] ?? null),
                    secureUrl: self::string($audio['secureUrl'] ?? null),
                );
            }
        }

        return $openGraph;
    }

    /**
     * Add an image.
     *
     * @param string $url Image URL
     * @param string|null $alt Alt text
     * @param int|null $width Width
     * @param int|null $height Height
     * @param \Crustum\Meta\Enums\ImageType|string|null $type Image type
     * @param string|null $secureUrl Secure URL
     * @return static
     */
    public function image(
        string $url,
        ?string $alt = null,
        ?int $width = null,
        ?int $height = null,
        ImageType|string|null $type = null,
        ?string $secureUrl = null,
    ): static {
        $this->images[$url] = self::media($url, $alt, $width, $height, self::imageType($type), $secureUrl);

        return $this;
    }

    /**
     * Add a video.
     *
     * @param string $url Video URL
     * @param string|null $alt Alt text
     * @param int|null $width Width
     * @param int|null $height Height
     * @param string|null $type Video type
     * @param string|null $secureUrl Secure URL
     * @return static
     */
    public function video(
        string $url,
        ?string $alt = null,
        ?int $width = null,
        ?int $height = null,
        ?string $type = null,
        ?string $secureUrl = null,
    ): static {
        $this->videos[$url] = self::media($url, $alt, $width, $height, $type, $secureUrl);

        return $this;
    }

    /**
     * Add an audio file.
     *
     * @param string $url Audio URL
     * @param string|null $type Audio type
     * @param string|null $secureUrl Secure URL
     * @return static
     */
    public function audio(string $url, ?string $type = null, ?string $secureUrl = null): static
    {
        $this->audios[$url] = self::media($url, type: $type, secureUrl: $secureUrl);

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

        $overlaid = new static(
            array_replace($base->properties, $this->properties),
            $this->overlayMedia($base->images, $this->images, $base->defaults),
            $this->overlayMedia($base->videos, $this->videos, $base->defaults),
            $this->overlayMedia($base->audios, $this->audios, $base->defaults),
        );

        $overlaid->defaults = $base->defaults && !$this->hasMedia();

        return $overlaid;
    }

    /**
     * Mark the builder's data as belonging to the defaults layer.
     *
     * @return static
     */
    #[Override]
    public function asDefaults(): static
    {
        $defaults = clone $this;
        $defaults->defaults = true;

        return $defaults;
    }

    /**
     * Media inherited from the defaults layer is a fallback, so media defined
     * by a higher layer replaces it rather than merging with it.
     *
     * @param array<string, MediaAttributes> $base Base media
     * @param array<string, MediaAttributes> $overlay Overlay media
     * @param bool $baseIsDefaults Whether the base is the defaults layer
     * @return array<string, MediaAttributes>
     */
    protected function overlayMedia(array $base, array $overlay, bool $baseIsDefaults): array
    {
        if ($overlay === []) {
            return $base;
        }

        return $baseIsDefaults ? $overlay : array_replace($base, $overlay);
    }

    /**
     * Determine if the builder holds media.
     *
     * @return bool
     */
    protected function hasMedia(): bool
    {
        return $this->images !== [] || $this->videos !== [] || $this->audios !== [];
    }

    /**
     * Determine if the builder holds no data.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->properties === []
            && $this->images === []
            && $this->videos === []
            && $this->audios === [];
    }

    /**
     * Get the Open Graph properties, falling back to the page title and description.
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
     * Get the Open Graph images.
     *
     * @return array<string, MediaAttributes>
     */
    public function images(): array
    {
        return $this->images;
    }

    /**
     * Convert this builder into its Head::toArray() value.
     *
     * @param \Crustum\Meta\Rendering\ResolvedHead $head Resolved head
     * @return array<string, mixed>
     */
    public function toHeadArray(ResolvedHead $head): array
    {
        return $this->headArray($head->title(), $head->description());
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
        $propertyTags = array_map(
            fn(string $property, string $content): string => $tags->meta('property', 'og:' . $property, $content),
            array_keys($properties),
            $properties,
        );

        return [
            ...$propertyTags,
            ...$this->mediaTags($tags, 'image', $this->images),
            ...$this->mediaTags($tags, 'video', $this->videos),
            ...$this->mediaTags($tags, 'audio', $this->audios),
        ];
    }

    /**
     * Get the builder's Head::toArray() value.
     *
     * @param string|null $title Page title
     * @param string|null $description Page description
     * @return array<string, mixed>
     */
    protected function headArray(?string $title = null, ?string $description = null): array
    {
        return array_filter([
            ...$this->render($title, $description),
            'images' => array_values($this->images),
            'videos' => array_values($this->videos),
            'audios' => array_values($this->audios),
        ]);
    }

    /**
     * Set non-null Open Graph properties.
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
     * Normalize media attributes, dropping null values.
     *
     * @param string $url Media URL
     * @param string|null $alt Alt text
     * @param int|null $width Width
     * @param int|null $height Height
     * @param string|null $type Media type
     * @param string|null $secureUrl Secure URL
     * @return MediaAttributes
     */
    protected static function media(
        string $url,
        ?string $alt = null,
        ?int $width = null,
        ?int $height = null,
        ?string $type = null,
        ?string $secureUrl = null,
    ): array {
        return array_filter([
            'url' => $url,
            'alt' => $alt,
            'width' => $width,
            'height' => $height,
            'type' => $type,
            'secureUrl' => $secureUrl,
        ], fn($value): bool => ! is_null($value));
    }

    /**
     * Cast a route attribute value to an Open Graph type.
     *
     * @param mixed $value Route attribute value
     * @return \Crustum\Meta\Enums\OgType|string|null
     */
    protected static function ogType(mixed $value): OgType|string|null
    {
        return $value instanceof OgType || is_string($value) ? $value : null;
    }

    /**
     * Cast a route attribute value to an image type string.
     *
     * @param mixed $value Route attribute value
     * @return string|null
     */
    protected static function imageType(mixed $value): ?string
    {
        return $value instanceof ImageType ? $value->value : self::string($value);
    }

    /**
     * Render the media meta tags for a property.
     *
     * @param \Crustum\Meta\Rendering\TagRenderer $tags Tag renderer
     * @param string $property Media property
     * @param array<string, MediaAttributes> $media Media items
     * @return array<int, string>
     */
    protected function mediaTags(TagRenderer $tags, string $property, array $media): array
    {
        $attributes = [
            'secureUrl' => 'secure_url',
            'type' => 'type',
            'width' => 'width',
            'height' => 'height',
            'alt' => 'alt',
        ];

        $rendered = [];

        foreach ($media as $item) {
            $rendered[] = $tags->meta('property', 'og:' . $property, $item['url']);

            foreach ($attributes as $attribute => $name) {
                if (isset($item[$attribute])) {
                    $rendered[] = $tags->meta('property', 'og:' . $property . ':' . $name, $item[$attribute]);
                }
            }
        }

        return $rendered;
    }
}
