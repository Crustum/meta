<?php
declare(strict_types=1);

namespace Crustum\Meta;

use Cake\Http\ServerRequest;
use Cake\Routing\Route\Route;
use Cake\Routing\Router;
use Cake\View\Helper\PaginatorHelper;
use Crustum\Meta\Rendering\HeadRenderer;
use Crustum\Meta\Routing\RouteAttributeParser;
use Crustum\Meta\Schema\SchemaFactory;
use Crustum\Meta\Tags\PaginationLinks;
use Crustum\Meta\Trait\BuildsHeadTrait;
use Crustum\Meta\Trait\ConditionableTrait;

/**
 * Manager service that resolves, layers, and renders the document head.
 */
class HeadManager
{
    use BuildsHeadTrait;
    use ConditionableTrait;

    /**
     * Default head data merged beneath every page's head.
     *
     * @var \Crustum\Meta\HeadData
     */
    protected HeadData $defaults;

    /**
     * Error page head data definitions.
     *
     * @var \Crustum\Meta\ErrorPages
     */
    protected ErrorPages $errorPages;

    /**
     * Request-scoped head data.
     *
     * @var \Crustum\Meta\CurrentHead
     */
    protected CurrentHead $state;

    /**
     * Constructor.
     *
     * @param \Crustum\Meta\Rendering\HeadRenderer $renderer Head renderer
     * @param \Crustum\Meta\TagRegistry $registry Tag builder registry
     * @param \Crustum\Meta\Schema\SchemaFactory|null $schemaFactory Schema factory
     * @param \Crustum\Meta\CurrentHead|null $state Request-scoped head data
     */
    public function __construct(
        protected HeadRenderer $renderer,
        protected TagRegistry $registry,
        protected ?SchemaFactory $schemaFactory = null,
        ?CurrentHead $state = null,
    ) {
        $this->schemaFactory ??= new SchemaFactory();
        $this->state = $state ?? new CurrentHead();
        $this->defaults = new HeadData();
        $this->errorPages = new ErrorPages(
            $this->registry,
            fn(callable $callback): HeadData => $this->define($callback),
        );
    }

    /**
     * Define default head data merged beneath every page's head.
     *
     * @param callable(\Crustum\Meta\HeadBuilder): mixed $callback Head definition callback
     * @return static
     */
    public function defaults(callable $callback): static
    {
        $this->defaults = $this->defaults->merge($this->define($callback)->asDefaults());

        return $this;
    }

    /**
     * Define head data for error pages.
     *
     * @param callable(\Crustum\Meta\ErrorPages): mixed $callback Error page definition callback
     * @return static
     */
    public function errors(callable $callback): static
    {
        $callback($this->errorPages);

        return $this;
    }

    /**
     * Register a custom tag builder to render on every head.
     *
     * @param class-string<\Crustum\Meta\Tags\TagBuilder> $builder Tag builder class
     * @return static
     */
    public function extend(string $builder): static
    {
        $this->registry->extend($builder);

        return $this;
    }

    /**
     * Set the response status used to resolve error page head data.
     *
     * @param int $status Response status code
     * @return static
     */
    public function status(int $status): static
    {
        $this->state()->setStatus($status);

        return $this;
    }

    /**
     * Get the resolved head as an array.
     *
     * @param int|null $status Response status code
     * @return array<string, mixed>
     */
    public function toArray(?int $status = null, ?PaginatorHelper $paginator = null): array
    {
        return $this->renderer->toArray($this->resolve($status, $paginator), $this->request());
    }

    /**
     * Get the resolved head as individual HTML element strings.
     *
     * @param int|null $status Response status code
     * @return array<int, string>
     */
    public function toElements(?int $status = null, ?PaginatorHelper $paginator = null): array
    {
        return $this->renderer->toElements($this->resolve($status, $paginator), $this->request());
    }

    /**
     * Render the resolved head as an HTML string.
     *
     * @param int|null $status Response status code
     * @return string
     */
    public function render(?int $status = null, ?PaginatorHelper $paginator = null): string
    {
        return $this->renderer->render($this->resolve($status, $paginator), $this->request());
    }

    /**
     * Render the resolved head as an HTML string.
     *
     * @param int|null $status Response status code
     * @return string
     */
    public function toHtml(?int $status = null, ?PaginatorHelper $paginator = null): string
    {
        return $this->render($status, $paginator);
    }

    /**
     * Flush runtime data for the current request scope.
     *
     * @return static
     */
    public function flush(): static
    {
        $this->state()->flush();

        return $this;
    }

    /**
     * Run a definition callback against a fresh head builder and return its data.
     *
     * @param callable(\Crustum\Meta\HeadBuilder): mixed $callback Head definition callback
     * @return \Crustum\Meta\HeadData
     */
    protected function define(callable $callback): HeadData
    {
        $data = new HeadData();

        $callback(new HeadBuilder($data, $this->schemaFactory()));

        return $data;
    }

    /**
     * Resolve all configured head layers into the final data for rendering.
     *
     * @param int|null $status Response status code
     * @return \Crustum\Meta\HeadData
     */
    protected function resolve(?int $status = null, ?PaginatorHelper $paginator = null): HeadData
    {
        $data = (new HeadData())->merge($this->defaults);

        $route = $this->route();

        if ($route instanceof Route) {
            foreach (RouteAttributeParser::routeMetadata($route) as $attributes) {
                $data = RouteAttributeParser::apply($data, $attributes, $this->registry);
            }
        }

        $data = $data->merge($this->state()->data());

        $pagination = $data->get(PaginationLinks::class);

        if ($pagination instanceof PaginationLinks && $pagination->isDeferred()) {
            $data->overlayBuilder($pagination->resolve($paginator));
        }

        $errorStatus = $status ?? $this->state()->status();

        if (! is_null($errorStatus)) {
            $error = $this->errorPages->forStatus($errorStatus);

            if ($error instanceof HeadData) {
                $data = $data->merge($error);
            }
        }

        return $data;
    }

    /**
     * Get the request-scoped head data the fluent methods write to.
     *
     * @return \Crustum\Meta\HeadData
     */
    public function headData(): HeadData
    {
        return $this->state()->data();
    }

    /**
     * Get the schema factory used to resolve schema callbacks.
     *
     * @return \Crustum\Meta\Schema\SchemaFactory
     */
    public function schemaFactory(): SchemaFactory
    {
        return $this->schemaFactory ??= new SchemaFactory();
    }

    /**
     * Get the request-scoped head data holder.
     *
     * @return \Crustum\Meta\CurrentHead
     */
    protected function state(): CurrentHead
    {
        return $this->state;
    }

    /**
     * Get the current request, or null when none is available.
     *
     * @return \Cake\Http\ServerRequest|null
     */
    protected function request(): ?ServerRequest
    {
        return Router::getRequest();
    }

    /**
     * Get the current matched route, or null when none is available.
     *
     * @return \Cake\Routing\Route\Route|null
     */
    protected function route(): ?Route
    {
        $route = $this->request()?->getAttribute('route');

        return $route instanceof Route ? $route : null;
    }
}
