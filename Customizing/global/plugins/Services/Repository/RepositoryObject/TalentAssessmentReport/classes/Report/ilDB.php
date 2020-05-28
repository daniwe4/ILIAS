<?php

namespace CaT\Plugins\TalentAssessmentReport\Report;

class ilDB implements DB
{
	const TABLE_OBSERVATIONS = "rep_obj_xtas_obs";
	const OBSERVER_ROLE_NAME = "Observer";

	/**
	 * @var \ilDBInterface
	 */
	protected $db;

	public function __construct(\ilDBInterface $db)
	{
		$this->db = $db;
	}

	public function getAssessmentsData(array $filter_values)
	{
		$select = $this->getSelect();
		$where = "";
		$having = "";

		$where = " WHERE DATE(ADDDATE(xtas.start_date, INTERVAL 6 MONTH)) >= CURDATE()";

		if ($filter_values["start_date"]["from"] !== null && $filter_values["start_date"]["to"] !== null) {
			$where .= " AND (xtas.start_date >= ".$this->getDB()->quote($filter_values["start_date"]["from"]->get(IL_CAL_DATE)." 00:00:00", "text")."\n"
					."    AND xtas.start_date <= ".$this->getDB()->quote($filter_values["start_date"]["to"]->get(IL_CAL_DATE)." 23:59:59", "text").")";
		}

		if (count($filter_values["result"]) > 0) {
			$having .= " HAVING ".$this->db->in("result", $filter_values["result"], false, "integer");
		}

		if (count($filter_values["career_goal"]) > 0) {
			$where .= " AND ".$this->db->in("xtas.career_goal_id", $filter_values["career_goal"], false, "integer");
		}

		if (count($filter_values["org_unit"]) > 0) {
			$where .= " AND ".$this->db->in("xtas.org_unit", $filter_values["org_unit"], false, "integer");
		}

		$select = $select.$where.$having;

		$res = $this->getDB()->query($select);
		$data = array();
		while ($row = $this->getDB()->fetchAssoc($res)) {
			$data[] = $row;
		}

		return $data;
	}

	protected function getSelect()
	{
		return "SELECT xtas.obj_id, od.title, xtas.org_unit, xtas.venue, xtas.start_date, xtas.end_date, ud.firstname, ud.lastname, oref.ref_id\n"
			  ." , IF(xtas.potential <= 0, 1, IF(xtas.potential > xtas.should_specification, 2 , IF(xtas.potential <= xtas.lowmark, 4,3))) as result\n"
			  ." FROM rep_obj_xtas xtas\n"
			  ." JOIN usr_data ud ON xtas.username = ud.login\n"
			  ." JOIN rep_obj_xcgo xcgo ON xtas.career_goal_id = xcgo.obj_id\n"
			  ." JOIN object_data od ON xcgo.obj_id = od.obj_id\n"
			  ." JOIN object_reference oref ON oref.obj_id = xtas.obj_id";
	}

	public function getObservationsCumulative($obj_id)
	{
		$select = "SELECT obj_id, title\n"
				." FROM ".self::TABLE_OBSERVATIONS."\n"
				." WHERE ta_id = ".$this->getDB()->quote($obj_id, "integer")."\n"
				." ORDER BY position";

		$res = $this->getDB()->query($select);
		$ret = array();

		while ($row = $this->getDB()->fetchAssoc($res)) {
			$ret[$row["obj_id"]] = $row["title"];
		}

		return $ret;
	}

	protected function getDB()
	{
		if (!$this->db) {
			throw new \Exception("no Database");
		}
		return $this->db;
	}

	public function getCareerGoalsOptions()
	{
		$ret = array();

		$select = "SELECT obj.obj_id, obj.title\n"
				." FROM object_data obj\n"
				." JOIN object_reference ref\n"
				."   ON obj.obj_id = ref.obj_id\n"
				." WHERE obj.type = 'xcgo'\n"
				."   AND ref.deleted IS NULL";

		$res = $this->getDB()->query($select);

		while ($row = $this->getDB()->fetchAssoc($res)) {
			$ret[(int)$row["obj_id"]] = $row["title"];
		}

		return $ret;
	}

	public function getAllObserver()
	{
		$select = "SELECT usr_id, CONCAT(firstname, ' ', lastname) as name\n"
				 ." FROM usr_data\n"
				 ." WHERE usr_id IN\n"
				 ."     (SELECT DISTINCT usr_id\n"
				 ."      FROM rbac_ua rua\n"
				 ."      JOIN object_data od\n"
				 ."          ON rua.rol_id = od.obj_id\n"
				 ."      WHERE od.title LIKE ".$this->db->quote(self::OBSERVER_ROLE_NAME, "text").")";

		$res = $this->db->query($select);
		$ret = array();
		while ($row = $this->db->fetchAssoc($res)) {
			$ret[(int)$row["usr_id"]] = $row["name"];
		}

		return $ret;
	}
}
