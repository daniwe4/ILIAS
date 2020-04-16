<?php

declare(strict_types=1);

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace CaT\Plugins\Venues\Venues;

/**
 * Object class for a single venue
 * Immutable
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class Venue
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var General\General
     */
    protected $general;

    /**
     * @var Rating\Rating
     */
    protected $rating;

    /**
     * @var Address\Address
     */
    protected $address;

    /**
     * @var Contact\Contact
     */
    protected $contact;

    /**
     * @var Conditions\Condition
     */
    protected $condition;

    /**
     * @var Capacity\Capacity
     */
    protected $capacity;

    /**
     * @var Service\Service
     */
    protected $service;

    /**
     * @var Costs\Costs
     */
    protected $costs;

    /**
     * @param int 	$id
     */
    public function __construct(
        int $id,
        General\General $general,
        Rating\Rating $rating,
        Address\Address $address,
        Contact\Contact $contact,
        Conditions\Conditions $condition,
        Capacity\Capacity $capacity,
        Service\Service $service,
        Costs\Costs $costs
    ) {
        $this->id = $id;
        $this->general = $general;
        $this->rating = $rating;
        $this->address = $address;
        $this->contact = $contact;
        $this->condition = $condition;
        $this->capacity = $capacity;
        $this->service = $service;
        $this->costs = $costs;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getGeneral() : General\General
    {
        return $this->general;
    }

    public function getRating() : Rating\Rating
    {
        return $this->rating;
    }

    public function getAddress() : Address\Address
    {
        return $this->address;
    }

    public function getContact() : Contact\Contact
    {
        return $this->contact;
    }

    public function getCondition() : Conditions\Conditions
    {
        return $this->condition;
    }

    public function getCapacity() : Capacity\Capacity
    {
        return $this->capacity;
    }

    public function getService() : Service\Service
    {
        return $this->service;
    }

    public function getCosts() : Costs\Costs
    {
        return $this->costs;
    }
}
