<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\DataChanges\Config\RemoveCourseFromHistory;

use DateTime;

interface DB
{
	public function getDeletedHistCourses(string $title, DateTime $start) : array;

	/**
	 * @return int|bool  > 0 for affected rows, false for no affected row
	 */
	public function deleteCourseFromHist(DeletedHistCourse $course, int $user_id = null);
}