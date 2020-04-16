<?php

namespace CaT\Plugins\MaterialList\Settings;

/**
 * Interace for extendes settings db
 */
interface DB
{
    /**
     * Install needet tables etc.
     *
     * @return null
     */
    public function install();

    /**
     * Create a new extended settings entry
     *
     * @param int 			$obj_id
     * @param \ilDateTime 	$last_edit_datetime
     * @param int 			$last_edit_by
     * @param string 		$recipient_mode
     * @param int | null 	$send_days_before
     *
     * @return MaterialList
     */
    public function create($obj_id, \ilDateTime $last_edit_datetime, $last_edit_by, $recipient_mode, $send_days_before = null);

    /**
     * Update an existing settings entry
     *
     * @param MaterialList 	$material_list
     *
     * @return null
     */
    public function update(MaterialList $material_list);

    /**
     * Get settings for object
     *
     * @param int 	$obj_id
     *
     * @throws \LogicException if no settings for obj are available
     *
     * @return MaterialList
     */
    public function selectFor($obj_id);

    /**
     * Delete settings for obj
     *
     * @param int 	$obj_id
     *
     * @return null
     */
    public function deleteFor($obj_id);
}
