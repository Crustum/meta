<?php
declare(strict_types=1);

namespace Crustum\Meta\Tags;

use Crustum\Meta\Rendering\ResolvedHead;
use Crustum\Meta\Rendering\TagRenderer;
use Override;

/**
 * Title tag builder.
 *
 * @phpstan-consistent-constructor
 */
class Title extends TagBuilder
{
    /**
     * Constructor.
     *
     * @param string|null $value Title
     * @param bool|null $exact Render exactly, skipping prefix and suffix
     * @param string|null $prefix Title prefix
     * @param string|null $suffix Title suffix
     */
    public function __construct(
        protected ?string $value = null,
        protected ?bool $exact = null,
        protected ?string $prefix = null,
        protected ?string $suffix = null,
    ) {
    }

    /**
     * The unique name identifying this builder within the head.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'title';
    }

    /**
     * Create a title builder.
     *
     * @param string $value Title
     * @param string|null $prefix Title prefix
     * @param string|null $suffix Title suffix
     * @param bool|null $exact Render exactly, skipping prefix and suffix
     * @return self
     */
    public static function make(string $value, ?string $prefix = null, ?string $suffix = null, ?bool $exact = null): self
    {
        return new self($value, $exact ?? false, $prefix, $suffix);
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
        if ($key !== 'title') {
            return null;
        }

        $title = $value;

        if (is_string($title)) {
            return self::make($title);
        }

        if (! is_array($title) || ! is_string($title['value'] ?? null)) {
            return null;
        }

        return self::make(
            $title['value'],
            prefix: self::string($title['prefix'] ?? null),
            suffix: self::string($title['suffix'] ?? null),
            exact: self::bool($title['exact'] ?? null),
        );
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
            $this->value ?? $base->value,
            $this->exact ?? $base->exact,
            $this->prefix ?? $base->prefix,
            $this->suffix ?? $base->suffix,
        );
    }

    /**
     * Default titles render exactly; the prefix and suffix only decorate page titles.
     *
     * @return static
     */
    #[Override]
    public function asDefaults(): static
    {
        return is_null($this->value)
            ? $this
            : new static($this->value, true, $this->prefix, $this->suffix);
    }

    /**
     * Determine if the builder holds no data.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return is_null($this->value)
            && is_null($this->exact)
            && is_null($this->prefix)
            && is_null($this->suffix);
    }

    /**
     * Render the resolved title.
     *
     * @return string|null
     */
    public function render(): ?string
    {
        if (is_null($this->value)) {
            return null;
        }

        if ($this->exact === true) {
            return $this->value;
        }

        return ($this->prefix ?? '') . $this->value . ($this->suffix ?? '');
    }

    /**
     * Convert this builder into its Head::toArray() value.
     *
     * @param \Crustum\Meta\Rendering\ResolvedHead $head Resolved head
     * @return string|null
     */
    public function toHeadArray(ResolvedHead $head): ?string
    {
        return $this->render();
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
        return ($title = $this->render()) ? [$tags->title($title)] : [];
    }
}
