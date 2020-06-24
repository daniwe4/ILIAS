<?php

namespace CaT\Plugins\AgendaItemPool\Options;

/**
 * Base interface for all options in AgendaItemPool.
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
interface DB
{
    /**
     * Create a new entry for any option and returns the new object
     *
     * @param int 		$agenda_item_id
     * @param string 		$caption_id
     *
     * @return Option
     */
    public function create(int $agenda_item_id, string $caption_id) : Option;

    /**
     * Select any defined options
     *
     * @param int 	$agenda_item_id
     *
     * @return Option[]
     */
    public function select(int $agenda_item_id) : array;

    /**
     * Delete a option by id
     *
     * @param int 	$agenda_item_id
     * @param int 	$caption_id
     *
     * @return void
     */
    public function delete(int $agenda_item_id, int $caption_id) : void;
}
