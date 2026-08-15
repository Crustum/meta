<?php
declare(strict_types=1);

namespace Crustum\Meta\Routing;

use Cake\Routing\Route\Route;
use Crustum\Meta\HeadData;
use Crustum\Meta\TagRegistry;
use InvalidArgumentException;

/**
 * @phpstan-type HeadAttributeArray array<mixed, mixed>
 */
class RouteAttributeParser
{
    public const HEAD = 'head';

    protected static int $metadataLayer = 0;

    /**
     * Normalize variadic head arguments, unwrapping a single array argument.
     *
     * @param array<int|string, mixed> $arguments Variadic head arguments
     * @return array<mixed, mixed>
     */
    public static function arguments(array $arguments): array
    {
        if (array_key_exists(0, $arguments) && is_array($arguments[0]) && count($arguments) === 1) {
            return $arguments[0];
        }

        return $arguments;
    }

    /**
     * Wrap head attributes in a cache-friendly route metadata layer.
     *
     * @param array<string, mixed> $attributes Head attributes
     * @return array<string, array<string, array<string, mixed>>>
     */
    public static function metadata(array $attributes): array
    {
        if ($attributes === []) {
            return [];
        }

        return [
            static::HEAD => [
                'layer-' . static::$metadataLayer++ => $attributes,
            ],
        ];
    }

    /**
     * Wrap named head arguments in a cache-friendly route metadata layer,
     * discarding arguments that were not provided.
     *
     * @param array<mixed, mixed> $arguments Named head arguments
     * @return array<string, array<string, array<string, mixed>>>
     */
    public static function metadataFromArguments(array $arguments): array
    {
        $extensions = $arguments['extensions'] ?? [];

        unset($arguments['extensions']);

        if (is_array($extensions)) {
            $arguments += $extensions;
        }

        $attributes = [];

        foreach ($arguments as $key => $value) {
            if (is_string($key) && ! is_null($value)) {
                $attributes[$key] = $value;
            }
        }

        return static::metadata($attributes);
    }

    /**
     * Get the head attribute layers stored on the route's options.
     *
     * @param \Cake\Routing\Route\Route $route The matched route
     * @return array<int, array<mixed, mixed>>
     */
    public static function routeMetadata(Route $route): array
    {
        $metadata = $route->options[static::HEAD] ?? [];

        if (! is_array($metadata) || $metadata === []) {
            return [];
        }

        if (array_is_list($metadata)) {
            return array_values(array_filter($metadata, is_array(...)));
        }

        if (array_all(array_keys($metadata), static fn(mixed $key): bool => is_string($key) && preg_match('/^layer-\d+$/', $key) === 1)) {
            return array_values(array_filter($metadata, is_array(...)));
        }

        return [$metadata];
    }

    /**
     * Apply head attributes on top of the given head data.
     *
     * @param \Crustum\Meta\HeadData|array<\Crustum\Meta\HeadData>|array<mixed, mixed>|null $attributes Head attributes
     */
    public static function apply(HeadData $head, HeadData|array|null $attributes, TagRegistry $registry): HeadData
    {
        if (is_null($attributes)) {
            return $head;
        }

        if ($attributes instanceof HeadData) {
            return $head->merge($attributes);
        }

        return static::fill($head, static::named($attributes), $registry);
    }

    /**
     * Fill head data from named route attributes.
     *
     * @param array<string, mixed> $attributes Named route attributes
     * @throws \InvalidArgumentException When an attribute is unknown or invalid
     */
    protected static function fill(HeadData $head, array $attributes, TagRegistry $registry): HeadData
    {
        $head = clone $head;

        $routeAttributeKeys = $registry->routeAttributeKeys();

        foreach ($attributes as $key => $value) {
            if (! isset($routeAttributeKeys[$key])) {
                throw new InvalidArgumentException(sprintf(
                    'Unknown route head attribute [%s]. Supported attributes are: %s.',
                    $key,
                    implode(', ', array_keys($routeAttributeKeys)),
                ));
            }

            $builderClass = $routeAttributeKeys[$key];

            $builder = $builderClass::fromRouteAttribute($key, $value);

            if (is_null($builder) || ($builder->isEmpty() && ! static::isEmptyRouteAttributeValue($value))) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid value for route head attribute [%s].',
                    $key,
                ));
            }

            $head->overlayBuilder($builder);
        }

        return $head;
    }

    /**
     * Determine if a route attribute value represents an empty collection.
     *
     * @param mixed $value Route attribute value
     * @return bool
     */
    protected static function isEmptyRouteAttributeValue(mixed $value): bool
    {
        return $value === [];
    }

    /**
     * @param array<mixed, mixed> $attributes Head attributes
     * @return array<string, mixed>
     * @throws \InvalidArgumentException When an attribute is not named
     */
    protected static function named(array $attributes): array
    {
        $named = [];

        foreach ($attributes as $key => $value) {
            if (is_string($key)) {
                $named[$key] = $value;

                continue;
            }

            throw new InvalidArgumentException('Route head attributes must be named.');
        }

        return $named;
    }
}
