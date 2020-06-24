<?php

namespace CaT\Plugins\AgendaItemPool\Options;

/**
 * Abstract to define functions each option must use
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
abstract class ilActions
{
    /**
     * @var \ilCourseClassificationPlugin
     */
    protected $plugin_object;

    /**
     * Return the data for table oberview
     *
     * @return Option[]
     */
    abstract public function getTableData() : array;

    /**
     * Create a new option
     *
     * @param string 	$agenda_item_id
     * @param string 	$caption_id
     *
     * @return Option
     */
    abstract public function create(string $agenda_item_id, string $caption_id) : Option;

    /**
     * Delete options
     *
     * @param int 	$option_id
     * @param int 	$caption_id
     *
     * @return void
     */
    abstract public function delete(int $option_id, int $caption_id) : void;
}
