<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\ScaledFeedback\Settings;

class Settings
{
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var int
     */
    protected $set_id;

    /**
     * @var bool
     */
    protected $online;

    /**
     * @var int
     */
    protected $lp_mode;

    public function __construct(
        int $obj_id,
        int $set_id,
        bool $online,
        int $lp_mode = 0
    ) {
        $this->obj_id = $obj_id;
        $this->set_id = $set_id;
        $this->online = $online;
        $this->lp_mode = $lp_mode;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function withObjId(int $value) : Settings
    {
        $clone = clone $this;
        $clone->obj_id = $value;
        return $clone;
    }

    public function getSetId() : int
    {
        return $this->set_id;
    }

    public function withSetId(int $value) : Settings
    {
        $clone = clone $this;
        $clone->set_id = $value;
        return $clone;
    }

    public function getOnline() : bool
    {
        return $this->online;
    }

    public function withOnline(bool $value) : Settings
    {
        $clone = clone $this;
        $clone->online = $value;
        return $clone;
    }

    public function getLPMode() : int
    {
        return $this->lp_mode;
    }

    public function withLPMode(int $value) : Settings
    {
        $clone = clone $this;
        $clone->lp_mode = $value;
        return $clone;
    }
}
