<?php
declare(strict_types=1);

namespace Crustum\Meta\Schema;

use Crustum\Meta\SchemaType;
use DateTimeInterface;

/**
 * Event schema.org object.
 */
#[SchemaType('Event')]
class Event extends SchemaObject
{
    /**
     * Set the event description.
     *
     * @param string $description Event description
     * @return static
     */
    public function description(string $description): static
    {
        return $this->set('description', $description);
    }

    /**
     * Set the event door time.
     *
     * @param \DateTimeInterface|string $doorTime Door time
     * @return static
     */
    public function doorTime(DateTimeInterface|string $doorTime): static
    {
        return $this->date('doorTime', $doorTime);
    }

    /**
     * Set the event end date.
     *
     * @param \DateTimeInterface|string $endDate End date
     * @return static
     */
    public function endDate(DateTimeInterface|string $endDate): static
    {
        return $this->date('endDate', $endDate);
    }

    /**
     * Set the event name.
     *
     * @param string $name Event name
     * @return static
     */
    public function name(string $name): static
    {
        return $this->set('name', $name);
    }

    /**
     * Set the event offers.
     *
     * @param \Crustum\Meta\Schema\Offer|array<int, \Crustum\Meta\Schema\Offer> $offers Offers
     * @return static
     */
    public function offers(Offer|array $offers): static
    {
        return $this->set('offers', $offers);
    }

    /**
     * Set the event organizer.
     *
     * @param \Crustum\Meta\Schema\Organization|\Crustum\Meta\Schema\Person $organizer Organizer
     * @return static
     */
    public function organizer(Organization|Person $organizer): static
    {
        return $this->set('organizer', $organizer);
    }

    /**
     * Set the event start date.
     *
     * @param \DateTimeInterface|string $startDate Start date
     * @return static
     */
    public function startDate(DateTimeInterface|string $startDate): static
    {
        return $this->date('startDate', $startDate);
    }
}
