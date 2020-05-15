<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\DataChanges\Config\MergeUsers;

class HistUserData
{
	/**
	 * @var int
	 */
	protected $crs_id;

	/**
	 * @var int
	 */
	protected $user_id;

	public function __construct(
		int $crs_id,
		int $user_id
	) {
		$this->crs_id = $crs_id;
		$this->user_id = $user_id;
	}

	public function getCrsId() : int
	{
		return $this->crs_id;
	}

	public function getUserId() : int
	{
		return $this->user_id;
	}
}