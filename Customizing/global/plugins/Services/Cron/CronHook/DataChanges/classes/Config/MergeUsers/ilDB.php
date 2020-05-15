<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\DataChanges\Config\MergeUsers;

use Exception;
use ilDBInterface;

class ilDB implements DB
{
	const TABLE_NAME = 'hhd_usrcrs';
	const TABLE_CERT_NAME = 'il_cert_user_cert';

	/**
	 * @var ilDBInterface
	 */
	protected $db;

	public function __construct(ilDBInterface $db)
	{
		$this->db = $db;
	}

	/**
	 * @inheritDoc
	 */
	public function selectAllParticipatedCoursesFor(int $user_id) : array
	{
		$sql =
			 'SELECT crs_id' . PHP_EOL
			.'FROM ' . self::TABLE_NAME . PHP_EOL
			.'WHERE usr_id = ' . $this->db->quote($user_id, 'integer') . PHP_EOL
			.'AND booking_status LIKE \'participant\'' . PHP_EOL
			.'AND participation_status IN (\'successful\', \'absent\')' . PHP_EOL
		;

		$result = $this->db->query($sql);

		if ($this->db->numRows($result) === 0) {
			return [];
		}

		return $this->getHistUserDataObjects($result);
	}

	public function getOpenCourses(int $user_id) : array
	{
		$sql =
			 'SELECT ref_id' . PHP_EOL
			.'FROM ' . self::TABLE_NAME . PHP_EOL
			.'JOIN object_reference ON crs_id = obj_id' . PHP_EOL
			.'WHERE usr_id = ' . $this->db->quote($user_id, 'integer') . PHP_EOL
			.'AND booking_status LIKE \'participant\'' . PHP_EOL
			.'AND (participation_status IN (\'none\', \'in_progress\') OR participation_status IS NULL)' . PHP_EOL
		;

		$result = $this->db->query($sql);

		$crs_ref_ids = [];
		while ($row = $this->db->fetchAssoc($result)) {
			$crs_ref_ids[] = $row['ref_id'];
		}

		return $crs_ref_ids;
	}

	public function getSameBookedCourses(int $to_deactivate_id, int $active_id) : array
	{
		$sql =
			 'SELECT ref_id, count(crs_id) AS cnt' . PHP_EOL
			.'FROM ' . self::TABLE_NAME . PHP_EOL
			.'JOIN object_reference ON crs_id = obj_id' . PHP_EOL
			.'WHERE ' . $this->db->in(
				'usr_id',
				[$to_deactivate_id, $active_id],
				false,
				'integer'
			 ) . PHP_EOL
			.'AND (booking_status LIKE \'participant\' OR booking_status IS NULL)' . PHP_EOL
			.'GROUP BY ref_id'
		;

		$result = $this->db->query($sql);

		$crs_ref_ids = [];
		while ($row = $this->db->fetchAssoc($result)) {
			if ($row['cnt'] > 1) {
				$crs_ref_ids[] = $row['ref_id'];
			}
		}

		return $crs_ref_ids;
	}

	protected function getHistUserDataObjects($results) : array
	{
		$data = [];
		while ($row = $this->db->fetchAssoc($results)) {
			$data[] = (int)$row['crs_id'];
		}

		return $data;
	}

	/**
	 * @inheritDoc
	 */
	public function mergeUserData(int $user_to_deactivate_id, int $user_to_activate_id) : bool
	{
		$deactivate_hist_data = $this->selectAllParticipatedCoursesFor($user_to_deactivate_id);

		if (count($deactivate_hist_data) == 0) {
			return false;
		}

		$mapping_array = $this->copyAffectedCourses($deactivate_hist_data);
		$this->copyAffectedUsrCourseEntries(
			$mapping_array,
			$user_to_deactivate_id,
			$user_to_activate_id
		);

		$this->deleteCurrentHistoryEntries(
			$deactivate_hist_data,
			$user_to_deactivate_id,
			$user_to_activate_id
		);

		foreach ($mapping_array as $entry) {
			$this->updateCert(
				$entry['old_id'],
				$entry['new_id'],
				$user_to_deactivate_id,
				$user_to_activate_id
			);
		}

		return true;
	}

	protected function copyAffectedCourses(array $affected_courses) {
		$ret = [];
		foreach ($affected_courses as $key => $affected_course) {
			$next_negative_id = $this->getNextNegativeId();
			$ret[] = [
				'old_id' => $affected_course,
				'new_id' => $next_negative_id
			];
			$q = "INSERT INTO hhd_crs"
				." SELECT ".$next_negative_id.", title, crs_type, deleted, venue, accomodation,"
				." provider, begin_date, end_date, created_ts, creator, edu_programme,"
				." idd_learning_time, is_template, booking_dl_date, storno_dl_date,"
				." booking_dl,storno_dl,max_members,min_members,net_total_cost,"
				." gross_total_cost,costcenter_finalized, participation_finalized_date,"
				." accomodation_date_start, accomodation_date_end, fee,to_be_acknowledged,"
				." venue_freetext, provider_freetext,gti_learning_time,"
				." max_cancellation_fee,gti_category, topics, tut,"
				." categories, target_groups, venue_from_course"
				." FROM hhd_crs"
				." WHERE crs_id = ".$this->db->quote($affected_course, "integer")
			;

			$this->db->manipulate($q);
		}
		return $ret;
	}

	protected function copyAffectedUsrCourseEntries(
		array $mappings,
		int $user_to_deactivate_id,
		int $user_to_activate_id
	) {
		foreach ($mappings as $mapping) {
			$old_id = $mapping["old_id"];
			$new_id = $mapping["new_id"];
			$q = "INSERT INTO hhd_usrcrs"
				." SELECT ".$new_id.", ".$user_to_activate_id.", booking_status,"
				." participation_status, created_ts, creator, booking_date, ps_acquired_date,"
				." idd_learning_time, prior_night, following_night, nights,"
				." cancel_booking_date, waiting_date, cancel_waiting_date, custom_p_status,"
				." wbd_booking_id, cancellation_fee, roles"
				." FROM hhd_usrcrs"
				." WHERE crs_id = ".$this->db->quote($old_id, "integer")
				."     AND usr_id = ".$this->db->quote($user_to_deactivate_id, "integer")
			;

			$this->db->manipulate($q);
		}
	}

	protected function getNextNegativeId() : int
	{
		$query = "SELECT MIN(crs_id) AS current FROM hhd_crs WHERE crs_id < 0";
		$res = $this->db->query($query);
		$row = $this->db->fetchAssoc($res);
		return (int)$row["current"] - 1;
	}

	protected function deleteCurrentHistoryEntries(
		array $crs_ids,
		int $user_to_deactivate_id,
		int $user_to_activate_id
	) {
		$sql = 'DELETE FROM ' . self::TABLE_NAME . PHP_EOL
			.' WHERE '.$this->db->in('crs_id', $crs_ids, false, "integer")
			.' AND '.$this->db->in('usr_id', [$user_to_deactivate_id, $user_to_activate_id], false, "integer")
		;

		$this->db->manipulate($sql);
	}

	protected function updateCert(
		int $old_id,
		int $new_id,
		int $user_to_deactivate_id,
		int $user_to_activate_id
	) {
		$where = [
			'user_id' => ['integer', $user_to_deactivate_id],
			'obj_id' => ['integer', $old_id]
		];

		$values = [
			'user_id' => ['integer', $user_to_activate_id],
			'obj_id' => ['integer', $new_id]
		];

		$this->db->update(self::TABLE_CERT_NAME, $values, $where);
	}
}
