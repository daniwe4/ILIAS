<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\DataChanges\Config\MergeUsers;

use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
	/**
	 * @var int
	 */
	protected $crs_id;

	/**
	 * @var int
	 */
	protected $user_id;

	/**
	 * @var HistUserData
	 */
	protected $hist_user_data;

	public function setUp()
	{
		$this->crs_id = 1;
		$this->user_id = 6;

		$this->hist_user_data = new HistUserData(
			$this->crs_id,
			$this->user_id
		);
	}

	public function testCreate()
	{
		$this->assertEquals($this->crs_id, $this->hist_user_data->getCrsId());
		$this->assertEquals($this->user_id, $this->hist_user_data->getUserId());
	}
}