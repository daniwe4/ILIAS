<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */
/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

namespace CaT\Plugins\Agenda\AgendaEntry;

use DateTime;

interface DB
{
    public function create(
        int $obj_id,
        int $pool_item_id,
        int $duration,
        int $position,
        bool $is_blank,
        string $agenda_item_content = null,
        string $goals = null
    ) : AgendaEntry;

    public function update(AgendaEntry $entry);

    public function delete(int $id);

    public function deleteFor(int $obj_id);

    /**
     * @param int $obj_id
     * @return AgendaEntry[]
     */
    public function selectFor(int $obj_id) : array;
}
