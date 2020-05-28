<?php

namespace CaT\Plugins\TalentAssessment;

/**
 * Action class for commiunication between plugin (gui, object, ...) and ilias
 */
class ilActions
{
	const F_TITLE = "title";
	const F_DESCRIPTION = "desc";
	const F_CAREER_GOAL = "career_goal";
	const F_USERNAME = "username";
	const F_DATE = "date";
	const F_VENUE = "venue";
	const F_ORG_UNIT = "org_unit";
	const F_STATE = "state";
	const F_FIRSTNAME = "firstname";
	const F_LASTNAME = "lastname";
	const F_EMAIL = "email";
	const F_RESULT_COMMENT = "resultComment";
	const F_POTENTIAL = "potential";
	const F_JUDGEMENT_TEXT = "judgement_text";

	const START_DATE = "start_date";
	const END_DATE = "end_date";

	const OBSERVER_TEMPLATE_ROLE_NAME = "pl_xtas_observer";
	const OBSERVER_ROLE_NAME = "Observer";
	const OBSERVER_ROLE_DESCRIPTION = "Observer for TA-Meeting (#%s)";

	const SI_PREFIX = "req_id";

	const TA_FAILED = "ta_failed";
	const TA_PASSED = "ta_passed";
	const TA_MAYBE = "ta_maybe";
	const TA_IN_PROGRESS = "ta_in_progress";

	public function __construct(\CaT\Plugins\TalentAssessment\ObjTalentAssessment $object, \CaT\Plugins\TalentAssessment\Settings\DB $settings_db, \CaT\Plugins\TalentAssessment\Observer\DB $observer_db, \CaT\Plugins\TalentAssessment\Observations\DB $observations_db)
	{
		global $DIC;

		$this->object = $object;
		$this->settings_db = $settings_db;
		$this->observer_db = $observer_db;
		$this->observations_db = $observations_db;

		$this->g_rbacadmin = $DIC->rbac()->admin();
		$this->g_rbacreview = $DIC->rbac()->review();
	}

	/**
	 * Update the object with the values from the array.
	 *
	 * @param	array	filled with fields according to F_*-constants
	 * @return  null
	 */
	public function update(array &$values)
	{
		assert('array_key_exists(self::F_TITLE, $values)');
		assert('is_string($values[self::F_TITLE])');
		$this->object->setTitle($values[self::F_TITLE]);
		if (array_key_exists(self::F_DESCRIPTION, $values)) {
			assert('is_string($values[self::F_DESCRIPTION])');
			$this->object->setDescription($values[self::F_DESCRIPTION]);
		} else {
			$this->object->setDescription("");
		}

		if (array_key_exists(self::F_DATE, $values)) {
			$start = \ilCalendarUtil::parseDateString($values[self::F_DATE]["start"], 1)["date"];
			$end = \ilCalendarUtil::parseDateString($values[self::F_DATE]["end"], 1)["date"];

			$values[self::START_DATE] = $start;
			$values[self::END_DATE] = $end;

			$this->object->updateSettings(function ($s) use (&$values) {
				return $s
				->withStartDate($values[self::START_DATE])
				->withEndDate($values[self::END_DATE])
				;
			});
		}

		$this->object->updateSettings(function ($s) use (&$values) {
			return $s
				->withCareerGoalID((int)$values[self::F_CAREER_GOAL])
				->withUsername($values[self::F_USERNAME])
				->withVenue($values[self::F_VENUE])
				->withOrgUnit($values[self::F_ORG_UNIT])
				;
		});

		$this->object->update();
	}

