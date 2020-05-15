<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\DataChanges\Config\RemoveCourseFromHistory;

use PHPUnit\Framework\TestCase;

class DeleteHistCourseTest extends TestCase
{
	/**
	 * @var int
	 */
	protected $crs_id;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var DeletedHistCourse
	 */
	protected $obj;

	public function setUp()
	{
		$this->crs_id = 1;
		$this->title = 'test_title';

		$this->obj = new DeletedHistCourse(
			$this->crs_id,
			$this->title
		);
	}

	public function testCreate()
	{
		$this->assertEquals($this->crs_id, $this->obj->getCrsId());
		$this->assertEquals($this->title, $this->obj->getTitle());
	}
}
