<?php
declare(strict_types=1);

use Crustum\Meta\Enums\RobotsRule;
use Crustum\Meta\Routing\RouteAttributeParser;

it('does not render robots tags by default', function (): void {
    expect($this->head()->toHtml())->not->toContain('name="robots"');
});

it('renders robots tags from strings', function (): void {
    $head = $this->head();

    $head->robots('noindex, nofollow');

    expect($head->toHtml())->toContain('<meta name="robots" content="noindex, nofollow">');
});

it('renders robots tags from rules', function (): void {
    $head = $this->head();

    $head->robots([RobotsRule::NoIndex, RobotsRule::NoFollow]);

    expect($head->toHtml())->toContain('<meta name="robots" content="noindex, nofollow">');
});

it('renders robots tags from a single rule', function (): void {
    $head = $this->head();

    $head->robots(RobotsRule::NoIndex);

    expect($head->toHtml())->toContain('<meta name="robots" content="noindex">');
});

it('renders tags for pages searchable by robots', function (): void {
    $head = $this->head();

    $head->searchableByRobots();

    expect($head->toHtml())->toContain('<meta name="robots" content="all">');
});

it('renders tags for pages hidden from robots', function (): void {
    $head = $this->head();

    $head->hiddenFromRobots();

    expect($head->toHtml())->toContain('<meta name="robots" content="none">');
});

it('renders robots tags from mixed string and enum rules', function (): void {
    $head = $this->head();

    $head->robots(['noindex', RobotsRule::NoFollow]);

    expect($head->toHtml())->toContain('<meta name="robots" content="noindex, nofollow">');
});

it('resolves robots rules from route attributes', function (): void {
    $head = $this->head();

    $this->route('/private', RouteAttributeParser::metadata([
        'robots' => [RobotsRule::NoIndex, RobotsRule::NoFollow],
    ]));

    expect($head->toHtml())->toContain('<meta name="robots" content="noindex, nofollow">');
});
