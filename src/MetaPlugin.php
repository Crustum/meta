<?php
declare(strict_types=1);

namespace Crustum\Meta;

use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\ContainerApplicationInterface;
use Cake\Core\ContainerInterface;
use Cake\Core\PluginApplicationInterface;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\RouteBuilder;
use Crustum\Meta\Rendering\HeadRenderer;
use Crustum\Meta\Schema\SchemaFactory;
use Crustum\Meta\Schema\SchemaValidator;
use Crustum\PluginManifest\Manifest\ManifestInterface;
use Crustum\PluginManifest\Manifest\ManifestTrait;
use Override;

/**
 * Plugin for Crustum/Meta
 *
 * @uses \Crustum\PluginManifest\Manifest\ManifestTrait
 */
class MetaPlugin extends BasePlugin implements ManifestInterface
{
    use ManifestTrait;

    /**
     * @inheritDoc
     */
    #[Override]
    public function bootstrap(PluginApplicationInterface $app): void
    {
        parent::bootstrap($app);

        if ($app instanceof ContainerApplicationInterface) {
            ContainerRegistry::setInstance($app->getContainer());
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function routes(RouteBuilder $routes): void
    {
        parent::routes($routes);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        return $middlewareQueue;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function console(CommandCollection $commands): CommandCollection
    {
        return parent::console($commands);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function services(ContainerInterface $container): void
    {
        $container->addShared(HeadData::class, fn(): HeadData => new HeadData());
        $container->addShared(CurrentHead::class, fn(): CurrentHead => new CurrentHead());
        $container->addShared(SchemaFactory::class, fn(): SchemaFactory => new SchemaFactory());
        $container->addShared(SchemaValidator::class, fn(): SchemaValidator => new SchemaValidator());
        $container->addShared(TagRegistry::class, fn(): TagRegistry => new TagRegistry());
        $container->addShared(HeadRenderer::class, fn(): HeadRenderer => new HeadRenderer(
            $container->get(SchemaValidator::class),
            $container->get(TagRegistry::class),
        ));
        $container->addShared(HeadManager::class, fn(): HeadManager => new HeadManager(
            $container->get(HeadRenderer::class),
            $container->get(TagRegistry::class),
            $container->get(SchemaFactory::class),
            $container->get(CurrentHead::class),
        ));
    }

    /**
     * Plugin install assets via crustum/plugin-manifest.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function manifest(): array
    {
        return static::manifestStarRepo('Crustum/Meta');
    }
}
