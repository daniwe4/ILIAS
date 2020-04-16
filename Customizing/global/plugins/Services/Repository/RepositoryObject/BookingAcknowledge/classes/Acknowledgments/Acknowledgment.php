<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingAcknowledge\Acknowledgments;

/**
 *
 */
class Acknowledgment
{
    //states
    const OPEN = 1;
    const APPROVED = 2;
    const DECLINED = 3;
    /*
    const OUTDATED = 4;
    const CANCELED_BY_USER = 5;
    const CANCELED_FOR_REASONS = 6;
    */

    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $acting_usr_id;

    /**
     * @var int
     */
    protected $usr_id;

    /**
     * @var int
     */
    protected $crs_ref_id;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var int
     */
    protected $state;

    public function __construct(
        int $id,
        int $acting_usr_id,
        int $usr_id,
        int $crs_ref_id,
        \DateTime $date,
        int $state = 1
    ) {
        $this->id = $id;
        $this->acting_usr_id = $acting_usr_id;
        $this->usr_id = $usr_id;
        $this->crs_ref_id = $crs_ref_id;
        $this->date = $date;
        $this->checkValidState($state);
        $this->state = $state;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getUserId() : int
    {
        return $this->usr_id;
    }

    public function getCourseRefId() : int
    {
        return $this->crs_ref_id;
    }

    public function getActingUserId() : int
    {
        return $this->acting_usr_id;
    }

    public function getLastUpdateDate() : \DateTime
    {
        return $this->date;
    }

    /**
     * Check, if $state is valid.
     * @throws \InvalidArgumentException
     */
    protected function checkValidState($state)
    {
        if (!in_array($state, [
            static::OPEN,
            static::APPROVED,
            static::DECLINED,
            /*
            static::OUTDATED,
            static::CANCELED_BY_USER,
            static::CANCELED_FOR_REASONS
            */
        ])) {
            throw new \InvalidArgumentException("Invalid State: $state", 1);
        }
    }

    public function withState(int $state) : BookingRequest
    {
        $this->checkValidState($state);
        $clone = clone $this;
        $clone->state = $state;
        return $clone;
    }

    public function getState() : int
    {
        return $this->state;
    }
}
