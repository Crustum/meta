<?php
declare(strict_types=1);

namespace Crustum\Meta;

use Crustum\Meta\Tags\TagBuilder;

/**
 * Normalized head state: a collection of tag builders with layer semantics.
 */
class HeadData
{
    /**
     * Tag builders keyed by class name.
     *
     * @var array<class-string<\Crustum\Meta\Tags\TagBuilder>, \Crustum\Meta\Tags\TagBuilder>
     */
    protected array $builders = [];

    /**
     * Get all tag builders keyed by class name.
     *
     * @return array<class-string<\Crustum\Meta\Tags\TagBuilder>, \Crustum\Meta\Tags\TagBuilder>
     */
    public function builders(): array
    {
        return $this->builders;
    }

    /**
     * Get a stored tag builder by class name.
     *
     * @param class-string<\Crustum\Meta\Tags\TagBuilder> $builder Tag builder class name
     * @return \Crustum\Meta\Tags\TagBuilder|null
     */
    public function get(string $builder): ?TagBuilder
    {
        return $this->builders[$builder] ?? null;
    }

    /**
     * Resolve a mutable tag builder instance, creating and storing it on first use.
     *
     * @template TBuilder of \Crustum\Meta\Tags\TagBuilder
     * @param class-string<TBuilder> $class Tag builder class name
     * @phpstan-return TBuilder
     */
    public function builder(string $class): TagBuilder
    {
        $builder = $this->builders[$class] ?? null;

        if (!$builder instanceof $class) {
            $builder = new $class();
            $this->builders[$class] = $builder;
        }

        return $builder;
    }

    /**
     * Overlay the given builder onto any existing builder of the same type.
     *
     * @param \Crustum\Meta\Tags\TagBuilder $builder Tag builder to overlay
     * @return static
     */
    public function overlayBuilder(TagBuilder $builder): static
    {
        $class = $builder::class;
        $this->builders[$class] = $builder->overlayOn($this->builders[$class] ?? null);

        return $this;
    }

    /**
     * Merge the given data over this data into a new instance.
     *
     * @param \Crustum\Meta\HeadData $data Head data to merge over this data
     * @return self
     */
    public function merge(HeadData $data): self
    {
        $merged = clone $this;

        foreach ($data->builders as $builder) {
            $merged->overlayBuilder($builder);
        }

        return $merged;
    }

    /**
     * Determine if the head data holds no populated builders.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return array_all($this->builders, fn(TagBuilder $builder): bool => $builder->isEmpty());
    }

    /**
     * Mark all builders as belonging to the defaults layer.
     *
     * @return static
     */
    public function asDefaults(): static
    {
        $defaults = clone $this;

        foreach ($defaults->builders as $class => $builder) {
            $defaults->builders[$class] = $builder->asDefaults();
        }

        return $defaults;
    }
}
