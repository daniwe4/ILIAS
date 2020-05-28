<?php

declare(strict_types=1);

namespace CaT\Plugins\TalentAssessment\Settings;

use CaT\Plugins\CareerGoal\Settings as CareerGoal;

class ilDB implements DB
{
	const PLUGIN_TABLE = "rep_obj_xtas";
	const USR_TABLE = "usr_data";
	const CAREER_GOAL_TABLE = "rep_obj_xcgo";

	public function __construct($db, $user, CareerGoal\ilDB $career_goal_db)
	{
		$this->db = $db;
		$this->user = $user;
		$this->career_goal_db = $career_goal_db;
	}

	public function install()
	{
		$this->createTable();
		$this->addColumns();
	}

	protected function createTable()
	{
		if (!$this->getDB()->tableExists(self::PLUGIN_TABLE)) {
			$fields =
				array('obj_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'state' => array(
						'type' 		=> 'float',
						'notnull' 	=> true
					),
					'career_goal_id' => array(
						'type' 		=> 'integer',
						'length'	=> 4,
						'notnull' 	=> true
					),
					'username' => array(
						'type' 		=> 'text',
						'length'	=> 80,
						'notnull' 	=> true
					),
					'start_date' => array(
						'type' 		=> 'timestamp',
						'notnull' 	=> true
					),
					'end_date' => array(
						'type' 		=> 'timestamp',
						'notnull' 	=> true
					),
					'venue' => array(
						'type' 		=> 'text',
						'length'	=> 150,
						'notnull' 	=> false
					),
					'org_unit' => array(
						'type' 		=> 'integer',
						'length'	=> 4,
						'notnull' 	=> false
					),
					'started' => array(
						'type' 		=> 'integer',
						'length'	=> 4,
						'notnull' 	=> false
					),
					'lowmark' => array(
						'type' 		=> 'float',
						'notnull' 	=> false
					),
					'should_specification' => array(
						'type' 		=> 'float',
						'notnull' 	=> false
					),
					'potential' => array(
						'type' 		=> 'float',
						'notnull' 	=> false
					),
					'result_comment' => array(
						'type' 		=> 'clob',
						'notnull' 	=> false
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

			$this->getDB()->createTable(self::PLUGIN_TABLE, $fields);
			$this->getDB()->addPrimaryKey(self::PLUGIN_TABLE, array("obj_id"));
		}
	}

	protected function addColumns()
	{
		if (!$this->getDB()->tableColumnExists(self::PLUGIN_TABLE, "default_text_failed")) {
			$this->getDB()->addTableColumn(self::PLUGIN_TABLE, "default_text_failed", array(
				'type' => 'clob',
				'notnull' => false
			));
		}

		if (!$this->getDB()->tableColumnExists(self::PLUGIN_TABLE, "default_text_partial")) {
			$this->getDB()->addTableColumn(self::PLUGIN_TABLE, "default_text_partial", array(
			'type' => 'clob',
			'notnull' => false
			));
		}

		if (!$this->getDB()->tableColumnExists(self::PLUGIN_TABLE, "default_text_success")) {
			$this->getDB()->addTableColumn(self::PLUGIN_TABLE, "default_text_success", array(
			'type' => 'clob',
			'notnull' => false
			));
		}
	}

	public function create(
		int $obj_id,
		int $state,
		int $career_goal_id,
		string $username,
		string $firstname,
		string $lastname,
		string $email,
		\ilDateTime $start_date = null,
		\ilDateTime $end_date = null,
		string $venue = null,
		string $org_unit = null,
		bool $started,
		float $lowmark,
		float $should_specification,
		float $potential,
		string $result_comment,
		string $default_text_failed,
		string $default_text_partial,
		string $default_text_success
	): TalentAssessment {
		$talent_assessment = new TalentAssessment(
			$obj_id,
			$state,
			$career_goal_id,
			$username,
			$firstname,
			$lastname,
			$email,
			$start_date,
			$end_date,
			$venue,
			$org_unit,
			$started,
			$lowmark,
			$should_specification,
			$potential,
			$result_comment,
			$default_text_failed,
			$default_text_partial,
			$default_text_success
		);

		$values = array(
			"obj_id" => array("integer", $talent_assessment->getObjId()),
			"state" => array("integer", $talent_assessment->getState()),
			"career_goal_id" => array("integer", $talent_assessment->getCareerGoalId()),
			"username" => array("text", $talent_assessment->getUsername()),
			"start_date" => array("text", $talent_assessment->getStartDate()->get(IL_CAL_DATETIME)),
			"end_date" => array("text", $talent_assessment->getEndDate()->get(IL_CAL_DATETIME)),
			"venue" => array("text", $talent_assessment->getVenue()),
			"org_unit" => array("text", $talent_assessment->getOrgUnit()),
			"started" => array("integer", $talent_assessment->getStarted()),
			"lowmark" => array("float", $talent_assessment->getLowmark()),
			"should_specification" => array("float", $talent_assessment->getShouldspecification()),
			"potential" => array("float", $talent_assessment->getPotential()),
			"result_comment" => array("text", $talent_assessment->getResultComment()),
			"last_change" => array("text", date("Y-m-d H:i:s")),
			"last_change_user" => array("integer", $this->user->getId()),
			"default_text_failed" => array("text", $talent_assessment->getDefaultTextFailed()),
			"default_text_partial" => array("text", $talent_assessment->getDefaultTextPartial()),
			"default_text_success" => array("text", $talent_assessment->getDefaultTextSuccess())
		);

		$this->getDB()->insert(self::PLUGIN_TABLE, $values);

		return $talent_assessment;
	}

	public function update(TalentAssessment $talent_assessment)
	{
		$values = array
				( "state" => array("integer", $talent_assessment->getState())
				, "career_goal_id" => array("integer", $talent_assessment->getCareerGoalId())
				, "username" => array("text", $talent_assessment->getUsername())
				, "start_date" => array("text", $talent_assessment->getStartDate()->get(IL_CAL_DATETIME))
				, "end_date" => array("text", $talent_assessment->getEndDate()->get(IL_CAL_DATETIME))
				, "venue" => array("text", $talent_assessment->getVenue())
				, "org_unit" => array("text", $talent_assessment->getOrgUnit())
				, "started" => array("integer", $talent_assessment->getStarted())
				, "lowmark" => array("float", $talent_assessment->getLowmark())
				, "should_specification" => array("float", $talent_assessment->getShouldspecification())
				, "potential" => array("float", $talent_assessment->getPotential())
				, "result_comment" => array("text", $talent_assessment->getResultComment())
				, "last_change" => array("text", date("Y-m-d H:i:s"))
				, "last_change_user" => array("integer", $this->user->getId())
				, "default_text_failed" => array("text", $talent_assessment->getDefaultTextFailed())
				, "default_text_partial" => array("text", $talent_assessment->getDefaultTextPartial())
				, "default_text_success" => array("text", $talent_assessment->getDefaultTextSuccess())
				);

		$where = array
				( "obj_id" => array("integer", $talent_assessment->getObjId())
				);

		$this->getDB()->update(self::PLUGIN_TABLE, $values, $where);
	}

	public function delete(int $obj_id)
	{
		$delete =
			 "DELETE FROM ".self::PLUGIN_TABLE."\n"
			." WHERE obj_id = ".$this->getDB()->quote($obj_id, "integer")
		;

		$this->getDB()->manipulate($delete);
	}

	public function select(int $obj_id): TalentAssessment
	{
		$select =
			 "SELECT A.state, A.career_goal_id, A.username, A.start_date, A.end_date, A.venue, A.org_unit\n"
			.", A.started, A.lowmark, A.should_specification, A.potential, A.result_comment\n"
			.", A.default_text_failed, A.default_text_partial, A.default_text_success\n"
			.", B.firstname, B.lastname, B.email\n"
			." FROM ".self::PLUGIN_TABLE." A\n"
			." LEFT JOIN ".self::USR_TABLE." B\n"
			."     ON A.username = B.login"
			." WHERE A.obj_id = ".$this->getDB()->quote($obj_id, "integer")
		;

		$res = $this->getDB()->query($select);
		$row = $this->getDB()->fetchAssoc($res);

		if (empty($row)) {
			throw new \InvalidArgumentException("Invalid id '$obj_id' for TalentAssessment-object");
		}

		$start_date = new \iLDateTime($row["start_date"], IL_CAL_DATETIME);
		$end_date = new \iLDateTime($row["end_date"], IL_CAL_DATETIME);

		$talent_assessment = new TalentAssessment(
			(int)$obj_id,
			(int)$row["state"],
			(int)$row["career_goal_id"],
			$row["username"],
			$row["firstname"] ?? "",
			$row["lastname"] ?? "",
			$row["email"] ?? "",
			$start_date,
			$end_date,
			$row["venue"],
			$row["org_unit"],
			(bool)$row["started"],
			(float)$row["lowmark"],
			(float)$row["should_specification"],
			(float)$row["potential"],
			$row["result_comment"] ?? "",
			$row["default_text_failed"] ?? "",
			$row["default_text_partial"] ?? "",
			$row["default_text_success"] ?? ""
		);

		return $talent_assessment;
	}

	public function cloneTalentAssessment(int $target_id, TalentAssessment $talent_assessment): TalentAssessment
	{
		$start = $talent_assessment->getStartDate()->get(IL_CAL_DATETIME);
		if ($start === null) {
			$start = "";
		}

		$end = $talent_assessment->getEndDate()->get(IL_CAL_DATETIME);
		if ($end === null) {
			$end = "";
		}

		$values = array(
			"obj_id" => array("integer", (int)$target_id),
			"state" => array("integer", TalentAssessment::IN_PROGRESS),
			"career_goal_id" => array("integer", $talent_assessment->getCareerGoalId()),
			"username" => array("text", $talent_assessment->getUsername()),
			"start_date" => array("text", $start),
			"end_date" => array("text", $end),
			"venue" => array("text", $talent_assessment->getVenue()),
			"org_unit" => array("text", $talent_assessment->getOrgUnit()),
			"started" => array("integer", false),
			"lowmark" => array("float", $talent_assessment->getLowmark()),
			"should_specification" => array("float", $talent_assessment->getShouldspecification()),
			"potential" => array("float", 0.0),
			"result_comment" => array("text", ""),
			"last_change" => array("text", date("Y-m-d H:i:s")),
			"last_change_user" => array("integer", $this->user->getId()),
			"default_text_failed" => array("text", $talent_assessment->getDefaultTextFailed()),
			"default_text_partial" => array("text", $talent_assessment->getDefaultTextPartial()),
			"default_text_success" => array("text", $talent_assessment->getDefaultTextSuccess())
		);

		$this->getDB()->insert(self::PLUGIN_TABLE, $values);

		$new_talent_assessment = $this->select((int)$target_id);

		return $new_talent_assessment;
	}

	public function isStarted($obj_id): bool
	{
		$select =
			 "SELECT started\n"
			." FROM ".self::PLUGIN_TABLE."\n"
			." WHERE obj_id = ".$this->getDB()->quote($obj_id, "integer");

		$res = $this->getDB()->query($select);
		$row = $this->getDB()->fetchAssoc($res);

		return (bool)$row["started"];
	}

	public function getCareerGoalsOptions(): array
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

	public function getAllObserver(string $role_name): array
	{
		$select =
			 "SELECT usr_id, CONCAT(firstname, ' ', lastname) as name\n"
			." FROM usr_data\n"
			." WHERE usr_id IN\n"
			."     (SELECT DISTINCT usr_id\n"
			."      FROM rbac_ua rua\n"
			."      JOIN object_data od\n"
			."          ON rua.rol_id = od.obj_id\n"
			."      WHERE od.title LIKE ".$this->db->quote($role_name."%", "text").")"
		;

		$res = $this->db->query($select);
		$ret = array();
		while ($row = $this->db->fetchAssoc($res)) {
			$ret[(int)$row["usr_id"]] = $row["name"];
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

	/**
	 * Returns user entries for all default result texts.
	 */
	public function getCareerGoalDefaultText(int $career_goal_id): array
	{
		return $this->career_goal_db->getCareerGoalDefaultText($career_goal_id);
	}

	public function update1()
	{
		if($this->getDB()->tableColumnExists(self::PLUGIN_TABLE, "org_unit")) {
			$this->getDB()->modifyTableColumn(self::PLUGIN_TABLE, 'org_unit', array(
				"type" => "text",
				"length" => 64,
				"notnull" => false
			));
		}
	}
}