<?php
declare(strict_types=1);

use Crustum\Meta\HeadBuilder;
use Crustum\Meta\Routing\RouteAttributeParser;

it('does not render a canonical URL by default', function (): void {
    expect($this->head()->toHtml())->not->toContain('rel="canonical"');
});

it('carries inherited canonical options into later canonical URLs', function (): void {
    $head = $this->head();

    $head->defaults(fn(HeadBuilder $head): HeadBuilder => $head->canonical(forceHttps: false, trailingSlash: true));

    $this->route('/about', RouteAttributeParser::metadata(['canonical' => '/about']));

    expect($head->toHtml())->toContain('<link rel="canonical" href="http://localhost/about/">');
});
