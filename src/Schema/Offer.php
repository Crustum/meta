<?php
declare(strict_types=1);

namespace Crustum\Meta\Schema;

use Crustum\Meta\Enums\OfferAvailability;
use Crustum\Meta\SchemaType;

/**
 * Offer schema.org object.
 */
#[SchemaType('Offer')]
class Offer extends SchemaObject
{
    /**
     * Set the offer price.
     *
     * @param string|float|int $price Offer price
     * @return static
     */
    public function price(float|int|string $price): static
    {
        return $this->set('price', $price);
    }

    /**
     * Set the offer price currency.
     *
     * @param string $currency ISO-4217 currency code
     * @return static
     */
    public function currency(string $currency): static
    {
        return $this->set('priceCurrency', $currency);
    }

    /**
     * Set the offer availability.
     *
     * @param \Crustum\Meta\Enums\OfferAvailability $availability Availability
     * @return static
     */
    public function availability(OfferAvailability $availability): static
    {
        return $this->set('availability', $availability->url());
    }
}
