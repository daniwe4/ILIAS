<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Accomodation\Reservation\Note;

class Note
{
    /**
     * @var int
     */
    protected $oac_obj_id;

    /**
     * @var int
     */
    protected $usr_id;

    /**
     * @var string
     */
    protected $note;

    public function __construct(int $oac_obj_id, int $usr_id, string $note)
    {
        $this->oac_obj_id = $oac_obj_id;
        $this->usr_id = $usr_id;
        $this->note = $note;
    }

    public function getOacObjId() : int
    {
        return $this->oac_obj_id;
    }

    public function withOacObjId(int $oac_obj_id) : Note
    {
        $clone = clone $this;
        $clone->oac_obj_id = $oac_obj_id;
        return $clone;
    }

    public function getUsrId() : int
    {
        return $this->usr_id;
    }

    public function withUsrId(int $usr_id) : Note
    {
        $clone = clone $this;
        $clone->usr_id = $usr_id;
        return $clone;
    }

    public function getNote() : string
    {
        return $this->note;
    }

    public function withNote(string $note) : Note
    {
        $clone = clone $this;
        $clone->note = $note;
        return $clone;
    }
}
