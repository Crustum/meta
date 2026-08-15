<?php
declare(strict_types=1);

namespace Crustum\Meta\Test\Fixtures;

use Crustum\Meta\Tags\TagBuilder;
use Override;

/**
 * Test tag builder that conflicts with the title route attribute.
 *
 * @phpstan-consistent-constructor
 */
class ConflictingRouteAttribute extends TagBuilder
{
    /**
     * @return string
     */
    public static function key(): string
    {
        return 'conflictingRouteAttribute';
    }

    /**
     * @return array<int, string>
     */
    #[Override]
    public static function routeAttributeKeys(): array
    {
        return ['title'];
    }

    /**
     * @inheritDoc
     */
    public function overlayOn(?TagBuilder $base): static
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isEmpty(): bool
    {
        return true;
    }
}
