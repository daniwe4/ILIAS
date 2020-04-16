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
    public function create($obj_id, $crs_id, $crs_ref_id);

    /**
     * Delete template vals for object
     *
     * @param int 	$obj_id
     *
     * @return void
     */
    public function deleteFor($obj_id);

    /**
     * Checks the given obj id is an template course
     *
     * @param int 	$crs_id
     *
     * @return bool
     */
    public function isTemplateByObjId($crs_id);

    /**
     * Checks the given obj id is an template course
     *
     * @param int 	$crs_ref_id
     *
     * @return bool
     */
    public function isTemplateByRefId($crs_ref_id);
}
