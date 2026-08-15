<?php
declare(strict_types=1);

use Crustum\Meta\HeadManager;
use Crustum\Meta\Routing\RouteAttributeParser;
use Crustum\Meta\Tags\Title;
use Crustum\Meta\Test\Fixtures\ConflictingRouteAttribute;
use Crustum\Meta\Test\Fixtures\ReadingTime;

it('renders a registered custom tag builder', function (): void {
    $head = $this->head();

    $head->extend(ReadingTime::class);

    $this->route('/article', RouteAttributeParser::metadata(['readingTime' => 7]));

    expect($head->toHtml())
        ->toContain('<meta name="twitter:label1" content="Reading time">')
        ->toContain('<meta name="twitter:data1" content="7 min read">');

    expect($head->toArray()['readingTime'])->toBe(7);
});

it('resolves custom builder values from route attributes', function (): void {
    $head = $this->head();

    $head->extend(ReadingTime::class);

    $this->route('/article', RouteAttributeParser::metadata(['readingTime' => 4]));

    expect($head->toHtml())->toContain('<meta name="twitter:data1" content="4 min read">');
});

it('rejects tag extensions that do not extend tag builders', function (): void {
    expect(fn(): HeadManager => $this->head()->extend(stdClass::class))
        ->toThrow(InvalidArgumentException::class, 'Head tag extensions must extend');
});

it('rejects built in tag builders', function (): void {
    expect(fn(): HeadManager => $this->head()->extend(Title::class))
        ->toThrow(InvalidArgumentException::class, 'Built-in head tag builders are already registered.');
});

it('rejects tag extensions that register duplicate route attribute keys', function (): void {
    expect(fn(): HeadManager => $this->head()->extend(ConflictingRouteAttribute::class))
        ->toThrow(InvalidArgumentException::class, 'Head tag extensions may not register route attributes already registered by another builder: title.');
});
