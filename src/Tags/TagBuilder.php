<?php
declare(strict_types=1);

namespace Crustum\Meta\Tags;

use BackedEnum;
use Crustum\Meta\Rendering\ResolvedHead;
use Crustum\Meta\Rendering\TagRenderer;

/**
 * Base contract for a head tag builder.
 *
 * Tag builders normalize head state into values and rendered tags. Concrete
 * builders are constructed directly, without a container.
 */
abstract class TagBuilder
{
    /**
     * The unique name identifying this builder within the head.
     *
     * @return string
     */
    abstract public static function key(): string;

    /**
     * Merge this builder over the given base builder, preferring this builder's values.
     *
     * @param static|null $base Base builder
     * @return static
     */
    abstract public function overlayOn(?self $base): static;

    /**
     * Determine if the builder holds no data.
     *
     * @return bool
     */
    abstract public function isEmpty(): bool;

    /**
     * The dot-notated key this builder occupies in the Head::toArray() result.
     *
     * @return string
     */
    public static function headArrayKey(): string
    {
        return static::key();
    }

    /**
     * The value used in Head::toArray() when this builder has no data.
     *
     * @return mixed
     */
    public static function headArrayDefault(): mixed
    {
        return null;
    }

    /**
     * The route attribute keys this builder accepts values for.
     *
     * @return array<int, string>
     */
    public static function routeAttributeKeys(): array
    {
        return [static::key()];
    }

    /**
     * Create a builder from a route attribute value, or null when the value is invalid.
     *
     * @param string $key Route attribute key
     * @param mixed $value Route attribute value
     * @return static|null
     */
    public static function fromRouteAttribute(string $key, mixed $value): ?self
    {
        return null;
    }

    /**
     * Determine if the builder should render even when no data was set.
     *
     * @param \Crustum\Meta\Rendering\ResolvedHead $head Resolved head
     * @return bool
     */
    public static function rendersWhenEmpty(ResolvedHead $head): bool
    {
        return false;
    }

    /**
     * Mark the builder's data as belonging to the defaults layer.
     *
     * @return static
     */
    public function asDefaults(): static
    {
        return $this;
    }

    /**
     * Convert this builder into its Head::toArray() value.
     *
     * @param \Crustum\Meta\Rendering\ResolvedHead $head Resolved head
     * @return mixed
     */
    public function toHeadArray(ResolvedHead $head): mixed
    {
        return null;
    }

    /**
     * Convert this builder into the HTML tags rendered by @head.
     *
     * @param \Crustum\Meta\Rendering\ResolvedHead $head Resolved head
     * @param \Crustum\Meta\Rendering\TagRenderer $tags Tag renderer
     * @return array<int, string>
     */
    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        return [];
    }

    /**
     * Cast a route attribute value to string, or null when invalid.
     *
     * @param mixed $value Route attribute value
     * @return string|null
     */
    protected static function string(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }

    /**
     * Cast a route attribute value to string, resolving backed enums.
     *
     * @param mixed $value Route attribute value
     * @return string|null
     */
    protected static function stringOrBackedEnum(mixed $value): ?string
    {
        if (is_string($value)) {
            return $value;
        }

        if ($value instanceof BackedEnum && is_string($value->value)) {
            return $value->value;
        }

        return null;
    }

    /**
     * Cast a route attribute value to boolean, or null when invalid.
     *
     * @param mixed $value Route attribute value
     * @return bool|null
     */
    protected static function bool(mixed $value): ?bool
    {
        return is_bool($value) ? $value : null;
    }

    /**
     * Cast a route attribute value to integer, or null when invalid.
     *
     * @param mixed $value Route attribute value
     * @return int|null
     */
    protected static function int(mixed $value): ?int
    {
        return is_int($value) ? $value : null;
    }

    /**
     * Normalize a route attribute value into a list of items.
     *
     * @param mixed $value Route attribute value
     * @return array<int, mixed>
     */
    protected static function items(mixed $value): array
    {
        return is_array($value) && array_is_list($value) ? $value : [$value];
    }

    /**
     * Filter an array down to its string-keyed values.
     *
     * @param array<mixed, mixed> $values Values
     * @return array<string, mixed>
     */
    protected static function named(array $values): array
    {
        $named = [];

        foreach ($values as $key => $value) {
            if (is_string($key)) {
                $named[$key] = $value;
            }
        }

        return $named;
    }
}
