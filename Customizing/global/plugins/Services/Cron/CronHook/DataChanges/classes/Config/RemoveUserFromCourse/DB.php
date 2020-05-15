<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\DataChanges\Config\RemoveUserFromCourse;

interface DB
{
	public function deleteUsersFromHistForCrs(int $usr_id, int $crs_id);
	public function deleteUserFromCourse(int $user_id, int $crs_id);
	public function hasUserAnnouncedIddTimes(int $user_id, int $crs_id) : bool;
	public function getBookingId(int $user_id, int $crs_id) : string;
	public function getBwvId(int $user_id, int $field_id) : string;
}