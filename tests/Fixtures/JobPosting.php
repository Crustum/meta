<?php
declare(strict_types=1);

namespace Crustum\Meta\Test\Fixtures;

use Crustum\Meta\Schema\SchemaObject;
use Crustum\Meta\SchemaType;
use DateTimeInterface;

/**
 * Test JSON-LD schema object for job postings.
 */
#[SchemaType('JobPosting')]
class JobPosting extends SchemaObject
{
    /**
     * Set the job posting title.
     *
     * @param string $title Job title
     * @return static
     */
    public function title(string $title): static
    {
        return $this->set('title', $title);
    }

    /**
     * Set the job posting date.
     *
     * @param \DateTimeInterface|string $date Date posted
     * @return static
     */
    public function datePosted(DateTimeInterface|string $date): static
    {
        return $this->date('datePosted', $date);
    }
}
