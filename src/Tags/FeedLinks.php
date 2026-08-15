<?php
declare(strict_types=1);

namespace Crustum\Meta\Tags;

use Crustum\Meta\Rendering\ResolvedHead;
use Crustum\Meta\Rendering\TagRenderer;
use Override;

/**
 * Feed link tag builder.
 *
 * @phpstan-consistent-constructor
 * @phpstan-type FeedAttributes array{href: string, title: string, type: string}
 */
class FeedLinks extends GroupedTagBuilder
{
    /**
     * Constructor.
     *
     * @param array<string, FeedAttributes> $feeds Feeds keyed by href
     */
    public function __construct(protected array $feeds = [])
    {
    }

    /**
     * The unique name identifying this builder within the head.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'feeds';
    }

    /**
     * The dot-notated key this builder occupies in the Head::toArray() result.
     *
     * @return string
     */
    #[Override]
    public static function headArrayKey(): string
    {
        return 'links.feeds';
    }

    /**
     * The route attribute keys this builder accepts values for.
     *
     * @return array<int, string>
     */
    #[Override]
    public static function routeAttributeKeys(): array
    {
        return ['feed'];
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
        if ($key !== 'feed' || ! is_array($value)) {
            return null;
        }

        $links = new self();

        foreach (self::feedAttributes($value) as $href => $feed) {
            if (!$links->addRouteAttributeFeed($href, $feed)) {
                return null;
            }
        }

        return $links;
    }

    /**
     * Add a feed.
     *
     * @param string $href Feed href
     * @param string $title Feed title
     * @param string $type Feed type
     * @return static
     */
    public function feed(string $href, string $title, string $type = 'rss'): static
    {
        $this->feeds[$href] = [
            'href' => $href,
            'title' => $title,
            'type' => $type,
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

        return new static(array_replace($base->feeds, $this->feeds));
    }

    /**
     * Determine if the builder holds no data.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->feeds === [];
    }

    /**
     * Convert this builder into its Head::toArray() value.
     *
     * @param \Crustum\Meta\Rendering\ResolvedHead $head Resolved head
     * @return array<int, FeedAttributes>
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
        return array_map(function (array $feed) use ($tags): string {
            $type = $feed['type'] === 'atom' ? 'application/atom+xml' : 'application/rss+xml';

            return $tags->linkWithAttributes('alternate', [
                'type' => $type,
                'title' => $feed['title'],
                'href' => $feed['href'],
            ]);
        }, $this->headArray());
    }

    /**
     * Get the builder's Head::toArray() value.
     *
     * @return array<int, FeedAttributes>
     */
    protected function headArray(): array
    {
        return array_values($this->feeds);
    }

    /**
     * Add a feed from a route attribute definition.
     *
     * @param mixed $href Feed href
     * @param mixed $feed Feed value
     * @return bool
     */
    private function addRouteAttributeFeed(mixed $href, mixed $feed): bool
    {
        if (is_string($href) && is_string($feed)) {
            $this->feed($href, $feed);

            return true;
        }

        $url = $this->routeAttributeHref($href, $feed);

        if (! is_array($feed) || ! is_string($feed['title'] ?? null) || is_null($url)) {
            return false;
        }

        $this->feed($url, $feed['title'], self::string($feed['type'] ?? null) ?? 'rss');

        return true;
    }

    /**
     * Normalize a route attribute value into a list of feed definitions,
     * treating an attribute map containing any single-feed key as one feed.
     *
     * @param array<mixed, mixed> $value Route attribute values
     * @return array<mixed, mixed>
     */
    private static function feedAttributes(array $value): array
    {
        if (array_is_list($value)) {
            return $value;
        }

        if (array_key_exists('href', $value) || array_key_exists('title', $value) || array_key_exists('type', $value)) {
            return [$value];
        }

        return $value;
    }

    /**
     * Resolve the feed href from a route attribute definition.
     *
     * @param mixed $href Feed href
     * @param array<mixed, mixed> $feed Feed value
     * @return string|null
     */
    private function routeAttributeHref(mixed $href, array $feed): ?string
    {
        return self::string($feed['href'] ?? null) ?? self::string($href);
    }
}
