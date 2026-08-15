<?php
declare(strict_types=1);

namespace Crustum\Meta\Test\TestCase;

use ArrayIterator;
use Cake\Core\Configure;
use Cake\Datasource\Paging\PaginatedResultSet;
use Cake\Http\ServerRequest;
use Cake\Routing\Route\Route;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\View\Helper\PaginatorHelper;
use Cake\View\View;
use Crustum\Meta\ContainerRegistry;
use Crustum\Meta\HeadManager;
use Crustum\Meta\Rendering\HeadRenderer;
use Crustum\Meta\Schema\SchemaFactory;
use Crustum\Meta\Schema\SchemaValidator;
use Crustum\Meta\TagRegistry;

/**
 * Base test case for all Crustum/Meta plugin tests.
 */
abstract class MetaTestCase extends TestCase
{
    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Router::resetRoutes();
        ContainerRegistry::clear();
        Configure::write('App.fullBaseUrl', 'http://localhost');
        Configure::write('debug', true);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        ContainerRegistry::clear();
        Router::resetRoutes();

        parent::tearDown();
    }

    /**
     * Create a fresh head manager with clean state.
     *
     * @param \Crustum\Meta\Schema\SchemaFactory|null $schemaFactory Schema factory
     * @return \Crustum\Meta\HeadManager
     */
    protected function head(?SchemaFactory $schemaFactory = null): HeadManager
    {
        $registry = new TagRegistry();

        return new HeadManager(
            new HeadRenderer(new SchemaValidator(), $registry),
            $registry,
            $schemaFactory,
        );
    }

    /**
     * Register a route carrying head metadata and bind the matched route to the
     * current request scope so the head manager can resolve it.
     *
     * @param string $path Request path
     * @param array<string, mixed> $options Route options, including the head metadata
     * @param array<string, mixed> $defaults Route defaults
     * @return \Cake\Routing\Route\Route
     */
    protected function route(string $path, array $options = [], array $defaults = []): Route
    {
        $defaults += ['controller' => 'TestController', 'action' => 'index'];

        Router::createRouteBuilder('/')->connect($path, $defaults, $options);

        $request = new ServerRequest([
            'environment' => ['REQUEST_METHOD' => 'GET', 'HTTP_HOST' => 'localhost'],
            'url' => $path,
        ]);

        $params = Router::parseRequest($request);
        $route = $params['_route'];

        $request = $request->withAttribute('route', $route);
        Router::setRequest($request);

        return $route;
    }

    /**
     * Create a request bound to a registered route.
     *
     * @param string $url Request path
     * @param array<string, string> $defaults Route defaults
     * @param array<string, string> $query Query params for the request
     * @return \Cake\Http\ServerRequest
     */
    protected function boundRequest(
        string $url = '/posts',
        array $defaults = ['controller' => 'Posts', 'action' => 'index'],
        array $query = [],
    ): ServerRequest {
        Router::createRouteBuilder('/')->connect($url, $defaults);

        $request = new ServerRequest([
            'environment' => ['REQUEST_METHOD' => 'GET', 'HTTP_HOST' => 'localhost'],
            'url' => $url,
            'query' => $query,
        ]);

        $params = Router::parseRequest($request);

        return $request
            ->withAttribute('params', $params)
            ->withAttribute('route', $params['_route']);
    }

    /**
     * Create a CakePHP paginator helper bound to a page of results.
     *
     * @param int $currentPage Current page number
     * @param bool $hasPrev Whether a previous page exists
     * @param bool $hasNext Whether a next page exists
     * @param string $url Request path used for generated URLs
     * @return \Cake\View\Helper\PaginatorHelper
     */
    protected function paginator(
        int $currentPage = 2,
        bool $hasPrev = true,
        bool $hasNext = true,
        string $url = '/posts',
    ): PaginatorHelper {
        $request = $this->boundRequest($url);
        Router::setRequest($request);

        $view = new View($request, null, null, ['helpers' => ['Paginator']]);

        $view->Paginator->setPaginated($this->paginated($currentPage, $hasPrev, $hasNext));

        return $view->Paginator;
    }

    /**
     * Create a paginated result set bound to the current request.
     *
     * @param int $currentPage Current page number
     * @param bool $hasPrev Whether a previous page exists
     * @param bool $hasNext Whether a next page exists
     * @return \Cake\Datasource\Paging\PaginatedResultSet
     */
    protected function paginated(
        int $currentPage = 2,
        bool $hasPrev = true,
        bool $hasNext = true,
    ): PaginatedResultSet {
        return new PaginatedResultSet(new ArrayIterator([]), [
            'count' => 30,
            'currentPage' => $currentPage,
            'perPage' => 10,
            'hasPrevPage' => $hasPrev,
            'hasNextPage' => $hasNext,
        ]);
    }
}
