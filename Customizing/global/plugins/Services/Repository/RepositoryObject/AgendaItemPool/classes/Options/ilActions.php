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
     * @return Options[]
     */
    abstract public function getTableData();

    /**
     * Create a new option
     *
     * @param string 	$agenda_item_id
     * @param string 	$caption_id
     *
     * @return Option
     */
    abstract public function create($agenda_item_id, $caption_id);

    /**
     * Delete options
     *
     * @param int 	$option_id
     * @param int 	$caption_id
     *
     * @return null
     */
    abstract public function delete($option_id, $caption_id);
}
