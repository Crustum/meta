<?php
declare(strict_types=1);

namespace Crustum\Meta\Schema;

use BadMethodCallException;
use Cake\Utility\Inflector;
use Crustum\Meta\SchemaType;
use DateTimeInterface;
use JsonSerializable;
use ReflectionClass;

/**
 * Base JSON-LD schema object.
 *
 * Schema objects coerce nested schema objects and dates and serialize into
 * schema.org JSON-LD arrays.
 */
abstract class SchemaObject implements JsonSerializable
{
    /**
     * Schema.org type name.
     *
     * @var string
     */
    protected string $type;

    /**
     * Schema properties.
     *
     * @var array<string, mixed>
     */
    protected array $properties = [];

    /**
     * Constructor.
     *
     * @param string|null $type Schema.org type name, resolved from the SchemaType attribute when null
     */
    public function __construct(?string $type = null)
    {
        $this->type = $type ?? $this->typeFromAttribute();
    }

    /**
     * Set a schema property, coercing nested schema objects and dates.
     *
     * @param string $property Property name
     * @param mixed $value Property value
     * @return static
     */
    public function set(string $property, mixed $value): static
    {
        $this->properties[$property] = $this->coerce($value);

        return $this;
    }

    /**
     * Set a date property, formatting DateTimeInterface values as ISO-8601.
     *
     * @param string $property Property name
     * @param \DateTimeInterface|string $date Date value
     * @return static
     */
    public function date(string $property, DateTimeInterface|string $date): static
    {
        return $this->set($property, $date instanceof DateTimeInterface ? $date->format(DateTimeInterface::ATOM) : $date);
    }

    /**
     * Get the schema object as an array without the JSON-LD context.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['@type' => $this->type] + $this->properties;
    }

    /**
     * Get the schema object as a JSON-LD array with the schema.org context.
     *
     * @return array<string, mixed>
     */
    public function toJsonLd(): array
    {
        return ['@context' => 'https://schema.org'] + $this->toArray();
    }

    /**
     * Get the schema object as a JSON-LD array for json_encode().
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toJsonLd();
    }

    /**
     * Dynamically set the schema property named by the called method.
     *
     * @param string $method Property name
     * @param array<int, mixed> $parameters Method parameters
     * @return static
     * @throws \BadMethodCallException When more than one value is passed
     */
    public function __call(string $method, array $parameters): static
    {
        if (count($parameters) > 1) {
            throw new BadMethodCallException(sprintf(
                'Magic schema property setters accept a single value, and [%s] does not define a [%s] method.',
                static::class,
                $method,
            ));
        }

        return $this->set($method, $parameters[0] ?? true);
    }

    /**
     * Coerce a value into its serialized form, flattening nested schema objects and dates.
     *
     * @param mixed $value Property value
     * @return mixed
     */
    protected function coerce(mixed $value): mixed
    {
        if ($value instanceof SchemaObject) {
            return $value->toArray();
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format(DateTimeInterface::ATOM);
        }

        if (is_array($value)) {
            return array_map($this->coerce(...), $value);
        }

        return $value;
    }

    /**
     * Resolve the schema.org type from the SchemaType attribute or the class name.
     *
     * @return string
     */
    protected function typeFromAttribute(): string
    {
        $reflection = new ReflectionClass($this);
        $attributes = $reflection->getAttributes(SchemaType::class);

        return $attributes === []
            ? Inflector::humanize(Inflector::underscore($reflection->getShortName()))
            : $attributes[0]->newInstance()->name;
    }
}
