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
    public function update(CourseClassification $course_classification) : void;

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
     * @return CourseClassification
     */
    public function create(
        int $obj_id,
        ?int $type = null,
        ?int $edu_program = null,
        array $topics = null,
        array $categories = null,
        ?int $content = null,
        ?string $goals = null,
        ?string $preparation = null,
        array $method = null,
        array $media = null,
        array $target_group = null,
        ?int $target_group_description = null,
        string $contact_name = "",
        string $contact_responsibility = "",
        string $contact_phone = "",
        string $contact_mail = ""
    ) : CourseClassification;

    /**
     * return CourseClassification for $obj_id
     *
     * @param int $obj_id
     *
     * @throws \LogicException 	if no setting entries is found
     *
     * @return CourseClassification
     */
    public function selectFor(int $obj_id) : CourseClassification;

    /**
     * Delete all information of the given obj id
     *
     * @param 	int 	$obj_id
     */
    public function deleteFor(int $obj_id) : void;
}
