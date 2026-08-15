<?php
declare(strict_types=1);

namespace Crustum\Meta\Tags;

use Cake\Datasource\Paging\PaginatedInterface;
use Cake\Routing\Router;
use Cake\View\Helper\PaginatorHelper;
use Crustum\Meta\Rendering\ResolvedHead;
use Crustum\Meta\Rendering\TagRenderer;
use Override;

/**
 * Pagination prev / next link tag builder.
 *
 * @phpstan-consistent-constructor
 */
class PaginationLinks extends GroupedTagBuilder
{
    /**
     * Constructor.
     *
     * @param array<string, string> $links Pagination links keyed by relation
     */
    public function __construct(protected array $links = [])
    {
    }

    /**
     * Deferred paginated result set used to build links at render time.
     *
     * @var \Cake\Datasource\Paging\PaginatedInterface<array-key, mixed>|null
     */
    protected ?PaginatedInterface $paginated = null;

    /**
     * Query params used by the request-query fallback.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $query = null;

    /**
     * The unique name identifying this builder within the head.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'pagination';
    }

    /**
     * The dot-notated key this builder occupies in the Head::toArray() result.
     *
     * @return string
     */
    #[Override]
    public static function headArrayKey(): string
    {
        return 'links.pagination';
    }

    /**
     * Create a builder from a CakePHP paginator helper.
     *
     * @param \Cake\View\Helper\PaginatorHelper $paginator Paginator helper
     * @return self
     */
    public static function fromPaginator(PaginatorHelper $paginator): self
    {
        $links = new self();

        if ($paginator->hasPrev()) {
            $links->link('prev', $paginator->generateUrl(['page' => max($paginator->current() - 1, 1)]));
        }

        if ($paginator->hasNext()) {
            $links->link('next', $paginator->generateUrl(['page' => $paginator->current() + 1]));
        }

        return $links;
    }

    /**
     * Create a builder from a paginated result set.
     *
     * URLs are built from the current request's query params, keeping sort,
     * direction and limit while pointing at the target page. Page 1 drops the
     * `page` query param, mirroring PaginatorHelper::generateUrl().
     *
     * @param \Cake\Datasource\Paging\PaginatedInterface<array-key, mixed> $paginated Paginated result set
     * @param array<string, mixed>|null $query Query params to use instead of the current request
     * @return self
     */
    public static function fromPaginated(PaginatedInterface $paginated, ?array $query = null): self
    {
        $links = new self();
        $links->paginated = $paginated;
        $links->query = $query;

        return $links;
    }

    /**
     * Determine whether links are waiting for render-time resolution.
     *
     * @return bool
     */
    public function isDeferred(): bool
    {
        return $this->paginated instanceof PaginatedInterface;
    }

    /**
     * Get the deferred paginated result set.
     *
     * @return \Cake\Datasource\Paging\PaginatedInterface<array-key, mixed>|null
     */
    public function paginated(): ?PaginatedInterface
    {
        return $this->paginated;
    }

    /**
     * Resolve deferred links using a Cake paginator helper or request fallback.
     *
     * @param \Cake\View\Helper\PaginatorHelper|null $paginator Paginator helper
     * @return self
     */
    public function resolve(?PaginatorHelper $paginator = null): self
    {
        if (!$this->paginated instanceof PaginatedInterface) {
            return $this;
        }

        if ($paginator instanceof PaginatorHelper) {
            $paginator->setPaginated($this->paginated);

            return self::fromPaginator($paginator);
        }

        $links = new self();

        if ($this->paginated->hasPrevPage()) {
            $links->link('prev', self::pageUrl($this->query, max($this->paginated->currentPage() - 1, 1)));
        }

        if ($this->paginated->hasNextPage()) {
            $links->link('next', self::pageUrl($this->query, $this->paginated->currentPage() + 1));
        }

        return $links;
    }

    /**
     * Build a relative URL for a target page, preserving the current query.
     *
     * @param array<string, mixed>|null $query Query params, defaulting to the request's
     * @param int $page Target page number
     * @return string
     */
    protected static function pageUrl(?array $query, int $page): string
    {
        $query ??= Router::getRequest()?->getQueryParams() ?? [];
        $query['page'] = $page;

        if ($page === 1) {
            unset($query['page']);
        }

        return Router::url(['?' => $query]);
    }

    /**
     * Add a pagination link.
     *
     * @param string $rel Link relation
     * @param string $href Link href
     * @return static
     */
    public function link(string $rel, string $href): static
    {
        $this->links[$rel] = $href;

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

        $merged = new static(array_replace($base->links, $this->links));
        $merged->paginated = $this->paginated ?? $base->paginated;
        $merged->query = $this->query ?? $base->query;

        return $merged;
    }

    /**
     * Determine if the builder holds no data.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->links === [] && !$this->isDeferred();
    }

    /**
     * Convert this builder into its Head::toArray() value.
     *
     * @param \Crustum\Meta\Rendering\ResolvedHead $head Resolved head
     * @return array<string, string>
     */
    public function toHeadArray(ResolvedHead $head): array
    {
        return $this->links;
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
            fn(string $rel, string $href): string => $tags->link($rel, $href),
            array_keys($this->links),
            $this->links,
        );
    }
}
