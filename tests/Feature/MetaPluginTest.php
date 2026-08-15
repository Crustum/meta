<?php
declare(strict_types=1);

use Cake\Core\Container;
use Crustum\Meta\ContainerRegistry;
use Crustum\Meta\CurrentHead;
use Crustum\Meta\HeadData;
use Crustum\Meta\HeadManager;
use Crustum\Meta\MetaPlugin;
use Crustum\Meta\Rendering\HeadRenderer;
use Crustum\Meta\Schema\SchemaFactory;
use Crustum\Meta\Schema\SchemaValidator;
use Crustum\Meta\TagRegistry;
use TestApp\Application;

it('registers shared container services', function (): void {
    $container = new Container();
    $plugin = new MetaPlugin();

    $plugin->services($container);

    expect($container->has(HeadManager::class))->toBeTrue();
    expect($container->has(HeadRenderer::class))->toBeTrue();
    expect($container->has(TagRegistry::class))->toBeTrue();
    expect($container->has(SchemaFactory::class))->toBeTrue();
    expect($container->has(SchemaValidator::class))->toBeTrue();
    expect($container->has(HeadData::class))->toBeTrue();
    expect($container->has(CurrentHead::class))->toBeTrue();
});

it('resolves the same shared head manager from the container', function (): void {
    $container = new Container();
    $plugin = new MetaPlugin();

    $plugin->services($container);

    $first = $container->get(HeadManager::class);
    $second = $container->get(HeadManager::class);

    expect($first)->toBeInstanceOf(HeadManager::class);
    expect($second)->toBe($first);
});

it('sets the container registry from the application container during bootstrap', function (): void {
    $app = new Application(CONFIG);
    $plugin = new MetaPlugin();

    $plugin->bootstrap($app);

    expect(ContainerRegistry::getInstance())->toBe($app->getContainer());
});
