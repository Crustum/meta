<?php
declare(strict_types=1);

namespace Crustum\Meta\View\Helper;

use Cake\View\Helper;
use Cake\View\Helper\PaginatorHelper;
use Crustum\Meta\ContainerRegistry;
use Crustum\Meta\HeadData;
use Crustum\Meta\HeadManager;
use Crustum\Meta\Rendering\HeadRenderer;
use Crustum\Meta\Schema\SchemaFactory;
use Crustum\Meta\Schema\SchemaValidator;
use Crustum\Meta\TagRegistry;
use Crustum\Meta\Trait\BuildsHeadTrait;

/**
 * View helper exposing the Head manager to templates.
 * Fluent head-building methods delegate to the shared Head manager. When
 * controller pagination was configured, render methods pass the view's
 * Paginator helper to the manager for URL generation.
 *
 * @extends \Cake\View\Helper<\Cake\View\View>
 */
class HeadHelper extends Helper
{
    use BuildsHeadTrait;

    /**
     * Resolved Head manager, created lazily.
     *
     * @var \Crustum\Meta\HeadManager|null
     */
    protected ?HeadManager $manager = null;

    /**
     * Render the resolved document head as an HTML string.
     *
     * @param int|null $status Response status code
     * @return string
     */
    public function render(?int $status = null): string
    {
        return $this->manager()->render($status, $this->paginator());
    }

    /**
     * Render the resolved document head for a view.
     *
     * @param int|null $status Response status code
     * @return string
     */
    public function renderForView(?int $status = null): string
    {
        return $this->render($status);
    }

    /**
     * Get the resolved head as an array.
     *
     * @param int|null $status Response status code
     * @return array<string, mixed>
     */
    public function toArray(?int $status = null): array
    {
        return $this->manager()->toArray($status, $this->paginator());
    }

    /**
     * Get the resolved head as individual HTML element strings.
     *
     * @param int|null $status Response status code
     * @return array<int, string>
     */
    public function toElements(?int $status = null): array
    {
        return $this->manager()->toElements($status, $this->paginator());
    }

    /**
     * Get the view's paginator helper when it is loaded.
     *
     * @return \Cake\View\Helper\PaginatorHelper|null
     */
    protected function paginator(): ?PaginatorHelper
    {
        $helpers = $this->getView()->helpers();

        if (!$helpers->has('Paginator')) {
            return null;
        }

        $paginator = $helpers->get('Paginator');

        return $paginator instanceof PaginatorHelper ? $paginator : null;
    }

    /**
     * Get the shared Head manager, creating one when the container is not wired.
     *
     * @return \Crustum\Meta\HeadManager
     * @throws \RuntimeException When the manager cannot be resolved
     */
    protected function manager(): HeadManager
    {
        if ($this->manager instanceof HeadManager) {
            return $this->manager;
        }

        $manager = ContainerRegistry::getInstance()?->get(HeadManager::class);

        if (!$manager instanceof HeadManager) {
            $manager = new HeadManager(
                new HeadRenderer(new SchemaValidator(), new TagRegistry()),
                new TagRegistry(),
            );
        }

        return $this->manager = $manager;
    }

    /**
     * Get the head data the fluent methods write to.
     *
     * @return \Crustum\Meta\HeadData
     */
    protected function headData(): HeadData
    {
        return $this->manager()->headData();
    }

    /**
     * Get the schema factory used to resolve schema callbacks.
     *
     * @return \Crustum\Meta\Schema\SchemaFactory
     */
    protected function schemaFactory(): SchemaFactory
    {
        return $this->manager()->schemaFactory();
    }
}
