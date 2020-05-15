<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\DataChanges\Config\RemoveCourseFromHistory;

use PHPUnit\Framework\TestCase;

class ilDBTest extends TestCase
{
	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var \DateTime
	 */
	protected $date;

	/**
	 * @var string
	 */
	protected $get_deleted_hist_courses_sql;

	public function setUp()
	{
		$this->title = 'test_title';
		$this->date = new \DateTime('2012-08-11');

		$date = $this->date->format('Y-m-d H:i:s');
		$this->get_deleted_hist_courses_sql =
			 'SELECT crs_id, title' . PHP_EOL
			.'FROM hhd_crs' . PHP_EOL
			.'WHERE deleted = 1' . PHP_EOL
			.'AND title = ' . $this->title . PHP_EOL
			.'AND begin_date = ' . $date . PHP_EOL
			.'AND crs_id > 0' . PHP_EOL
		;

	}	

	public function test_init()
	{
		$db = $this->getMockBuilder('ilDBInterface')->getMock();
		$db = new ilDB($db);
		$this->assertInstanceOf(ilDB::class, $db);
	}

	public function testGetDeletedHistCourse_WithNullRows()
	{
		$sql = $this->get_deleted_hist_courses_sql;

		$db = $this
			->getMockBuilder('ilDBInterface')
			->setMethods(['quote', 'query', 'numRows'])
			->getMock()
		;

		$db
			->expects($this->exactly(2))
			->method('quote')
			->will($this->onConsecutiveCalls(
				$this->title,
				$this->date->format('Y-m-d H:i:s')
			))
		;

		$db
			->expects($this->once())
			->method('query')
			->with($sql)
		;

		$db
			->expects($this->once())
			->method('numRows')
			->willReturn(0)
		;

		$rmcfh_db = new ilDB($db);
		$result = $rmcfh_db->getDeletedHistCourses(
			$this->title,
			$this->date
		);
		$this->assertEmpty($result);
	}


	public function testGetDeletedHistCourse()
	{
		$sql = $this->get_deleted_hist_courses_sql;

		$db = $this
			->getMockBuilder('ilDBInterface')
			->setMethods(['quote', 'query', 'numRows', 'fetchAssoc'])
			->getMock()
		;

		$db
			->expects($this->exactly(2))
			->method('quote')
			->will($this->onConsecutiveCalls(
				$this->title,
				$this->date->format('Y-m-d H:i:s')
			))
		;

		$db
			->expects($this->once())
			->method('query')
			->with($sql)
			->willReturn(
				[[
					'crs_id' => 22,
					'title' => $this->title
				]]
			)
		;

		$db
			->expects($this->once())
			->method('numRows')
			->willReturn(1)
		;

		$db
			->expects($this->exactly(2))
			->method('fetchAssoc')
			->will($this->onConsecutiveCalls(
				[
					'crs_id' => 22,
					'title' => $this->title
				],
				false
			))
		;

		$rmcfh_db = new ilDB($db);
		$result = $rmcfh_db->getDeletedHistCourses(
			$this->title,
			$this->date
		);
		$this->assertEquals(22, $result[0]->getCrsId());
		$this->assertEquals($this->title, $result[0]->getTitle());
	}
}

