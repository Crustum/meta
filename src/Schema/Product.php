<?php
declare(strict_types=1);

namespace Crustum\Meta\Schema;

use Crustum\Meta\SchemaType;

/**
 * Product schema.org object.
 */
#[SchemaType('Product')]
class Product extends SchemaObject
{
    /**
     * Set the product name.
     *
     * @param string $name Product name
     * @return static
     */
    public function name(string $name): static
    {
        return $this->set('name', $name);
    }

    /**
     * Set the product description.
     *
     * @param string $description Product description
     * @return static
     */
    public function description(string $description): static
    {
        return $this->set('description', $description);
    }

    /**
     * Set the product image.
     *
     * @param array<int, string>|string $image Image URL or URLs
     * @return static
     */
    public function image(string|array $image): static
    {
        return $this->set('image', $image);
    }

    /**
     * Set the product SKU.
     *
     * @param string $sku Product SKU
     * @return static
     */
    public function sku(string $sku): static
    {
        return $this->set('sku', $sku);
    }

    /**
     * Set the product brand.
     *
     * @param \Crustum\Meta\Schema\Brand|\Crustum\Meta\Schema\Organization $brand Brand
     * @return static
     */
    public function brand(Brand|Organization $brand): static
    {
        return $this->set('brand', $brand);
    }

    /**
     * Set the product offers.
     *
     * @param \Crustum\Meta\Schema\Offer|array<int, \Crustum\Meta\Schema\Offer> $offers Offers
     * @return static
     */
    public function offers(Offer|array $offers): static
    {
        return $this->set('offers', $offers);
    }
}
