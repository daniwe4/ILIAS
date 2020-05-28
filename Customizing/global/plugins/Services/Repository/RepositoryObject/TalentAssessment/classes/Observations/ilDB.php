<?php

namespace CaT\Plugins\TalentAssessment\Observations;
use CaT\Plugins\CareerGoal\Observations as BaseObservations;

class ilDB implements DB
{
	const TABLE_OBSERVATIONS = "rep_obj_xtas_obs";
	const TABLE_OBSERVATIONS_NOTICE = "rep_obj_xtas_obs_not";
	const TABLE_REQUIREMENTS = "rep_obj_xtas_req";
	const TABLE_REQUIREMENTS_POINTS = "rep_obj_xtas_req_pts";

	public function __construct($db, $user, BaseObservations\DB $base_observations_db)
	{
		$this->db = $db;
		$this->user = $user;
		$this->base_observations_db = $base_observations_db;
	}

	public function install()
	{
		$this->createTables();
	}

	public function updateColumns()
	{
		if ($this->getDB()->tableColumnExists(self::TABLE_OBSERVATIONS_NOTICE, 'notice')) {
			$this->getDB()->modifyTableColumn(self::TABLE_OBSERVATIONS_NOTICE, 'notice', array(
						'type' 		=> 'text',
						'length'	=> 255,
						'notnull' 	=> false
			));
		}
	}

	protected function createTables()
	{
		if (!$this->getDB()->tableExists(self::TABLE_OBSERVATIONS)) {
			$fields =
				array('obj_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'ta_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'title' => array(
						'type' 		=> 'text',
						'length'	=> 255,
						'notnull' 	=> true
					),
					'description' => array(
						'type' 		=> 'clob',
						'notnull' 	=> false
					),
					'position' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'last_change' => array(
						'type' 		=> 'timestamp',
						'notnull' 	=> true
					),
					'last_change_user' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					)
				);

			$this->getDB()->createTable(self::TABLE_OBSERVATIONS, $fields);
			$this->getDB()->addPrimaryKey(self::TABLE_OBSERVATIONS, array("obj_id", "ta_id"));
			$this->getDB()->createSequence(self::TABLE_OBSERVATIONS);
		}

		if (!$this->getDB()->tableExists(self::TABLE_REQUIREMENTS)) {
			$fields =
				array('obj_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'obs_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'title' => array(
						'type' 		=> 'text',
						'length'	=> 255,
						'notnull' 	=> true
					),
					'description' => array(
						'type' 		=> 'clob',
						'notnull' 	=> false
					),
					'position' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'last_change' => array(
						'type' 		=> 'timestamp',
						'notnull' 	=> true
					),
					'last_change_user' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					)
				);

