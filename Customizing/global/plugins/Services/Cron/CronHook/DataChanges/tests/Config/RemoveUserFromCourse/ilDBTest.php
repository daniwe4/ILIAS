<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\DataChanges\Config\RemoveUserFromCourse;

use PHPUnit\Framework\TestCase;

class ilDBTest extends TestCase
{
	/**
	 * @var int
	 */
	protected $user_id;

	/**
	 * @var int
	 */
	protected $crs_id;

	/**
	 * @var int
	 */
	protected $field_id;

	/**
	 * @var string
	 */
	protected $has_user_announced_idd_times_sql;

	/**
	 * @var string
	 */
	protected $get_bwv_id_sql;

	/**
	 * @var string
	 */
	protected $get_booking_id_sql;

	public function setUp()
	{
		$this->user_id = 22;
		$this->crs_id = 44;
		$this->field_id = 66;

		$this->has_user_announced_idd_times_sql =
			'SELECT crs_id, usr_id' . PHP_EOL
			.'FROM xwbd_announced_cases' . PHP_EOL
			.'WHERE crs_id = ' . $this->crs_id . PHP_EOL
			.'AND usr_id = ' . $this->user_id . PHP_EOL
		;

		$this->get_booking_id_sql =
			'SELECT wbd_booking_id' . PHP_EOL
			.'FROM ' . ilDB::TABLE_NAME . PHP_EOL
			.'WHERE crs_id = ' . $this->crs_id . PHP_EOL
			.'AND usr_id = ' . $this->user_id . PHP_EOL
		;

		$this->get_bwv_id_sql =
			'SELECT value' . PHP_EOL
			.'FROM udf_text' . PHP_EOL
			.'WHERE usr_id = ' . $this->user_id . PHP_EOL
			.'AND field_id = ' . $this->field_id . PHP_EOL
		;
	}

	public function test_init()
	{
		$db = $this->getMockBuilder('ilDBInterface')->getMock();
		$db = new ilDB($db);
		$this->assertInstanceOf(ilDB::class, $db);
	}

	public function testHasUserAnnouncedIddTimes_WithNullRows()
	{
		$sql = $this->has_user_announced_idd_times_sql;

		$db = $this
			->getMockBuilder('ilDBInterface')
			->setMethods(['tableExists', 'quote', 'query', 'numRows'])
			->getMock()
		;

		$db
			->expects($this->once())
			->method('tableExists')
			->willReturn(true)
		;

		$db
			->expects($this->exactly(2))
			->method('quote')
			->will($this->onConsecutiveCalls(
				$this->crs_id,
				$this->user_id
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

		$rmufc_db = new ilDB($db);
		$result = $rmufc_db->hasUserAnnouncedIddTimes($this->user_id, $this->crs_id);
		$this->assertFalse($result);
	}

	public function testHasUserAnnouncedIddTimes()
	{
		$sql = $this->has_user_announced_idd_times_sql;

		$db = $this
			->getMockBuilder('ilDBInterface')
			->setMethods(['tableExists', 'quote', 'query', 'numRows'])
			->getMock()
		;

		$db
			->expects($this->once())
			->method('tableExists')
			->willReturn(true)
		;

		$db
			->expects($this->exactly(2))
			->method('quote')
			->will($this->onConsecutiveCalls(
				$this->crs_id,
				$this->user_id
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
			->willReturn(1)
		;

		$rmufc_db = new ilDB($db);
		$result = $rmufc_db->hasUserAnnouncedIddTimes($this->user_id, $this->crs_id);
		$this->assertTrue($result);
	}

	public function testGetBookingId_WithNullValue()
	{
		$sql = $this->get_booking_id_sql;

		$db = $this
			->getMockBuilder('ilDBInterface')
			->setMethods(['quote', 'query', 'fetchAssoc'])
			->getMock()
		;

		$db
			->expects($this->exactly(2))
			->method('quote')
			->will($this->onConsecutiveCalls(
				$this->crs_id,
				$this->user_id
			))
		;

		$db
			->expects($this->once())
			->method('query')
			->with($sql)
		;

		$db
			->expects($this->once())
			->method('fetchAssoc')
			->willReturn(['wbd_booking_id' => null])
		;

		$rmufc_db = new ilDB($db);
		$result = $rmufc_db->getBookingId($this->user_id, $this->crs_id);
		$this->assertEquals('-', $result);
	}

	public function testGetBookingId()
	{
		$sql = $this->get_booking_id_sql;

		$db = $this
			->getMockBuilder('ilDBInterface')
			->setMethods(['quote', 'query', 'fetchAssoc'])
			->getMock()
		;

		$db
			->expects($this->exactly(2))
			->method('quote')
			->will($this->onConsecutiveCalls(
				$this->crs_id,
				$this->user_id
			))
		;

		$db
			->expects($this->once())
			->method('query')
			->with($sql)
		;

		$db
			->expects($this->once())
			->method('fetchAssoc')
			->willReturn([ 'wbd_booking_id' => 'test' ])
		;

		$rmufc_db = new ilDB($db);
		$result = $rmufc_db->getBookingId($this->user_id, $this->crs_id);
		$this->assertEquals('test', $result);
	}

	public function testGetBwvId_WithNullValue()
	{
		$sql = $this->get_bwv_id_sql;

		$db = $this
			->getMockBuilder('ilDBInterface')
			->setMethods(['quote', 'query', 'fetchAssoc'])
			->getMock()
		;

		$db
			->expects($this->exactly(2))
			->method('quote')
			->will($this->onConsecutiveCalls(
				$this->user_id,
				$this->field_id
			))
		;

		$db
			->expects($this->once())
			->method('query')
			->with($sql)
		;

		$db
			->expects($this->once())
			->method('fetchAssoc')
			->willReturn(['value' => null])
		;

		$rmufc_db = new ilDB($db);
		$result = $rmufc_db->getBwvId($this->user_id, $this->field_id);
		$this->assertEquals('-', $result);
	}

	public function testGetBwvId()
	{
		$sql = $this->get_bwv_id_sql;

		$db = $this
			->getMockBuilder('ilDBInterface')
			->setMethods(['quote', 'query', 'fetchAssoc'])
			->getMock()
		;

		$db
			->expects($this->exactly(2))
			->method('quote')
			->will($this->onConsecutiveCalls(
				$this->user_id,
				$this->field_id
			))
		;

		$db
			->expects($this->once())
			->method('query')
			->with($sql)
		;

		$db
			->expects($this->once())
			->method('fetchAssoc')
			->willReturn([ 'value' => 'test' ])
		;

		$rmufc_db = new ilDB($db);
		$result = $rmufc_db->getBwvId($this->user_id, $this->field_id);
		$this->assertEquals('test', $result);
	}
}

