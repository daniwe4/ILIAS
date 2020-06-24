<?php

namespace CaT\Plugins\CopySettings\TemplateCourses;

interface DB
{
    /**
     * Create a new entry for a new template course
     *
     * @param int 	$obj_id
     * @param int 	$crs_id
     * @param int 	$crs_ref_id
     *
     * @return void
     */
    public function create(int $obj_id, int $crs_id, int $crs_ref_id);

    /**
     * Delete template vals for object
     *
     * @param int 	$obj_id
     *
     * @return void
     */
    public function deleteFor(int $obj_id);

    /**
     * Checks the given obj id is an template course
     *
     * @param int 	$crs_id
     *
     * @return bool
     */
    public function isTemplateByObjId(int $crs_id);

    /**
     * Checks the given obj id is an template course
     *
     * @param int 	$crs_ref_id
     *
     * @return bool
     */
    public function isTemplateByRefId(int $crs_ref_id);
}
