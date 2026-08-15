<?php
declare(strict_types=1);

namespace Crustum\Meta\Tags;

use Crustum\Meta\Rendering\ResolvedHead;
use Crustum\Meta\Rendering\TagRenderer;
use Override;

/**
 * Alternate link tag builder.
 *
 * @phpstan-consistent-constructor
 */
class AlternateLinks extends GroupedTagBuilder
{
    /**
     * Constructor.
     *
     * @param array<string, string> $links Alternate links keyed by hreflang
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
        return 'alternates';
    }

    /**
     * The dot-notated key this builder occupies in the Head::toArray() result.
     *
     * @return string
     */
    #[Override]
    public static function headArrayKey(): string
    {
        return 'links.alternates';
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
        if ($key !== 'alternates' || ! is_array($value)) {
            return null;
        }

        $links = new self();

        foreach ($value as $locale => $href) {
            if (is_string($href)) {
                $links->link((string)$locale, $href);
            }
        }

        return $links;
    }

    /**
     * Add an alternate link.
     *
     * @param string $locale Locale / hreflang value
     * @param string $href Link href
     * @return static
     */
    public function link(string $locale, string $href): static
    {
        $this->links[$locale] = $href;

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
     * @return array<string, string>
     */
    public function toHeadArray(ResolvedHead $head): array
    {
        return $this->links;
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
        return array_map(
            fn(string $hreflang, string $href): string => $tags->linkWithAttributes('alternate', ['hreflang' => $hreflang, 'href' => $href]),
            array_keys($this->links),
            $this->links,
        );
    }
}
