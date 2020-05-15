<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\DataChanges\Config\RemoveCourseFromHistory;

use DateTime;
use Exception;
use ilDBInterface;
use ilPDOStatement;

class ilDB implements DB
{
	const TABLE_NAME_CRS = 'hhd_crs';
	const TABLE_NAME_USRCRS = 'hhd_usrcrs';

	/**
	 * @var ilDBInterface
	 */
	protected $db;

	public function __construct(ilDBInterface $db)
	{
		$this->db = $db;
	}

	public function getDeletedHistCourses(string $title, DateTime $start) : array
	{
		$start = $start->format('Y-m-d');
		$sql =
			 'SELECT crs_id, title' . PHP_EOL
			.'FROM ' . self::TABLE_NAME_CRS . PHP_EOL
			.'WHERE deleted = 1' . PHP_EOL
			.'AND title = ' . $this->db->quote($title, 'text') . PHP_EOL
			.'AND begin_date = ' . $this->db->quote($start, 'text') . PHP_EOL
			.'AND crs_id > 0' . PHP_EOL
		;
		$result = $this->db->query($sql);

		if ($this->db->numRows($result) == 0) {
			return [];
		}

		return $this->getDeletedHistCourseObjects($result);
	}

	protected function getDeletedHistCourseObjects($result)
	{
		$data = [];
		while ($row = $this->db->fetchAssoc($result)) {
			$data[] = new DeletedHistCourse(
				(int)$row['crs_id'],
				$row['title']
			);
		}

		return $data;
	}

	public function deleteCourseFromHist(DeletedHistCourse $course, int $user_id = null)
	{
		$where =' ';
		if (! is_null($user_id)) {
			$where = ' AND usr_id = ' . $this->db->quote($user_id, 'integer');
		}

		$sql =
			 'DELETE FROM ' . self::TABLE_NAME_USRCRS . PHP_EOL
			.' WHERE crs_id = ' .$this->db->quote($course->getCrsId(), 'integer') .PHP_EOL
			.$where
		;
		return $this->db->manipulate($sql);
	}
}
