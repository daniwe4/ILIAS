<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\DataChanges\Config\MergeUsers;

use PHPUnit\Framework\TestCase;

class ilDBTest extends TestCase
{
	/**
	 * @var string
	 */
	protected $select_all_courses_for_sql;

	/**
	 * @var string
	 */
	protected $select_all_participants_courses_for_sql;

	/**
	 * @var string
	 */
	protected $has_open_courses_sql;

	public function setUp()
	{
		$this->select_all_courses_for_sql =
			 'SELECT crs_id, usr_id, booking_status, participation_status' . PHP_EOL
			.'FROM hhd_usrcrs' . PHP_EOL
			.'WHERE usr_id = 0' . PHP_EOL
		;

		$this->select_all_participants_courses_for_sql =
			 'SELECT crs_id, usr_id, booking_status, participation_status' . PHP_EOL
			.'FROM hhd_usrcrs' . PHP_EOL
			.'WHERE usr_id = 0' . PHP_EOL
			.'AND booking_status LIKE \'participant\'' . PHP_EOL
			.'AND participation_status IN (\'successful\', \'absent\')' . PHP_EOL
		;

		$this->get_open_courses_sql =
			 'SELECT ref_id' . PHP_EOL
			.'FROM hhd_usrcrs' . PHP_EOL
			.'JOIN object_reference ON crs_id = obj_id' . PHP_EOL
			.'WHERE usr_id = 0' . PHP_EOL
			.'AND booking_status LIKE \'participant\'' . PHP_EOL
			.'AND participation_status IN (\'none\', \'null\', \'in_progress\')' . PHP_EOL
		;

		$this->get_same_booked_courses_sql =
			 'SELECT ref_id, count(crs_id) AS cnt' . PHP_EOL
			.'FROM hhd_usrcrs' . PHP_EOL
			.'JOIN object_reference ON crs_id = obj_id' . PHP_EOL
			.'WHERE usr_id IN (0, 1)' . PHP_EOL
			.'AND booking_status LIKE \'participant\'' . PHP_EOL
			.'GROUP BY ref_id' . PHP_EOL
		;
	}

	public function test_init()
	{
		$db = $this->getMockBuilder('ilDBInterface')->getMock();
		$db = new ilDB($db);
		$this->assertInstanceOf(ilDB::class, $db);
	}

	public function test_selectAllParticipatedCoursesFor_WithNullRows()
	{
		$sql = $this->select_all_participants_courses_for_sql;

		$db = $this
			->getMockBuilder('ilDBInterface')
			->setMethods(['quote', 'query', 'numRows'])
			->getMock()
		;

		$db
			->expects($this->once())
			->method('quote')
			->willReturn(0)
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

		$merge_db = new ilDB($db);
		$result = $merge_db->selectAllParticipatedCoursesFor(0);
		$this->assertEquals(count($result), 0);
	}

	public function test_selectAllParticipatedCoursesFor_WithOneRow()
	{
		$sql = $this->select_all_participants_courses_for_sql;

		$db = $this
			->getMockBuilder('ilDBInterface')
			->setMethods(['quote', 'query', 'numRows', 'fetchAssoc'])
			->getMock()
		;

		$db
			->expects($this->once())
			->method('quote')
			->willReturn(0)
		;

		$db
			->expects($this->once())
			->method('query')
			->with($sql)
			->willReturn(
				[[
					'crs_id' => 22,
					'usr_id' => 6
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
					'usr_id' => 6
				],
				false
			))
		;

		$merge_db = new ilDB($db);
		$result = $merge_db->selectAllParticipatedCoursesFor(6);

		$this->assertEquals(22, $result[22]->getCrsId());
		$this->assertEquals(6, $result[22]->getUserId());
	}

	public function test_getOpenCourses_WithNullRows()
	{
		$sql = $this->get_open_courses_sql;

		$db = $this
			->getMockBuilder('ilDBInterface')
			->setMethods(['quote', 'query', 'fetchAssoc'])
			->getMock()
		;

		$db
			->expects($this->once())
			->method('quote')
			->willReturn(0)
		;

		$db
			->expects($this->once())
			->method('query')
			->with($sql)
		;

		$db
			->expects($this->once())
			->method('fetchAssoc')
			->willReturn(0)
		;

		$merge_db = new ilDB($db);
		$result = $merge_db->getOpenCourses(0);
		$this->assertEmpty($result);
	}

	public function test_getOpenCourses_WithOneRow()
	{
		$sql = $this->get_open_courses_sql;
		$result = [['ref_id' => 2]];

		$db = $this
			->getMockBuilder('ilDBInterface')
			->setMethods(['quote', 'query', 'fetchAssoc'])
			->getMock()
		;

		$db
			->expects($this->once())
			->method('quote')
			->willReturn(0)
		;

		$db
			->expects($this->once())
			->method('query')
			->with($sql)
			->willReturn($result)
		;

		$db
			->expects($this->atLeastOnce())
			->method('fetchAssoc')
			->with($result)
			->will($this->onConsecutiveCalls(['ref_id' => 2], null))
		;

		$merge_db = new ilDB($db);
		$result = $merge_db->getOpenCourses(0);
		$this->assertEquals($result, [2]);
	}
}
