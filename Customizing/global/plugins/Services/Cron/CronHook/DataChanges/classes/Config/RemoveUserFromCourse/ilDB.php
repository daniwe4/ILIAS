<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\DataChanges\Config\RemoveUserFromCourse;

use Exception;
use ilDBInterface;

class ilDB implements DB
{
	const TABLE_NAME = 'hhd_usrcrs';

	/**
	 * @var ilDBInterface
	 */
	protected $db;

	public function __construct(ilDBInterface $db)
	{
		$this->db = $db;
	}

	public function deleteUsersFromHistForCrs(int $usr_id, int $crs_id)
	{
		$sql =
			 'DELETE FROM ' . self::TABLE_NAME . PHP_EOL
			.'WHERE usr_id = ' . $this->getDB()->quote($usr_id, 'integer') . PHP_EOL
			.'AND crs_id = ' . $this->getDB()->quote($crs_id, 'integer')
		;

		$this->getDB()->manipulate($sql);
	}

	public function deleteUserFromCourse(int $user_id, int $crs_id)
	{
		$sql =
			 'DELETE FROM obj_members' . PHP_EOL
			.'WHERE usr_id = ' . $this->getDB()->quote($user_id, 'integer') . PHP_EOL
			.'AND obj_id = ' . $this->getDB()->quote($crs_id, 'integer')
		;

		$this->getDB()->manipulate($sql);
	}

	public function hasUserAnnouncedIddTimes(int $user_id, int $crs_id) : bool
	{
		if (! $this->getDB()->tableExists('xwbd_announced_cases')) {
			return false;
		}

		$sql =
			 'SELECT crs_id, usr_id' . PHP_EOL
			.'FROM xwbd_announced_cases' . PHP_EOL
			.'WHERE crs_id = ' . $this->getDB()->quote($crs_id, 'integer') . PHP_EOL
			.'AND usr_id = ' . $this->getDB()->quote($user_id, 'integer') . PHP_EOL
		;

		$result = $this->getDB()->query($sql);

		return $this->getDB()->numRows($result) > 0;
	}

	public function getBookingId(int $user_id, int $crs_id) : string
	{
		$sql =
			 'SELECT wbd_booking_id' . PHP_EOL
			.'FROM ' . self::TABLE_NAME . PHP_EOL
			.'WHERE crs_id = ' . $this->getDB()->quote($crs_id, 'integer') . PHP_EOL
			.'AND usr_id = ' . $this->getDB()->quote($user_id, 'integer') . PHP_EOL
		;

		$result = $this->getDB()->query($sql);
		$row = $this->getDB()->fetchAssoc($result);

		$id = '-';
		if (! is_null($row['wbd_booking_id'])) {
			$id = $row['wbd_booking_id'];
		}

		return $id;
	}

	/**
	 * @inheritDoc
	 */
	public function getBwvId(int $user_id, int $field_id) : string
	{
		$sql =
			 'SELECT value' . PHP_EOL
			.'FROM udf_text' . PHP_EOL
			.'WHERE usr_id = ' . $this->getDB()->quote($user_id, 'integer') . PHP_EOL
			.'AND field_id = ' . $this->getDB()->quote($field_id, 'integer') . PHP_EOL
		;

		$result = $this->getDB()->query($sql);
		$row = $this->getDB()->fetchAssoc($result);

		$id = '-';
		if (! is_null($row['value'])) {
			$id = $row['value'];
		}

		return $id;
	}

	protected function getDB() : ilDBInterface
	{
		if (is_null($this->db)) {
			throw new Exception('no database');
		}
		return $this->db;
	}
}