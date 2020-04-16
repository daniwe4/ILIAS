<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */
/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Agenda\AgendaEntry;

use DateTime;

class AgendaEntry
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var int
     */
    protected $pool_item_id;

    /**
     * @var int
     */
    protected $duration;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var float
     */
    protected $idd_time;

    /**
     * @var bool
     */
    protected $is_blank;

    /**
     * @var string | null
     */
    protected $agenda_item_content;

    /**
     * @var string | null
     */
    protected $goals;

    public function __construct(
        int $id,
        int $obj_id,
        int $pool_item_id = null,
        int $duration = null,
        $position = 0,
        float $idd_time = 0.0,
        bool $is_blank = false,
        string $agenda_item_content = null,
        string $goals = null
    ) {
        $this->id = $id;
        $this->obj_id = $obj_id;
        $this->pool_item_id = $pool_item_id;
        $this->duration = $duration;
        $this->position = $position;
        $this->idd_time = $idd_time;
        $this->is_blank = $is_blank;
        $this->agenda_item_content = $agenda_item_content;
        $this->goals = $goals;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    /**
     * @return int | null
     */
    public function getPoolItemId()
    {
        return $this->pool_item_id;
    }

    /**
     * @return int | null
     */
    public function getDuration()
    {
        return $this->duration;
    }

    public function getPosition() : int
    {
        return $this->position;
    }

    public function getIDDTime() : float
    {
        return $this->idd_time;
    }

    public function getIsBlank() : bool
    {
        return $this->is_blank;
    }

    /**
     * @return string | null
     */
    public function getAgendaItemContent()
    {
        return $this->agenda_item_content;
    }

    /**
     * @return string | null
     */
    public function getGoals()
    {
        return $this->goals;
    }

    public function withId(int $id) : AgendaEntry
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function withPoolItemId(int $pool_item_id) : AgendaEntry
    {
        $clone = clone $this;
        $clone->pool_item_id = $pool_item_id;
        return $clone;
    }

    public function withDuration(int $duration) : AgendaEntry
    {
        $clone = clone $this;
        $clone->duration = $duration;
        return $clone;
    }

    public function withPosition(int $position) : AgendaEntry
    {
        $clone = clone $this;
        $clone->position = $position;
        return $clone;
    }

    public function withIDDTime(float $idd_time) : AgendaEntry
    {
        $clone = clone $this;
        $clone->idd_time = $idd_time;
        return $clone;
    }

    public function withIsBlank(bool $is_blank) : AgendaEntry
    {
        $clone = clone $this;
        $clone->is_blank = $is_blank;
        return $clone;
    }

    public function withAgendaItemContent(string $agenda_item_content = null) : AgendaEntry
    {
        $clone = clone $this;
        $clone->agenda_item_content = $agenda_item_content;
        return $clone;
    }

    public function withGoals(string $goals = null) : AgendaEntry
    {
        $clone = clone $this;
        $clone->goals = $goals;
        return $clone;
    }
}
