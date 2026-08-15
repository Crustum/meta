<?php
declare(strict_types=1);

namespace Crustum\Meta\Schema;

use Crustum\Meta\SchemaType;

/**
 * Web site schema.org object.
 */
#[SchemaType('WebSite')]
class WebSite extends SchemaObject
{
    /**
     * Set the web site name.
     *
     * @param string $name Web site name
     * @return static
     */
    public function name(string $name): static
    {
        return $this->set('name', $name);
    }

    /**
     * Set the web site URL.
     *
     * @param string $url Web site URL
     * @return static
     */
    public function url(string $url): static
    {
        return $this->set('url', $url);
    }
}
