<?php
declare(strict_types=1);

namespace Crustum\Meta;

use Closure;
use Crustum\Meta\Routing\RouteAttributeParser;

/**
 * Head data definition for error pages, keyed by response status.
 */
class ErrorPages
{
    /**
     * Default error page head data.
     *
     * @var \Crustum\Meta\HeadData
     */
    protected HeadData $defaults;

    /**
     * Status-specific error page head data.
     *
     * @var array<int, \Crustum\Meta\HeadData>
     */
    protected array $statuses = [];

    /**
     * Constructor.
     *
     * @param \Crustum\Meta\TagRegistry $registry Tag builder registry
     * @param \Closure(callable(\Crustum\Meta\HeadBuilder): mixed): \Crustum\Meta\HeadData $define Head definition callback
     */
    public function __construct(
        protected TagRegistry $registry,
        protected Closure $define,
    ) {
        $this->defaults = new HeadData();
    }

    /**
     * Define head metadata for every error page, using either named
     * route-attribute values or a single head builder callback.
     *
     * @param mixed ...$head Head attributes or a builder callback
     * @return static
     */
    public function defaults(mixed ...$head): static
    {
        $this->defaults = $this->apply($this->defaults, $head);

        return $this;
    }

    /**
     * Define head metadata for a specific error status, using either named
     * route-attribute values or a single head builder callback.
     *
     * @param int $status Response status code
     * @param mixed ...$head Head attributes or a builder callback
     * @return static
     */
    public function status(int $status, mixed ...$head): static
    {
        $this->statuses[$status] = $this->apply(new HeadData(), $head);

        return $this;
    }

    /**
     * Get the merged error head data for a status, or null when none is defined.
     *
     * @param int $status Response status code
     * @return \Crustum\Meta\HeadData|null
     */
    public function forStatus(int $status): ?HeadData
    {
        if (! isset($this->statuses[$status]) && $this->defaults->isEmpty()) {
            return null;
        }

        return $this->defaults->merge($this->statuses[$status] ?? new HeadData());
    }

    /**
     * Apply head attributes onto a base head data instance.
     *
     * @param \Crustum\Meta\HeadData $base Base head data
     * @param array<int|string, mixed> $head Head attributes or a builder callback
     * @return \Crustum\Meta\HeadData
     */
    protected function apply(HeadData $base, array $head): HeadData
    {
        if (array_keys($head) === [0] && $head[0] instanceof Closure) {
            return $base->merge(($this->define)($head[0]));
        }

        return RouteAttributeParser::apply($base, RouteAttributeParser::arguments($head), $this->registry);
    }
}
