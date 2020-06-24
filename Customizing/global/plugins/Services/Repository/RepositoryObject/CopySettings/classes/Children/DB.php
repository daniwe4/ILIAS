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
     * @param string 	$process_type
     *
     * @return Child
     */
    public function create(
        int $obj_id,
        int $target_ref_id,
        int $target_obj_id,
        bool $is_referenced,
        string $process_type
    ) : Child;

    /**
     * Get all copy settings of obj id
     *
     * @param int 	$obj_id
     *
     * @return Child[]
     */
    public function select(int $obj_id) : array;

    /**
     * Delete all copy settings of obj id
     *
     * @param int 	$obj_id
     *
     * @return void
     */
    public function delete(int $obj_id) : void;
}
