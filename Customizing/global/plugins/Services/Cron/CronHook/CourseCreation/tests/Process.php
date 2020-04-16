<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

/**
 * Creates courses based on templates.
 */
interface Process
{
    /**
     * Run the course creation process for a given course.
     *
     * @return Request
     */
    public function run(Request $request);
}
