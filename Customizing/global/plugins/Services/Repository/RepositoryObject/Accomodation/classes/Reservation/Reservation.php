<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Accomodation\Reservation;

use ilDate;
use ilDateTime;

/**
 * A reservation is the relation of a user to a venue at a single date.
 * This is a single date with the reference to a session, which in turn will
 * have information about the venue.
 */
class Reservation
{

    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $oac_obj_id;

    /**
     * @var int
     */
    protected $usr_id;

    /**
     * @var ilDate[]
     */
    protected $date;

    /**
     * @var bool
     */
    protected $selfpay;

    public function __construct(
        int $id,
        int $oac_obj_id,
        int $usr_id,
        ilDateTime $date,
        bool $selfpay = false
    ) {
        $this->id = $id;
        $this->oac_obj_id = $oac_obj_id;
        $this->usr_id = $usr_id;
        $this->date = $date;
        $this->selfpay = $selfpay;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getAccomodationObjId() : int
    {
        return $this->oac_obj_id;
    }

    public function getDate() : ilDateTime
    {
        return $this->date;
    }

    public function getUserId() : int
    {
        return (int) $this->usr_id;
    }

    /**
     * Get the session's id this reservation relates to;
     * however, reservation holds its own date, so changing the session
     * will _not_ update this.
     */
    public function getSessionObjId()
    {
        return $this->ses_obj_id;
    }

    public function getSelfpay() : bool
    {
        return $this->selfpay;
    }

    /**
     * get a Reservation with selfpay
     */
    public function withSelfpay(bool $selfpay) : Reservation
    {
        $clone = clone $this;
        $clone->selfpay = $selfpay;
        return $clone;
    }
}
