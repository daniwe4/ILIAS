<?php

namespace CaT\Plugins\CourseClassification\Settings;

/**
 * Interface for DB handle of additional setting values
 */
interface DB
{
    /**
     * Update settings of an existing repo object.
     *
     * @param	CourseClassification		$course_classification
     */
    public function update(CourseClassification $course_classification);

    /**
     * Create new CourseClassification for obj_id.
     *
     * @param int 			$obj_id
     * @param int | null	$type
     * @param int | null	$edu_program
     * @param int[] | null	$topics
     * @param int[] | null	$categories
     * @param int | null	$content
     * @param string | null	$goals
     * @param string | null $preparation
     * @param int[] | null	$method
     * @param int[] | null	$media
     * @param int[] | null	$target_group
     * @param int | null	$target_group_description
     * @param string 		$contact_name
     * @param string 		$contact_responsibility
     * @param string 		$contact_phone
     * @param string 		$contact_mail
     *
     * @return \CaT\Plugins\CourseClassification\Settings\CourseClassification
     */
    public function create(
        $obj_id,
        $type = null,
        $edu_program = null,
        array $topics = null,
        array $categories = null,
        $content = null,
        $goals = null,
        $preparation = null,
        array $method = null,
        array $media = null,
        array $target_group = null,
        $target_group_description = null,
        $contact_name = "",
        $contact_responsibility = "",
        $contact_phone = "",
        $contact_mail = ""
    );

    /**
     * return CourseClassification for $obj_id
     *
     * @param int $obj_id
     *
     * @throws \LogicException 	if no setting entries is found
     *
     * @return \CaT\Plugins\CourseClassification\Settings\CourseClassification
     */
    public function selectFor($obj_id);

    /**
     * Delete all information of the given obj id
     *
     * @param 	int 	$obj_id
     */
    public function deleteFor($obj_id);
}
