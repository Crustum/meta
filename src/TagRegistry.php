<?php
declare(strict_types=1);

namespace Crustum\Meta;

use Crustum\Meta\Tags\AlternateLinks;
use Crustum\Meta\Tags\Canonical;
use Crustum\Meta\Tags\Description;
use Crustum\Meta\Tags\FeedLinks;
use Crustum\Meta\Tags\GenericLinks;
use Crustum\Meta\Tags\MetaTags;
use Crustum\Meta\Tags\OpenGraph;
use Crustum\Meta\Tags\PaginationLinks;
use Crustum\Meta\Tags\PerformanceLinks;
use Crustum\Meta\Tags\Robots;
use Crustum\Meta\Tags\Schemas;
use Crustum\Meta\Tags\TagBuilder;
use Crustum\Meta\Tags\Title;
use Crustum\Meta\Tags\Twitter;
use InvalidArgumentException;

/**
 * Registry of the tag builders rendered for every head.
 */
class TagRegistry
{
    /**
     * Registered custom tag builder classes.
     *
     * @var array<int, class-string<\Crustum\Meta\Tags\TagBuilder>>
     */
    protected array $extensions = [];

    /**
     * The tag builders rendered for every head, in render order.
     *
     * @return array<int, class-string<\Crustum\Meta\Tags\TagBuilder>>
     */
    public function builders(): array
    {
        return [...$this->defaults(), ...$this->extensions];
    }

    /**
     * Register a custom tag builder.
     *
     * @param class-string $builder Tag builder class
     * @return static
     * @throws \InvalidArgumentException When the builder is invalid
     */
    public function extend(string $builder): static
    {
        if (! is_subclass_of($builder, TagBuilder::class)) {
            throw new InvalidArgumentException('Head tag extensions must extend ' . TagBuilder::class . '.');
        }

        if (in_array($builder, $this->defaults(), true)) {
            throw new InvalidArgumentException('Built-in head tag builders are already registered.');
        }

        $duplicates = array_values(array_intersect($builder::routeAttributeKeys(), array_keys($this->routeAttributeKeys())));

        if ($duplicates !== []) {
            throw new InvalidArgumentException(sprintf(
                'Head tag extensions may not register route attributes already registered by another builder: %s.',
                implode(', ', $duplicates),
            ));
        }

        if (! in_array($builder, $this->extensions, true)) {
            $this->extensions[] = $builder;
        }

        return $this;
    }

    /**
     * Map each supported route attribute key to its tag builder.
     *
     * @return array<string, class-string<\Crustum\Meta\Tags\TagBuilder>>
     */
    public function routeAttributeKeys(): array
    {
        $keys = [];

        foreach ($this->builders() as $builder) {
            foreach ($builder::routeAttributeKeys() as $key) {
                $keys[$key] = $builder;
            }
        }

        return $keys;
    }

    /**
     * The default tag builders, in render order.
     *
     * @return array<int, class-string<\Crustum\Meta\Tags\TagBuilder>>
     */
    protected function defaults(): array
    {
        return [
            Title::class,
            Description::class,
            Canonical::class,
            Robots::class,
            OpenGraph::class,
            Twitter::class,
            PaginationLinks::class,
            AlternateLinks::class,
            FeedLinks::class,
            PerformanceLinks::class,
            MetaTags::class,
            GenericLinks::class,
            Schemas::class,
        ];
    }
}
