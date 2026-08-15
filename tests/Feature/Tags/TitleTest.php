<?php
declare(strict_types=1);

use Crustum\Meta\HeadBuilder;

it('renders the default title without applying its inherited suffix', function (): void {
    $head = $this->head();

    $head->defaults(fn(HeadBuilder $head): HeadBuilder => $head->title('Acme', suffix: ' - Acme'));

    expect($head->toHtml())->toContain('<title>Acme</title>');
});

it('can render an exact title without the configured suffix', function (): void {
    $head = $this->head();

    $head->defaults(fn(HeadBuilder $head): HeadBuilder => $head->title('Acme', suffix: ' - Acme'));

    $head->title('Checkout', exact: true);

    expect($head->toHtml())->toContain('<title>Checkout</title>');
});
