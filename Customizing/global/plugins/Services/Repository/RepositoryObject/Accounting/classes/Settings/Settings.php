<?php

/* Copyright (c) 2018 Daniel Weise <daniel.weise@concepts-and-training.de> */
/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Accounting\Settings;

/**
 * This is the object for additional settings.
 */
class Settings
{
    /**
     * @var bool
     */
    protected $finalized;

    /**
     * @var integer
     */
    protected $obj_id;

    /**
     * @var bool
     */
    protected $edit_fee;

    public function __construct(int $obj_id, bool $finalized, bool $edit_fee)
    {
        $this->obj_id = $obj_id;
        $this->finalized = $finalized;
        $this->edit_fee = $edit_fee;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function getFinalized() : bool
    {
        return $this->finalized;
    }

    public function withFinalized(bool $value) : Settings
    {
        $clone = clone $this;
        $clone->finalized = $value;
        return $clone;
    }

    public function getEditFee() : bool
    {
        return $this->edit_fee;
    }

    public function withEditFee(bool $value) : Settings
    {
        $clone = clone $this;
        $clone->edit_fee = $value;
        return $clone;
    }
}
