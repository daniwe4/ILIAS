<?php

namespace CaT\Plugins\CourseMember\Members;

/**
 * This is an interface to handle member informations in a backend.
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    /**
     * Creates a new member object and entry or updates existing
     *
     * @param Member 	$member
     *
     * @return void
     */
    public function upsert(Member $member);

    /**
     * Select all member informations
     *
     * @param int 	$crs_id
     *
     * @return Member[]
     */
    public function select($crs_id);

    /**
     * Delete entry for user and course
     *
     * @param int 	$user_id
     * @param int 	$crs_id
     *
     * @return void
     */
    public function deleteForUserAndCourse($user_id, $crs_id);

    /**
     * Delete entries for course
     *
     * @param int 	$crs_id
     *
     * @return void
     */
    public function deleteForCourse($crs_id);
}
