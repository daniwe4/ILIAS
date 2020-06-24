<?php

namespace CaT\Plugins\BookingModalities\Settings\SelectableReasons;

/**
 * Inteface for db handling of reasons
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    /**
     * Delete all reason by id
     *
     * @param int 	$id
     *
     * @return null
     */
    public function delete(int $id);

    /**
     * Creates a reason object
     *
     * @param string 	$reason
     * @param bool 	$active
     *
     * @return SelectableReason
     */
    public function create(string $reason, bool $active);

    /**
     * Update existing selectable reason
     *
     * @param SelectableReason
     *
     * @return void
     */
    public function update(SelectableReason $selectable_reason);

    /**
     * Get all configured reasons
     *
     * @return SelectableReason[]
     */
    public function select();

    /**
     * Get options array for ILIAS form gui's
     *
     * @param string 	$parent
     *
     * @return string[]
     */
    public function getReasonOptions(string $parent);
}
