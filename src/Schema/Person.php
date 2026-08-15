<?php
declare(strict_types=1);

namespace Crustum\Meta\Schema;

use Crustum\Meta\SchemaType;

/**
 * Person schema.org object.
 */
#[SchemaType('Person')]
class Person extends SchemaObject
{
    /**
     * Set the person name.
     *
     * @param string $name Person name
     * @return static
     */
    public function name(string $name): static
    {
        return $this->set('name', $name);
    }

    /**
     * Set the person URL.
     *
     * @param string $url Person URL
     * @return static
     */
    public function url(string $url): static
    {
        return $this->set('url', $url);
    }
}
