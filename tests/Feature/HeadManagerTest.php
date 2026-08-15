<?php
declare(strict_types=1);

it('renders the head as an html string', function (): void {
    $head = $this->head();

    expect($head->render())->toBeString();
});
