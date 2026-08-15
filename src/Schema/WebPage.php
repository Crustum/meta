<?php
declare(strict_types=1);

namespace Crustum\Meta\Schema;

use Crustum\Meta\SchemaType;

/**
 * Web page schema.org object.
 */
#[SchemaType('WebPage')]
class WebPage extends SchemaObject
{
    /**
     * Set the web page name.
     *
     * @param string $name Web page name
     * @return static
     */
    public function name(string $name): static
    {
        return $this->set('name', $name);
    }

    /**
     * Set the web page description.
     *
     * @param string $description Web page description
     * @return static
     */
    public function description(string $description): static
    {
        return $this->set('description', $description);
    }

    /**
     * Set the web page URL.
     *
     * @param string $url Web page URL
     * @return static
     */
    public function url(string $url): static
    {
        return $this->set('url', $url);
    }
}
