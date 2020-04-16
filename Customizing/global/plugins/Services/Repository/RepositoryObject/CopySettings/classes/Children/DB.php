<?php

namespace CaT\Plugins\CopySettings\Children;

/**
 * Interface for handle copy settings of children
 *
 * @author Stefan Hecken 	<stefan.hecken@cocnepts-and-training.de>
 */
interface DB
{
    /**
     * Saves and creates copy settings for a child
     *
     * @param int 	$obj_id
     * @param int 	$target_ref_id
     * @param int 	$target_obj_id
     * @param bool 	$is_referenced
     * @param sring 	$process_type
     *
     * @return Child
     */
    public function create($obj_id, $target_ref_id, $target_obj_id, $is_referenced, $process_type);

    /**
     * Get all copy settings of obj id
     *
     * @param int 	$obj_id
     *
     * @return Child[]
     */
    public function select($obj_id);

    /**
     * Delete all copy settings of obj id
     *
     * @param int 	$obj_id
     *
     * @return void
     */
    public function delete($obj_id);
}
