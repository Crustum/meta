<?php
declare(strict_types=1);

namespace Crustum\Meta\Schema;

use Cake\Utility\Inflector;
use ReflectionClass;

/**
 * Creates schema objects for registered or arbitrary schema.org types.
 */
class SchemaFactory
{
    /**
     * Registered schema object types keyed by their camelized class name.
     *
     * @var array<string, class-string<\Crustum\Meta\Schema\SchemaObject>>
     */
    protected array $types = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->register(Article::class);
        $this->register(BlogPosting::class);
        $this->register(Event::class);
        $this->register(Product::class);
        $this->register(Offer::class);
        $this->register(Brand::class);
        $this->register(Breadcrumbs::class);
        $this->register(Faq::class);
        $this->register(Organization::class);
        $this->register(Person::class);
        $this->register(WebPage::class);
        $this->register(WebSite::class);
    }

    /**
     * Register a first-class schema object type.
     *
     * @param class-string<\Crustum\Meta\Schema\SchemaObject> $class Schema object class
     * @return static
     */
    public function register(string $class): static
    {
        $this->types[Inflector::variable((new ReflectionClass($class))->getShortName())] = $class;

        return $this;
    }

    /**
     * Make a schema object from a schema object class or registered type name.
     *
     * @template TSchema of \Crustum\Meta\Schema\SchemaObject
     * @param class-string<TSchema>|string $type Schema object class or type name
     * @return ($type is class-string<TSchema> ? TSchema : \Crustum\Meta\Schema\SchemaObject)
     */
    public function make(string $type): SchemaObject
    {
        if (is_subclass_of($type, SchemaObject::class)) {
            return new $type();
        }

        $class = $this->types[Inflector::variable($type)] ?? null;

        return $class ? new $class() : new GenericSchemaObject(Inflector::camelize($type));
    }

    /**
     * Dynamically make a schema object for the called type name.
     *
     * @param string $method Type name
     * @param array<int, mixed> $parameters Method parameters
     * @return \Crustum\Meta\Schema\SchemaObject
     */
    public function __call(string $method, array $parameters): SchemaObject
    {
        return $this->make($method);
    }
}
