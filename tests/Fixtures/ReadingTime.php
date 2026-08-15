<?php
declare(strict_types=1);

namespace Crustum\Meta\Test\Fixtures;

use Crustum\Meta\Rendering\ResolvedHead;
use Crustum\Meta\Rendering\TagRenderer;
use Crustum\Meta\Tags\TagBuilder;
use Override;

/**
 * Test tag builder exposing a single integer head array value.
 *
 * @phpstan-consistent-constructor
 */
class ReadingTime extends TagBuilder
{
    /**
     * Constructor.
     *
     * @param int|null $minutes Estimated reading time in minutes
     */
    public function __construct(protected ?int $minutes = null)
    {
    }

    /**
     * @return string
     */
    public static function key(): string
    {
        return 'readingTime';
    }

    /**
     * @param int $minutes Estimated reading time in minutes
     * @return static
     */
    public static function make(int $minutes): static
    {
        return new static($minutes);
    }

    /**
     * @inheritDoc
     */
    public static function fromRouteAttribute(string $key, mixed $value): ?static
    {
        return $key === 'readingTime' && is_int($value) ? static::make($value) : null;
    }

    /**
     * @param \Crustum\Meta\Rendering\ResolvedHead $head Resolved head
     * @return int|null
     */
    public function toHeadArray(ResolvedHead $head): ?int
    {
        return $this->minutes;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return is_null($this->minutes)
            ? []
            : [
                $tags->meta('name', 'twitter:label1', 'Reading time'),
                $tags->meta('name', 'twitter:data1', $this->minutes . ' min read'),
            ];
    }

    /**
     * @inheritDoc
     */
    public function overlayOn(?TagBuilder $base): static
    {
        if (!$base instanceof static) {
            return $this;
        }

        return new static($this->minutes ?? $base->minutes);
    }

    /**
     * @inheritDoc
     */
    public function isEmpty(): bool
    {
        return is_null($this->minutes);
    }
}