	/**
	 * Read the object to an array.
	 *
	 * @return array
	 */
	public function read()
	{
		$values = array();
		$values[self::F_TITLE] = $this->object->getTitle();
		$values[self::F_DESCRIPTION] = $this->object->getDescription();

		$settings = $this->object->getSettings();
		$values[self::F_CAREER_GOAL] = $settings->getCareerGoalId();
		$values[self::F_USERNAME] = $settings->getUSername();

		$start_date = $settings->getStartDate()->get(IL_CAL_DATETIME);
		$start_date = explode(" ", $start_date);
		$end_date = $settings->getEndDate()->get(IL_CAL_DATETIME);
		$end_date = explode(" ", $end_date);

		$date = array("start" => $settings->getStartDate()->get(IL_CAL_DATETIME)
					, "end" => $settings->getEndDate()->get(IL_CAL_DATETIME)
									);

		$values[self::F_DATE] = $date;
		$values[self::F_VENUE] = $settings->getVenue();
		$values[self::F_ORG_UNIT] = $settings->getOrgUnit();
		$values[self::F_FIRSTNAME] = $settings->getFirstname();
		$values[self::F_LASTNAME] = $settings->getLastname();
		$values[self::F_EMAIL] = $settings->getEmail();

		return $values;
	}

	/**
	 * Get options of career goals for settings
	 *
	 * @return array<int, string>
	 */
	public function getCareerGoalsOptions()
	{
		return $this->settings_db->getCareerGoalsOptions();
	}

	/**
	 * create local observer role for $obj_id
	 *
	 * @param 	\ilObject 	$newObj
	 */
	public function createLocalRole(\ilObject $newObj, $title, $description)
	{
		include_once './Services/AccessControl/classes/class.ilObjRole.php';
		$role = new \ilObjRole();
		$role->setTitle($title);
		$role->setDescription($description);
		$role->create();

		$this->g_rbacadmin->assignRoleToFolder($role->getId(), $newObj->getRefId());

		// protect
		$this->g_rbacadmin->setProtected(
			$newObj->getRefId(),
			$role->getId(),
			'n'
		);

		$rolt_obj_id = $this->observer_db->getRoltId(self::OBSERVER_TEMPLATE_ROLE_NAME);
		$this->g_rbacadmin->copyRoleTemplatePermissions($rolt_obj_id, ROLE_FOLDER_ID, $newObj->getRefId(), $role->getId(), false);

		// SET OBJECT PERMISSIONS OF COURSE OBJECT
		$ops = $this->g_rbacreview->getOperationsOfRole($role->getId(), "xtas", $role->getRefId());
		$this->g_rbacadmin->grantPermission($role->getId(), $ops, $newObj->getRefId());
	}

	/**
	 * assign user to local observer role
	 *
	 * @param int 	$user_id
	 */
	public function assignObserver($user_id, $obj_id)
	{
		$role_id = $this->getLocalRoleId($obj_id);

		$this->g_rbacadmin->assignUser($role_id, $user_id);
	}

	/**
	 * deassign user to local observer role
	 *
	 * @param int 	$user_id
	 */
	public function deassignObserver($user_id, $obj_id)
	{
		if ($this->observationStarted($obj_id)) {
			$this->observations_db->deleteObservationResults($obj_id, $user_id);
			$middle = $this->requestsMiddle();
			$this->updatePotential($middle);
		}

		$role_id = $this->getLocalRoleId($obj_id);
		$this->g_rbacadmin->deassignUser($role_id, $user_id);
	}

	// TODO: Why can this return null? An empty array would be enough.
	public function getAssignedUsers($obj_id)
	{
		$ret = array();
		$role_id = $this->getLocalRoleId($obj_id);

		foreach ($this->g_rbacreview->assignedUsers($role_id) as $user_id) {
			$user = \ilObjectFactory::getInstanceByObjId($user_id);
			$ret[] = $user;
		}

		return $ret;
	}

	/**
	 * Get the local role id for obj
	 *
	 * @param int 	$obj_id
	 *
	 * @return int 	$role_id
	 */
	public function getLocalRoleId($obj_id)
	{
		$ref_id = array_shift(\ilObject::_getAllReferences($obj_id));
		$role_name = $this->getLocalRoleNameFor($obj_id);

		foreach ($this->g_rbacreview->getLocalRoles($ref_id) as $key => $local_role) {
			if ($role_name = \ilObject::_lookUpTitle($local_role)) {
				return $local_role;
			}
		}

		throw new \Exception(__METHOD__.": No local role found for ".$obj_id);
	}

