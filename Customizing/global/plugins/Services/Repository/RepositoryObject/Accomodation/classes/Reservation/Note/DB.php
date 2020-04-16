<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Accomodation\Reservation\Note;

interface DB
{
    public function createNote(int $aoc_obj_id, int $usr_id, string $note) : Note;
    public function update(int $oac_obj_id, int $usr_id, string $note);
    public function nodeExists(int $oac_obj_id, int $usr_id) : bool;
}
