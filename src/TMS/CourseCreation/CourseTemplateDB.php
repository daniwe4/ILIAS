<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

/**
 * Fetches infos about the course templates a user could use to create courses.
 */
interface CourseTemplateDB
{
    /**
     * Returns an array of ref_id => info of all template courses in the
     * system.
     *
     * @return	array<int,CourseTemplateInfo>
     */
    public function getAllCourseTemplates();

    /**
     * @param	int	$user_id
     * @return CourseTemplateInfo[]
     */
    public function getCreatableCourseTemplates(int $user_id);
}
