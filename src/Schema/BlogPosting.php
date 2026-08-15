<?php
declare(strict_types=1);

namespace Crustum\Meta\Schema;

use Crustum\Meta\SchemaType;

/**
 * Blog posting schema.org object.
 */
#[SchemaType('BlogPosting')]
class BlogPosting extends Article
{
}
