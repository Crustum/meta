<?php
declare(strict_types=1);

namespace Crustum\Meta\Schema;

use Crustum\Meta\SchemaType;

/**
 * Brand schema.org object.
 */
#[SchemaType('Brand')]
class Brand extends SchemaObject
{
    /**
     * Set the brand name.
     *
     * @param string $name Brand name
     * @return static
     */
    public function name(string $name): static
    {
        return $this->set('name', $name);
    }

    /**
     * Set the brand URL.
     *
     * @param string $url Brand URL
     * @return static
     */
    public function url(string $url): static
    {
        return $this->set('url', $url);
    }

    /**
     * Set the brand logo URL.
     *
     * @param string $url Logo URL
     * @return static
     */
    public function logo(string $url): static
    {
        return $this->set('logo', $url);
    }
}
