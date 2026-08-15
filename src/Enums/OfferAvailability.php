<?php
declare(strict_types=1);

namespace Crustum\Meta\Enums;

/**
 * Schema.org offer availability values.
 */
enum OfferAvailability: string
{
    case BackOrder = 'BackOrder';
    case Discontinued = 'Discontinued';
    case InStock = 'InStock';
    case InStoreOnly = 'InStoreOnly';
    case LimitedAvailability = 'LimitedAvailability';
    case MadeToOrder = 'MadeToOrder';
    case OnlineOnly = 'OnlineOnly';
    case OutOfStock = 'OutOfStock';
    case PreOrder = 'PreOrder';
    case PreSale = 'PreSale';
    case Reserved = 'Reserved';
    case SoldOut = 'SoldOut';

    /**
     * Schema.org URL for this availability value.
     *
     * @return string
     */
    public function url(): string
    {
        return 'https://schema.org/' . $this->value;
    }
}