	public function getLocalRoleNameFor($obj_id)
	{
		return self::OBSERVER_ROLE_NAME;
	}

	public function getLocalRoleDescriptionFor($obj_id)
	{
		return sprintf(self::OBSERVER_ROLE_DESCRIPTION, $obj_id);
	}

	/**
	 * Is the observation sarted or not
	 *
	 * @param int 	$obj_id
	 */
	public function observationStarted($obj_id)
	{
		return $this->settings_db->isStarted($obj_id);
	}

	/**
	 * Set the satte of observation to startetd
	 *
	 * @param boolean 	$started
	 */
	public function setObservationStarted($started)
	{
		$this->object->updateSettings(function ($s) use ($started) {
			return $s
				->withStarted($started)
				;
		});
		$this->object->update();
	}

	/**
	 * Copy the default texts from career foal to ta
	 *
	 * @param int 	$career_goal_id
	 */
	public function copyCopyDefaultText($career_goal_id)
	{
		$default_texts = $this->settings_db->getCareerGoalDefaultText($career_goal_id);

		$this->updateDefaultText($default_texts);
	}

	protected function updateDefaultText(array &$values)
	{
		$this->object->updateSettings(function ($s) use (&$values) {
			return $s
				->withDefaultTextFailed($values["default_text_failed"])
				->withDefaultTextPartial($values["default_text_partial"])
				->withDefaultTextSuccess($values["default_text_success"])
				;
		});

		$this->object->update();
	}

	/**
	 * Copy observations from career goal
	 *
	 * @param int 	$obj_id
	 * @param int 	$career_goal_id
	 */
	public function copyObservations($obj_id, $career_goal_id)
	{
		$this->observations_db->copyObservations($obj_id, $career_goal_id);
	}

	/**
	 * Get observations from career goal
	 *
	 * @param int 	$career_goal_id
	 *
	 * @return array
	 */
	public function getBaseObservations($career_goal_id)
	{
		return $this->observations_db->getBaseObservations($career_goal_id);
	}

	/**
	 * Get observation for overview
	 *
	 * @param int 	$obj_id
	 *
	 * @return array
	 */
	public function getObservationListData($obj_id)
	{
		return $this->observations_db->getObservations($obj_id);
	}

	/**
	 * Set the notice for observation
	 *
	 * @param int 		$obj_id
	 * @param string 	$notice
	 */
	public function setNoticeFor($obs_id, $notice)
	{
		$this->observations_db->setNotice((int)$obs_id, $notice);
	}

	/**
	 * Set points for observation
	 *
	 * @param array 	$post
	 */
	public function setPoints($post)
	{
		$points = $post[self::SI_PREFIX];

		foreach ($points as $req_id => $points) {
			$this->observations_db->setPoints((int)$req_id, (float)$points);
		}
	}

	/**
	 * Get data for observation overview
	 *
	 * @param int 	$obj_id
	 * @param int 	$role_id
	 *
	 * @return array
	 */
	public function getObservationOverviewData($obj_id, $role_id)
	{
		return $this->observations_db->getObservationOverviewData($obj_id, $role_id);
	}

	/**
	 * Get data for cumulative view
	 *
	 * @param int 	$obj_id
	 *
	 * @return array
	 */
	public function getObservationsCumulative($obj_id)
	{
		return $this->observations_db->getObservationsCumulative($obj_id);
	}

	/**
	 * Get request result for cumulative view
	 *
	 * @param int 	$obs_ids
	 *
	 * @return array
	 */
	public function getRequestresultCumulative($obs_ids)
	{
		return $this->observations_db->getRequestresultCumulative($obs_ids);
	}

	/**
	 * Copy lowmark and should from career goal
	 *
	 * @param int 	$career_goal_id
	 */
	public function copyClassificationValues($career_goal_id)
	{
		$career_goal_obj = \ilObjectFactory::getInstanceByObjId($career_goal_id);

		$this->object->updateSettings(function ($s) use ($career_goal_obj) {
			return $s
				->withLowmark($career_goal_obj->getSettings()->getLowmark())
				->withShouldSpecifiaction($career_goal_obj->getSettings()->getShouldSpecification())
				;
		});
		$this->object->update();
	}