			$this->getDB()->createTable(self::TABLE_REQUIREMENTS, $fields);
			$this->getDB()->addPrimaryKey(self::TABLE_REQUIREMENTS, array("obj_id", "obs_id"));
			$this->getDB()->createSequence(self::TABLE_REQUIREMENTS);
		}

		if (!$this->getDB()->tableExists(self::TABLE_OBSERVATIONS_NOTICE)) {
			$fields =
				array('obs_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'observer_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'notice' => array(
						'type' 		=> 'text',
						'length'	=> 255,
						'notnull' 	=> true
					),
					'last_change' => array(
						'type' 		=> 'timestamp',
						'notnull' 	=> true
					)
				);

			$this->getDB()->createTable(self::TABLE_OBSERVATIONS_NOTICE, $fields);
			$this->getDB()->addPrimaryKey(self::TABLE_OBSERVATIONS_NOTICE, array("obs_id", "observer_id"));
		}

		if (!$this->getDB()->tableExists(self::TABLE_REQUIREMENTS_POINTS)) {
			$fields =
				array('req_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'observer_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'points' => array(
						'type' 		=> 'float',
						'notnull' 	=> true
					),
					'last_change' => array(
						'type' 		=> 'timestamp',
						'notnull' 	=> true
					)
				);

			$this->getDB()->createTable(self::TABLE_REQUIREMENTS_POINTS, $fields);
			$this->getDB()->addPrimaryKey(self::TABLE_REQUIREMENTS_POINTS, array("req_id", "observer_id"));
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getBaseObservations($career_goal_id)
	{
		return $this->base_observations_db->getTAListData($career_goal_id);
	}

	/**
	 * @inheritdoc
	 */
	public function getObservations($obj_id)
	{
		$select = "SELECT A.obj_id, A.title, A.description, A.position\n"
				."    , B.obj_id as req_obj_id, B.title as req_title, B.description as req_description\n"
				."    , C.notice\n"
				."    , D.points\n"
				." FROM ".self::TABLE_OBSERVATIONS." A\n"
				." LEFT JOIN ".self::TABLE_REQUIREMENTS." B\n"
				."     ON A.obj_id = B.obs_id\n"
				." LEFT JOIN ".self::TABLE_OBSERVATIONS_NOTICE." C\n"
				."     ON A.obj_id = C.obs_id\n"
				."         AND C.observer_id = ".$this->getDB()->quote($this->user->getId())."\n"
				." LEFT JOIN ".self::TABLE_REQUIREMENTS_POINTS." D\n"
				."     ON B.obj_id = D.req_id\n"
				."         AND D.observer_id = ".$this->getDB()->quote($this->user->getId())."\n"
				." WHERE A.ta_id = ".$this->getDB()->quote($obj_id, "integer")."\n"
				." ORDER BY A.position, B.position";

		$res = $this->getDB()->query($select);

		$ret = array();
		$pos = null;
		$ret_ar = array();
		while ($row = $this->getDB()->fetchAssoc($res)) {
			if ($pos != $row["position"]) {
				if (!empty($ret_ar)) {
					$ret[] = $ret_ar;
				}

				$ret_ar = array();
				$ret_ar["title"] = $row["title"];
				$ret_ar["description"] = $row["description"];
				$ret_ar["obs_id"] = $row["obj_id"];
				$ret_ar["notice"] = $row["notice"] ? $row["notice"] : "";
				$ret_ar["requirements"] = array();
				$pos = $row["position"];
			}

			$ret_ar["requirements"][] = array("obj_id"=>$row["req_obj_id"], "title"=>$row["req_title"], "description"=>$row["req_description"], "value"=>$row["points"]);
		}

		$ret[] = $ret_ar;

		return $ret;
	}

	/**
	 * @inheritdoc
	 */
	public function copyObservations($ta_obj_id, $career_goal_id)
	{
		$obs = $this->base_observations_db->getDataForCopy($career_goal_id);

		if (empty($obs[0])) {
			return null;
		}

		foreach ($obs as $key => $ob) {
			$obj_id = $this->getObjId(self::TABLE_OBSERVATIONS);
			$values = array("obj_id" => array("integer", $obj_id)
					  , "ta_id" => array("integer", $ta_obj_id)
					  , "title" => array("text", $ob["title"])
					  , "description" => array("text", $ob["description"])
					  , "position" => array("integer", $ob["position"])
					  , "last_change" => array("text", date("Y-m-d H:i:s"))
					  , "last_change_user" => array("integer",$this->user->getId())
			);
			$this->getDB()->insert(self::TABLE_OBSERVATIONS, $values);

			$reqs = $ob["requirements"];
			foreach ($reqs as $key => $req) {
				$req_obj_id = $this->getObjId(self::TABLE_REQUIREMENTS);
				$values = array("obj_id" => array("integer", $req_obj_id)
					  , "obs_id" => array("integer", $obj_id)
					  , "title" => array("text", $req["title"])
					  , "description" => array("text", $req["description"])
					  , "position" => array("integer", $req["position"])
					  , "last_change" => array("text", date("Y-m-d H:i:s"))
					  , "last_change_user" => array("integer",$this->user->getId())
				);
				$this->getDB()->insert(self::TABLE_REQUIREMENTS, $values);
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	public function setNotice($obs_id, $notice)
	{
		$insert = "INSERT INTO ".self::TABLE_OBSERVATIONS_NOTICE."\n"
				."    (obs_id, observer_id, notice, last_change)\n"
				." VALUES \n"
				."    (\n"
				.$this->getDB()->quote($obs_id, "integer")
				.", ".$this->getDB()->quote($this->user->getId(), "integer")
				.", ".$this->getDB()->quote($notice, "text")
				.", NOW())\n"
				." ON DUPLICATE KEY UPDATE notice = ".$this->getDB()->quote($notice, "text").", last_change = NOW()";

		$this->getDB()->manipulate($insert);
	}

	/**
	 * @inheritdoc
	 */
	public function setPoints($req_id, $points)
	{
		$insert = "INSERT INTO ".self::TABLE_REQUIREMENTS_POINTS."\n"
				."    (req_id, observer_id, points, last_change)\n"
				." VALUES \n"
				."    (\n"
				.$this->getDB()->quote($req_id, "integer")
				.", ".$this->getDB()->quote($this->user->getId(), "integer")
				.", ".$this->getDB()->quote($points, "float")
				.", NOW())\n"
				." ON DUPLICATE KEY UPDATE points = ".$this->getDB()->quote($points, "float").", last_change = NOW()";

		$this->getDB()->manipulate($insert);
	}

	/**
	 * @inheritdoc
	 */
	public function getObservationOverviewData($obj_id, $observer)
	{
		$observer_id = array_map(function ($obs) {
			return $obs->getId();
		}, $observer);

		$select = "SELECT A.title, A.position\n"
				."    , B.title as req_title, B.position as req_position\n"
				."    , C.points, C.observer_id\n"
				." FROM ".self::TABLE_OBSERVATIONS." A\n"
				." JOIN ".self::TABLE_REQUIREMENTS." B\n"
				."     ON A.obj_id = B.obs_id\n"
				." LEFT JOIN ".self::TABLE_REQUIREMENTS_POINTS." C\n"
				."     ON B.obj_id = C.req_id\n"
				."         AND ".$this->getDB()->in("C.observer_id", array_values($observer_id), false, "integer")."\n"
				." WHERE A.ta_id = ".$this->getDB()->quote($obj_id, "integer")."\n"
				." ORDER BY A.position, B.position";

		$res = $this->getDB()->query($select);

		$ret = array();
		$pos = null;
		$req_pos = null;
		$ret_ar = array();
		$req = array();
		while ($row = $this->getDB()->fetchAssoc($res)) {
			if ($pos != $row["position"]) {
				if (!empty($ret_ar)) {
					$ret_ar["requirements"][$req_pos] = $req;
					$ret[] = $ret_ar;
				}

				$ret_ar = array();
				$req = array();
				$ret_ar["title"] = $row["title"];
				$ret_ar["requirements"] = array();
				$pos = $row["position"];
			}

			if ($req_pos != $row["req_position"]) {
				if (!empty($req)) {
					$ret_ar["requirements"][$req_pos] = $req;
				}

				$req["title"] = $row["req_title"];
				$req["observer"] = array();
				$req_pos = $row["req_position"];
			}

			if ($row["observer_id"] !== null) {
				$req["observer"][$row["observer_id"]] = $row["points"];
			}
		}

		$ret_ar["requirements"][$req_pos] = $req;
		$ret[] = $ret_ar;

		return $ret;
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

	public function getRequestresultCumulative($obs_ids)
	{
		$select = "SELECT A.obj_id as req_id, A.title, A.obs_id, A.position, B.points, B.observer_id, SUM(B.points)\n"
				 ." FROM ".self::TABLE_REQUIREMENTS." A\n"
				 ." LEFT JOIN ".self::TABLE_REQUIREMENTS_POINTS." B\n"
				 ."    ON A.obj_id = B.req_id\n"
				 ." WHERE ".$this->getDB()->in("A.obs_id", $obs_ids, false, "integer")."\n"
				 ." GROUP BY A.obj_id, A.title, A.obs_id, A.position, B.points, B.observer_id\n"
				 ." ORDER BY A.position, A.obs_id";

		$res = $this->getDB()->query($select);

		$pos = null;
		$obs = null;
		while ($row = $this->getDB()->fetchAssoc($res)) {
			if ($pos != $row["position"]) {
				if (!empty($ret_ar)) {
					$ret_ar["sum"] = $sum;
					$ret_ar["middle"] = $ret_ar["sum"] / $observer_count;
					$ret[$title] = $ret_ar;
					$ret_ar = array();
					$observer_count = 0;
					$sum = 0;
				}

				$pos = $row["position"];
				$title = $row["title"];
			}

			$ret_ar[$row["obs_id"]]["observer"][$row["observer_id"]] = $row["points"];
			$sum += $row["points"];
			$observer_count++;
		}

		$ret_ar["sum"] = $sum;
		$ret_ar["middle"] = $ret_ar["sum"] / $observer_count;
		$ret[$title] = $ret_ar;

		return $ret;
	}

	public function getAssessmentsData($filter_values)
	{
		$to_sql = new \CaT\Filter\SqlPredicateInterpreter($this->getDB());
		$select = $this->getSelect();
		$where = "";
		$having = "";

		$where = " WHERE DATE(ADDDATE(xtas.start_date, INTERVAL 6 MONTH)) >= CURDATE()";

		if (!empty($filter_values[0]) && !empty($filter_values[1])) {
			$where .= " AND (xtas.start_date >= ".$this->getDB()->quote($filter_values[0]->format("Y-m-d")." 00:00:00", "text")."\n"
					."    AND xtas.start_date <= ".$this->getDB()->quote($filter_values[1]->format("Y-m-d")." 23:59:59", "text").")";
		}

		if (!empty($filter_values[2])) {
			$having .= " HAVING ".$this->db->in("result", $filter_values[2], false, "integer");
		}

		if (!empty($filter_values[3])) {
			$where .= " AND ".$this->db->in("xtas.career_goal_id", $filter_values[3], false, "integer");
		}

		if (!empty($filter_values[4])) {
			$where .= " AND ".$this->db->in("xtas.org_unit", $filter_values[4], false, "integer");
		}

		$select = $select.$where.$having;
// die($select);
		$res = $this->getDB()->query($select);
		$data = array();
		while ($row = $this->getDB()->fetchAssoc($res)) {
			$data[] = $row;
		}

		return $data;
	}

	public function getAllObserver()
	{
		$select = "SELECT rua.usr_id FROM rbac_ua rua JOIN object_data od WHERE od.title LIKE '".ilActions::OBSERVER_ROLE_NAME."%'";
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

	protected function getDB()
	{
		if (!$this->db) {
			throw new \Exception("no Database");
		}
		return $this->db;
	}

	protected function getObjId($table)
	{
		return $this->getDB()->nextId($table);
	}

	/**
	 *@inheritdoc
	 */
	public function deleteObservationResults($obj_id, $user_id)
	{
		$delete = "DELETE FROM ".self::TABLE_REQUIREMENTS_POINTS."\n"
				." WHERE req_id IN\n"
				."    (SELECT DISTINCT A.obj_id FROM ".self::TABLE_REQUIREMENTS. " A\n"
				."     JOIN ".self::TABLE_OBSERVATIONS. " B\n"
				."         ON A.obs_id = B.obj_id\n"
				."     WHERE B.ta_id = ".$this->getDB()->quote($obj_id, "integer")."\n"
				."    )\n"
				." AND observer_id = ".$this->getDB()->quote($user_id, "integer")."\n";

		$this->getDB()->manipulate($delete);
	}

	public function deleteByTalentAssessmentId($ta_id)
	{
		$select = "SELECT obj_id FROM ".self::TABLE_OBSERVATIONS." WHERE ta_id = ".$this->getDB()->quote($ta_id, "integer")."\n";

		$res = $this->getDB()->query($select);
		while ($row = $this->getDB()->fetchAssoc($res)) {
			$sel = "SELECT obj_id FROM ".self::TABLE_REQUIREMENTS." WHERE obs_id = ".$this->getDB()->quote($row["obj_id"], "integer")."\n";
			$res2 = $this->getDB()->query($sel);

			while ($row2 = $this->getDB()->fetchAssoc($res2)) {
				$del = "DELETE FROM ".self::TABLE_REQUIREMENTS_POINTS." WHERE req_id = ".$this->getDB()->quote($row2["obj_id"], "integer")."\n";
				$this->getDB()->manipulate($del);
			}

			$del = "DELETE FROM ".self::TABLE_REQUIREMENTS." WHERE obs_id = ".$this->getDB()->quote($row["obj_id"], "integer")."\n";
			$this->getDB()->manipulate($del);
		}

		$del = "DELETE FROM ".self::TABLE_OBSERVATIONS." WHERE ta_id = ".$this->getDB()->quote($ta_id, "integer")."\n";
		$this->getDB()->manipulate($del);
	}

	public function getNumberOfVotes($obj_id, $usr_id)
	{
		$query = "SELECT count(C.observer_id) as votes".PHP_EOL
			." FROM ".self::TABLE_OBSERVATIONS."  A".PHP_EOL
			." JOIN ". self::TABLE_REQUIREMENTS." B ON A.obj_id = B.obs_id".PHP_EOL
			." JOIN ".self::TABLE_REQUIREMENTS_POINTS." C ON B.obj_id = C.req_id".PHP_EOL
			." WHERE A.ta_id = ".$this->getDB()->quote($obj_id, "integer").PHP_EOL
			."     AND C.observer_id = ".$this->getDB()->quote($usr_id, "integer")
		;

		$res = $this->getDB()->query($query);
		$row = $this->getDB()->fetchAssoc($res);

		return $row["votes"];
	}
}
