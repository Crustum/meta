<?php
declare(strict_types=1);

use Crustum\Meta\ErrorPages;
use Crustum\Meta\HeadBuilder;
use Crustum\Meta\Routing\RouteAttributeParser;

it('lets error head beat the resolved page head', function (): void {
    $head = $this->head();

    $head->defaults(fn(HeadBuilder $head): HeadBuilder => $head->title('Acme', suffix: ' - Acme'));

    $head->errors(function (ErrorPages $errors): void {
        $errors->defaults(robots: 'noindex, follow');

        $errors->status(
            404,
            title: 'Page Not Found',
            description: 'The page could not be found.',
        );
    });

    $this->route('/missing', RouteAttributeParser::metadata([
        'title' => 'Original Page',
        'description' => 'Original description.',
    ]));

    expect($head->toHtml(404))
        ->toContain('<title>Page Not Found - Acme</title>')
        ->toContain('<meta name="description" content="The page could not be found.">')
        ->toContain('<meta name="robots" content="noindex, follow">');
});

it('accepts head builder callbacks for error metadata', function (): void {
    $head = $this->head();

    $head->defaults(fn(HeadBuilder $head): HeadBuilder => $head->title('Acme', suffix: ' - Acme'));

    $head->errors(function (ErrorPages $errors): void {
        $errors->defaults(fn(HeadBuilder $head): HeadBuilder => $head->hiddenFromRobots());

        $errors->status(404, fn(HeadBuilder $head): HeadBuilder => $head
            ->title('Page Not Found')
            ->description('The page could not be found.'));
    });

    $this->route('/missing', RouteAttributeParser::metadata([
        'title' => 'Original Page',
    ]));

    expect($head->toHtml(404))
        ->toContain('<title>Page Not Found - Acme</title>')
        ->toContain('<meta name="description" content="The page could not be found.">')
        ->toContain('<meta name="robots" content="none">');
});
