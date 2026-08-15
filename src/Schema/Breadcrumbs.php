<?php
declare(strict_types=1);

namespace Crustum\Meta\Schema;

use Crustum\Meta\SchemaType;

/**
 * Breadcrumb list schema.org object.
 */
#[SchemaType('BreadcrumbList')]
class Breadcrumbs extends SchemaObject
{
    /**
     * Breadcrumb list items.
     *
     * @var array<int, array{'@type': string, position: int, name: string, item: string}>
     */
    protected array $items = [];

    /**
     * Add a breadcrumb item.
     *
     * @param string $name Item name
     * @param string $url Item URL
     * @return static
     */
    public function item(string $name, string $url): static
    {
        $this->items[] = [
            '@type' => 'ListItem',
            'position' => count($this->items) + 1,
            'name' => $name,
            'item' => $url,
        ];

        return $this->set('itemListElement', $this->items);
    }

    /**
     * Add multiple breadcrumb items keyed by name to URL.
     *
     * @param array<string, string> $items Breadcrumb names keyed to their URLs
     * @return static
     */
    public function items(array $items): static
    {
        foreach ($items as $name => $url) {
            $this->item($name, $url);
        }

        return $this;
    }
}
