<?php
declare(strict_types=1);

namespace Crustum\Meta\Schema;

use Crustum\Meta\SchemaType;
use DateTimeInterface;

/**
 * Article schema.org object.
 */
#[SchemaType('Article')]
class Article extends SchemaObject
{
    /**
     * Set the article headline.
     *
     * @param string $headline Article headline
     * @return static
     */
    public function headline(string $headline): static
    {
        return $this->set('headline', $headline);
    }

    /**
     * Set the article description.
     *
     * @param string $description Article description
     * @return static
     */
    public function description(string $description): static
    {
        return $this->set('description', $description);
    }

    /**
     * Set the article author.
     *
     * @param \Crustum\Meta\Schema\Person|\Crustum\Meta\Schema\Organization $author Author
     * @return static
     */
    public function author(Person|Organization $author): static
    {
        return $this->set('author', $author);
    }

    /**
     * Set the article publication date.
     *
     * @param \DateTimeInterface|string $date Publication date
     * @return static
     */
    public function publishedAt(DateTimeInterface|string $date): static
    {
        return $this->date('datePublished', $date);
    }

    /**
     * Set the article modification date.
     *
     * @param \DateTimeInterface|string $date Modification date
     * @return static
     */
    public function modifiedAt(DateTimeInterface|string $date): static
    {
        return $this->date('dateModified', $date);
    }

    /**
     * Set the article image.
     *
     * @param array<int, string>|string $image Image URL or URLs
     * @return static
     */
    public function image(string|array $image): static
    {
        return $this->set('image', $image);
    }
}
