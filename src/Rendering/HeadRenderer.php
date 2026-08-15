<?php
declare(strict_types=1);

namespace Crustum\Meta\Rendering;

use Cake\Http\ServerRequest;
use Crustum\Meta\HeadData;
use Crustum\Meta\Schema\SchemaValidator;
use Crustum\Meta\TagRegistry;

/**
 * Renders resolved head data as HTML elements and arrays.
 */
class HeadRenderer
{
    /**
     * Tag renderer.
     *
     * @var \Crustum\Meta\Rendering\TagRenderer
     */
    protected TagRenderer $tags;

    /**
     * Constructor.
     *
     * @param \Crustum\Meta\Schema\SchemaValidator $schemas Schema validator
     * @param \Crustum\Meta\TagRegistry $registry Tag builder registry
     * @param \Crustum\Meta\Rendering\TagRenderer|null $tags Tag renderer
     */
    public function __construct(
        protected SchemaValidator $schemas,
        protected TagRegistry $registry,
        ?TagRenderer $tags = null,
    ) {
        $this->tags = $tags ?? new TagRenderer();
    }

    /**
     * Render the resolved head as an HTML string.
     *
     * @param \Crustum\Meta\HeadData $head Head data
     * @param \Cake\Http\ServerRequest|null $request Current request
     * @return string
     */
    public function render(HeadData $head, ?ServerRequest $request = null): string
    {
        return implode(PHP_EOL, $this->toElements($head, $request));
    }

    /**
     * Render the resolved head as individual HTML element strings.
     *
     * @param \Crustum\Meta\HeadData $head Head data
     * @param \Cake\Http\ServerRequest|null $request Current request
     * @return array<int, string>
     */
    public function toElements(HeadData $head, ?ServerRequest $request = null): array
    {
        return $this->elements($head, $request, $this->tags);
    }

    /**
     * Render the resolved head element strings.
     *
     * @param \Crustum\Meta\HeadData $head Head data
     * @param \Cake\Http\ServerRequest|null $request Current request
     * @param \Crustum\Meta\Rendering\TagRenderer $tags Tag renderer
     * @return array<int, string>
     */
    protected function elements(HeadData $head, ?ServerRequest $request, TagRenderer $tags): array
    {
        $resolved = new ResolvedHead($head, $this->registry, $request, $this->schemas);
        $rendered = [];

        foreach ($resolved->builders() as $builder) {
            foreach ($builder->toTags($resolved, $tags) as $tag) {
                if ($tag !== '') {
                    $rendered[] = $tag;
                }
            }
        }

        return $rendered;
    }

    /**
     * Render the resolved head as the array returned by Head::toArray().
     *
     * @param \Crustum\Meta\HeadData $head Head data
     * @param \Cake\Http\ServerRequest|null $request Current request
     * @return array<string, mixed>
     */
    public function toArray(HeadData $head, ?ServerRequest $request = null): array
    {
        $resolved = new ResolvedHead($head, $this->registry, $request, $this->schemas);
        $headArray = [];

        foreach ($this->registry->builders() as $builder) {
            $headArray = $this->setKey($headArray, $builder::headArrayKey(), $resolved->headArray($builder));
        }

        return $headArray;
    }

    /**
     * Set a dot-notated key on an array, returning the modified array.
     *
     * @param array<mixed> $array Array
     * @param string $key Dot-notated key
     * @param mixed $value Value
     * @return array<mixed>
     */
    protected function setKey(array $array, string $key, mixed $value): array
    {
        $segments = explode('.', $key);
        $reference = &$array;

        foreach ($segments as $segment) {
            $reference = &$reference[$segment];
        }

        $reference = $value;

        return $array;
    }
}
