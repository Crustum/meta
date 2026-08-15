<?php
declare(strict_types=1);

namespace Crustum\Meta\Tags;

use Crustum\Meta\Rendering\ResolvedHead;
use Crustum\Meta\Rendering\TagRenderer;
use Crustum\Meta\Schema\SchemaObject;
use Override;

/**
 * JSON-LD schema tag builder.
 *
 * @phpstan-consistent-constructor
 * @phpstan-type SchemaData array<string, mixed>
 */
class Schemas extends GroupedTagBuilder
{
    /**
     * Constructor.
     *
     * @param array<string, \Crustum\Meta\Schema\SchemaObject|SchemaData> $schemas Schemas
     */
    public function __construct(protected array $schemas = [])
    {
    }

    /**
     * The unique name identifying this builder within the head.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'schemas';
    }

    /**
     * The route attribute keys this builder accepts values for.
     *
     * @return array<int, string>
     */
    #[Override]
    public static function routeAttributeKeys(): array
    {
        return ['schema'];
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
        if ($key !== 'schema' || (!$value instanceof SchemaObject && ! is_array($value))) {
            return null;
        }

        $builder = new self();

        if ($value instanceof SchemaObject || ! array_is_list($value)) {
            return $builder->schema($value instanceof SchemaObject ? $value : self::named($value));
        }

        foreach ($value as $schema) {
            if ($schema instanceof SchemaObject || is_array($schema)) {
                $builder->schema($schema instanceof SchemaObject ? $schema : self::named($schema));
            }
        }

        return $builder;
    }

    /**
     * Add a schema, keyed by content so identical schemas render once.
     *
     * @param \Crustum\Meta\Schema\SchemaObject|SchemaData $schema Schema
     * @return static
     */
    public function schema(SchemaObject|array $schema): static
    {
        $this->schemas[$schema instanceof SchemaObject ? spl_object_hash($schema) : md5(json_encode($schema, JSON_THROW_ON_ERROR))] = $schema;

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

        return new static(array_replace($base->schemas, $this->schemas));
    }

    /**
     * Determine if the builder holds no data.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->schemas === [];
    }

    /**
     * Convert this builder into its Head::toArray() value.
     *
     * @param \Crustum\Meta\Rendering\ResolvedHead $head Resolved head
     * @return array<int, SchemaData>
     */
    public function toHeadArray(ResolvedHead $head): array
    {
        return array_map(function (SchemaObject|array $schema) use ($head): array {
            $schemaData = $schema instanceof SchemaObject ? $schema->toJsonLd() : $schema;

            $head->validateSchema($schemaData);

            return $schemaData;
        }, $this->headArray());
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
            $tags->jsonLd(...),
            $this->toHeadArray($head),
        );
    }

    /**
     * Get the builder's Head::toArray() value.
     *
     * @return array<int, \Crustum\Meta\Schema\SchemaObject|SchemaData>
     */
    protected function headArray(): array
    {
        return array_values($this->schemas);
    }
}
