<?php
declare(strict_types=1);

namespace Crustum\Meta;

use Attribute;

/**
 * Declares the schema.org type name for a schema object class.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class SchemaType
{
    /**
     * Constructor.
     *
     * @param string $name Schema.org type name
     */
    public function __construct(public string $name)
    {
    }
}
