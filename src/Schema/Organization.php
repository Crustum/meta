<?php
declare(strict_types=1);

namespace Crustum\Meta\Schema;

use Crustum\Meta\SchemaType;

/**
 * Organization schema.org object.
 */
#[SchemaType('Organization')]
class Organization extends SchemaObject
{
    /**
     * Set the organization name.
     *
     * @param string $name Organization name
     * @return static
     */
    public function name(string $name): static
    {
        return $this->set('name', $name);
    }

    /**
     * Set the organization URL.
     *
     * @param string $url Organization URL
     * @return static
     */
    public function url(string $url): static
    {
        return $this->set('url', $url);
    }

    /**
     * Set the organization logo URL.
     *
     * @param string $url Logo URL
     * @return static
     */
    public function logo(string $url): static
    {
        return $this->set('logo', $url);
    }
}