	/**
	 * Save the values for report
	 *
	 * @param array 	$post
	 */
	public function saveReportData($post)
	{
		$settings = $this->object->getSettings();
		$potential = $settings->getPotential();
		$lowmark = $settings->getLowmark();
		$should = $settings->getShouldSpecification();

		if ($potential < $lowmark) {
			$this->object->updateSettings(function ($s) use ($post) {
				return $s
					->withDefaultTextFailed($post[self::F_JUDGEMENT_TEXT])
					;
			});
		} elseif ($potential > $should) {
			$this->object->updateSettings(function ($s) use ($post) {
				return $s
					->withDefaultTextSuccess($post[self::F_JUDGEMENT_TEXT])
					;
			});
		} else {
			$this->object->updateSettings(function ($s) use ($post) {
				return $s
					->withDefaultTextPartial($post[self::F_JUDGEMENT_TEXT])
					;
			});
		}

		$this->object->updateSettings(function ($s) use ($post) {
			return $s
				->withResultComment($post[self::F_RESULT_COMMENT])
				;
		});
		$this->object->update();
	}

	/**
	 * Finish the talent assessment
	 */
	public function finishTA()
	{
		$this->object->updateSettings(function ($s) {
			return $s
				->withFinished(true)
				;
		});
		$this->object->update();
	}

	/**
	 * Update the potential of tested user
	 *
	 * @param int 	$potential
	 */
	public function updatePotential($potential)
	{
		$this->object->updateSettings(function ($s) use ($potential) {
			return $s
				->withPotential($potential)
				;
		});
		$this->object->update();
	}

	/**
	 * Get the potential of user for form
	 *
	 * @param array 	$values
	 * @param int 		$potential
	 *
	 * @return array
	 */
	public function setPotentialToValues($values, $potential)
	{
		$values[self::F_STATE] = $potential;

		return $values;
	}

	/**
	 * Get the potential text according to current potential
	 *
	 * @return string
	 */
	public function potentialText()
	{
		$settings = $this->object->getSettings();

		if (!$settings->Finished()) {
			return self::TA_IN_PROGRESS;
		}

		if (!$middle = $settings->getPotential()) {
			$middle = $this->requestsMiddle();
		}

		if ($middle <= $settings->getLowmark()) {
			return self::TA_FAILED;
		} elseif ($middle >= $settings->getShouldSpecification()) {
			return self::TA_PASSED;
		} else {
			return self::TA_MAYBE;
		}
	}

	/**
	 * Get the middle of all observations
	 *
	 * @return float
	 */
	public function requestsMiddle()
	{
		$obs = $this->getObservationsCumulative($this->object->getId());
		$req_res = $this->getRequestresultCumulative(array_keys($obs));

		$middle_total = 0;
		foreach ($req_res as $key => $req_det) {
			$sum += $req_det["sum"];
		}

		$middle = $sum / count($req_res);
		$middle_total += $middle;

		return round($middle_total, 1);
	}

	/**
	 * Get the title of the org unit
	 *
	 * @param int 	$org_unit_id
	 *
	 * @return string
	 */
	public function getOrgUnitTitle($org_unit_id)
	{
		return \ilObject2::_lookupTitle($org_unit_id);
	}

	/**
	 * Get the title of career goal
	 *
	 * @param int 	$career_goal_id
	 *
	 * @return string
	 */
	public function getCareerGoalTitle($career_goal_id)
	{
		$obj = \ilObjectFactory::getInstanceByObjId($career_goal_id);
		return $obj->getTitle();
	}

	public function getSettings()
	{
		return $this->object->getSettings();
	}

	public function getNumberOfVotes($usr_id)
	{
		return $this->observations_db->getNumberOfVotes($this->object->getId(), $usr_id);
	}
}
