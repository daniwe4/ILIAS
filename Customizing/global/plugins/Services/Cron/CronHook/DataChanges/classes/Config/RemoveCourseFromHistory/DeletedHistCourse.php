<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\DataChanges\Config\RemoveCourseFromHistory;

class DeletedHistCourse
{
	/**
	 * @var int
	 */
	protected $crs_id;

	/**
	 * @var string
	 */
	protected $title;

	public function __construct(
		int $crs_id,
		string $title
	) {
		$this->crs_id = $crs_id;
		$this->title = $title;
	}

	public function getCrsId() : int
	{
		return $this->crs_id;
	}

	public function getTitle() : string
	{
		return $this->title;
	}
}