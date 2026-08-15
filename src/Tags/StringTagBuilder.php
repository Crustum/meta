<?php
declare(strict_types=1);

namespace Crustum\Meta\Tags;

use Crustum\Meta\Rendering\ResolvedHead;

/**
 * A tag builder holding a single string value.
 *
 * @phpstan-consistent-constructor
 */
abstract class StringTagBuilder extends TagBuilder
{
    /**
     * Constructor.
     *
     * @param string|null $value String value
     */
    public function __construct(protected ?string $value = null)
    {
    }

    /**
     * Create a builder with the given value.
     *
     * @param string $value String value
     * @return static
     */
    public static function make(string $value): static
    {
        return new static($value);
    }

    /**
     * Create a builder from a route attribute value.
     *
     * @param string $key Route attribute key
     * @param mixed $value Route attribute value
     * @return static|null
     */
    public static function fromRouteAttribute(string $key, mixed $value): ?self
    {
        return $key === static::key() && is_string($value)
            ? static::make($value)
            : null;
    }

    /**
     * Merge this builder over the given base builder, preferring this builder's values.
     *
     * @param \Crustum\Meta\Tags\TagBuilder|null $base Base builder
     * @return static
     */
    public function overlayOn(?TagBuilder $base): static
    {
        if (!$base instanceof self) {
            return $this;
        }

        return new static($this->value ?? $base->value);
    }

    /**
     * Determine if the builder holds no value.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return is_null($this->value);
    }

    /**
     * Render the string value.
     *
     * @return string|null
     */
    public function render(): ?string
    {
        return $this->value;
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
}
