<?php
declare(strict_types=1);

namespace Crustum\Meta\Rendering;

use Cake\Http\ServerRequest;
use Crustum\Meta\HeadData;
use Crustum\Meta\Schema\SchemaValidator;
use Crustum\Meta\TagRegistry;
use Crustum\Meta\Tags\Canonical;
use Crustum\Meta\Tags\Description;
use Crustum\Meta\Tags\OpenGraph;
use Crustum\Meta\Tags\Robots;
use Crustum\Meta\Tags\TagBuilder;
use Crustum\Meta\Tags\Title;

/**
 * The resolved head data with the rendering context needed to produce tags.
 */
class ResolvedHead
{
    /**
     * Constructor.
     *
     * @param \Crustum\Meta\HeadData $data Head data
     * @param \Crustum\Meta\TagRegistry $registry Tag builder registry
     * @param \Cake\Http\ServerRequest|null $request Current request
     * @param \Crustum\Meta\Schema\SchemaValidator|null $schemas Schema validator
     */
    public function __construct(
        protected HeadData $data,
        protected TagRegistry $registry,
        protected ?ServerRequest $request = null,
        protected ?SchemaValidator $schemas = null,
    ) {
    }

    /**
     * Get the resolved head data.
     *
     * @return \Crustum\Meta\HeadData
     */
    public function data(): HeadData
    {
        return $this->data;
    }

    /**
     * Get the current request.
     *
     * @return \Cake\Http\ServerRequest|null
     */
    public function request(): ?ServerRequest
    {
        return $this->request;
    }

    /**
     * Validate a schema array.
     *
     * @param array<string, mixed> $schema Schema data
     * @return void
     */
    public function validateSchema(array $schema): void
    {
        $this->schemas?->validate($schema);
    }

    /**
     * Get the resolved page title.
     *
     * @return string|null
     */
    public function title(): ?string
    {
        $title = $this->builder(Title::class);

        return $title instanceof Title ? $title->render() : null;
    }

    /**
     * Get the resolved page description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        $description = $this->builder(Description::class);

        return $description instanceof Description ? $description->render() : null;
    }

    /**
     * Get the resolved canonical URL.
     *
     * @return string|null
     */
    public function canonical(): ?string
    {
        $canonical = $this->builder(Canonical::class);

        return $canonical instanceof Canonical ? $canonical->render($this->request) : null;
    }

    /**
     * Get the resolved robots directive.
     *
     * @return string|null
     */
    public function robots(): ?string
    {
        $robots = $this->builder(Robots::class);

        return $robots instanceof Robots ? $robots->render() : null;
    }

    /**
     * Get the first Open Graph image, when any.
     *
     * @return array<string, string>|null
     */
    public function openGraphImage(): ?array
    {
        $openGraph = $this->builder(OpenGraph::class);

        if (!$openGraph instanceof OpenGraph) {
            return null;
        }

        $images = $openGraph->images();

        if ($images === []) {
            return null;
        }

        $image = reset($images);

        return array_filter([
            'url' => $image['url'],
            'alt' => $image['alt'] ?? null,
        ], fn(mixed $value): bool => ! is_null($value));
    }

    /**
     * The tag builders to render, in declared order, including those that derive
     * their values from the rest of the head even when never set explicitly.
     *
     * @return array<int, \Crustum\Meta\Tags\TagBuilder>
     */
    public function builders(): array
    {
        $builders = [];

        foreach ($this->registry->builders() as $builder) {
            $value = $this->builder($builder);

            if ($value instanceof TagBuilder) {
                $builders[] = $value;
            } elseif ($builder::rendersWhenEmpty($this)) {
                $builders[] = new $builder();
            }
        }

        return $builders;
    }

    /**
     * Resolve one tag builder into its Head::toArray() value,
     * including defaults for builder data that was never set.
     *
     * @param class-string<\Crustum\Meta\Tags\TagBuilder> $builder Tag builder class
     * @return mixed
     */
    public function headArray(string $builder): mixed
    {
        $value = $this->builder($builder);

        if (!$value instanceof TagBuilder && $builder::rendersWhenEmpty($this)) {
            $value = new $builder();
        }

        return $value?->toHeadArray($this) ?? $builder::headArrayDefault();
    }

    /**
     * Get a stored tag builder by class name.
     *
     * @param class-string<\Crustum\Meta\Tags\TagBuilder> $builder Tag builder class
     * @return \Crustum\Meta\Tags\TagBuilder|null
     */
    public function builder(string $builder): ?TagBuilder
    {
        return $this->data->get($builder);
    }
}
