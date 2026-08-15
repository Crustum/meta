<?php
declare(strict_types=1);

use Cake\Core\Container;
use Cake\Routing\Router;
use Cake\View\Helper;
use Cake\View\View;
use Crustum\Meta\ContainerRegistry;
use Crustum\Meta\HeadManager;
use Crustum\Meta\View\Helper\HeadHelper;

it('renders the resolved head through the view helper', function (): void {
    $manager = $this->head();
    $manager->title('About')->description('About Acme.');

    $container = new Container();
    $container->addShared(HeadManager::class, fn(): HeadManager => $manager);
    ContainerRegistry::setInstance($container);

    $view = new View(null, null, null, ['helpers' => ['Crustum/Meta.Head']]);

    expect($view->Head->render())
        ->toContain('<title>About</title>')
        ->toContain('<meta name="description" content="About Acme.">');
});

it('renders the head for a view', function (): void {
    $manager = $this->head();
    $manager->title('About');

    $container = new Container();
    $container->addShared(HeadManager::class, fn(): HeadManager => $manager);
    ContainerRegistry::setInstance($container);

    $view = new View(null, null, null, ['helpers' => ['Crustum/Meta.Head']]);

    expect($view->Head->renderForView())->toContain('<title>About</title>');
});

it('exposes head arrays and elements through the view helper', function (): void {
    $manager = $this->head();
    $manager->title('About');

    $container = new Container();
    $container->addShared(HeadManager::class, fn(): HeadManager => $manager);
    ContainerRegistry::setInstance($container);

    $view = new View(null, null, null, ['helpers' => ['Crustum/Meta.Head']]);

    expect($view->Head->toArray()['title'])->toBe('About');
    expect($view->Head->toElements())->toBe(['<title>About</title>']);
});

it('creates a head manager when the container is not wired', function (): void {
    $view = new View(null, null, null, ['helpers' => ['Crustum/Meta.Head']]);

    expect($view->Head->render())->toBe('');
});

it('renders pagination links passed explicitly through the view helper', function (): void {
    $request = $this->boundRequest();
    Router::setRequest($request);

    $view = new View($request, null, null, ['helpers' => ['Crustum/Meta.Head', 'Paginator']]);
    $view->Paginator->setPaginated($this->paginated());

    $html = $view->Head->paginate($view->Paginator)->render();

    expect($html)
        ->toContain('rel="prev" href="/posts">')
        ->toContain('rel="next" href="/posts?page=3">');
});

it('resolves controller pagination through the view paginator at render time', function (): void {
    $request = $this->boundRequest();
    Router::setRequest($request);

    $manager = $this->head();
    $manager->paginate($this->paginated());

    $container = new Container();
    $container->addShared(HeadManager::class, fn(): HeadManager => $manager);
    ContainerRegistry::setInstance($container);

    $view = new View($request, null, null, ['helpers' => ['Crustum/Meta.Head', 'Paginator']]);

    expect($view->Head->render())
        ->toContain('rel="prev" href="/posts">')
        ->toContain('rel="next" href="/posts?page=3">');
});

it('supports the fluent api on the view helper', function (): void {
    $request = $this->boundRequest();
    Router::setRequest($request);

    $view = new View($request, null, null, ['helpers' => ['Crustum/Meta.Head', 'Paginator']]);
    $view->Paginator->setPaginated($this->paginated());

    $html = $view->Head
        ->title('About')
        ->paginate($view->Paginator)
        ->render();

    expect($html)
        ->toContain('<title>About</title>')
        ->toContain('rel="prev" href="/posts">')
        ->toContain('rel="next" href="/posts?page=3">');
});

it('does not render pagination links without an explicit paginate call', function (): void {
    $request = $this->boundRequest();
    Router::setRequest($request);

    $view = new View($request, null, null, ['helpers' => ['Crustum/Meta.Head', 'Paginator']]);
    $view->Paginator->setPaginated($this->paginated());

    expect($view->Head->render())
        ->not->toContain('rel="prev"')
        ->not->toContain('rel="next"');
});

it('is a cake view helper', function (): void {
    expect(is_a(HeadHelper::class, Helper::class, true))->toBeTrue();
});
