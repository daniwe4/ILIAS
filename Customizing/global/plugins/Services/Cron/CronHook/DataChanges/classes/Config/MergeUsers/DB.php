<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\DataChanges\Config\MergeUsers;

interface DB
{
	/**
	 * @return int[]
	 */
	public function selectAllParticipatedCoursesFor(int $user_id) : array;

	/**
	 * @return int[]
	 */
	public function getOpenCourses(int $user_id) : array;

	/**
	 * @param int $to_deactivate_id
	 * @param int $active_id
	 *
	 * @return int[]
	 */
	public function getSameBookedCourses(int $to_deactivate_id, int $active_id) : array;

	public function mergeUserData(int $user_to_deactivate, int $user_to_activate);
}