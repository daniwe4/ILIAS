<?php
use \CaT\Plugins\TalentAssessment;
use \CaT\Plugins\TalentAssessmentReport;

class ilReportGUI
{
	const CMD_SHOWCONTENT = "showContent";
	const TA_IN_PROGRESS = 1;
	const TA_PASSED = 2;
	const TA_MAYBE = 3;
	const TA_FAILED = 4;
	const OBSERVER_ROLE_NAME = "Observer";


	protected $orgu_assgmnt_queries;

	public function __construct(
		\ilObjTalentAssessmentReportGUI $parent_obj,
		\Closure $txt,
		TalentAssessmentReport\ilActions $actions,
		\ilOrgUnitUserAssignmentQueries $orgu_assgmnt_queries
	)
	{
		global $DIC;

		$this->parent_obj = $parent_obj;
		$this->txt = $txt;
		$this->actions = $actions;
		$this->orgu_assgmnt_queries = $orgu_assgmnt_queries;
		$this->apply_filter = false;
		$this->reset_filter = false;

		$this->g_tpl = $DIC->ui()->mainTemplate();
		$this->g_ctrl = $DIC->ctrl();
		$this->g_rbacreview = $DIC->rbac()->review();
		$this->g_user = $DIC->user();
	}

	public function executeCommand()
	{
		$cmd = $this->g_ctrl->getCmd();

		switch ($cmd) {
			case self::CMD_SHOWCONTENT:
				$this->showContent();
				break;
			case "applyFilter":
				$this->apply_filter = true;
				$this->showContent();
				break;
			case "resetFilter":
				$this->reset_filter = true;
				$this->showContent();
				break;
			case "showPDF":
				$this->showPDF();
				break;
			default:
				throw new LogicException(__METHOD__." unknown command ". $cmd);
		}
	}

	protected function showContent()
	{
		$settings = $this->actions->getObject()->getSettings();

		if ($settings->getIsAdmin()) {
			$this->renderAdminTable();
		} else {
			$this->renderUserTable();
		}
	}

	protected function renderAdminTable()
	{
		$table = new TalentAssessmentReport\Report\ilReportAdminTableGUI($this, $this->txt, $this->actions, $this->apply_filter, $this->reset_filter, self::CMD_SHOWCONTENT);
		$table->setData($this->getAdminTableData($table->getFilterSettings()));
		$this->g_tpl->setContent($table->getHtml());
	}

	protected function renderUserTable()
	{
		$table = new TalentAssessmentReport\Report\ilReportUserTableGUI($this, $this->txt, $this->actions, $this->apply_filter, $this->reset_filter, self::CMD_SHOWCONTENT);
		$table->setData($this->getUserTableData($table->getFilterSettings()));
		$this->g_tpl->setContent($table->getHtml());
	}

	protected function getAdminTableData($filter_values)
	{
		$base_data = $this->actions->getAssessmentsData($filter_values);
		$data = array();

		foreach ($base_data as $key => $row) {
			$row = $this->addOrgUnitTitle($row);
			$row = $this->calcStartDate($row);
			$row = $this->addObserver($row);
			$row = $this->addOrgUnitSupervisor($row);

			if (count($filter_values["observer"]) == 0) {
				$data[$key] = $row;
			} elseif ($this->observerIn($filter_values["observer"], $row)) {
				$data[$key] = $row;
			}
		}

		return $data;
	}

	public function getUserTableData($filter_values)
	{
		$base_data = $this->actions->getAssessmentsData($filter_values);
		$data = array();

		foreach ($base_data as $key => $row) {
			$row = $this->addOrgUnitTitle($row);
			$row = $this->calcStartDate($row);
			$row = $this->addObserver($row);
			$row = $this->addOrgUnitSupervisor($row);

			if ($this->observerIn(array($this->g_user->getId()), $row)) {
				$data[$key] = $row;
			}
		}

		return $data;
	}

	protected function showPDF()
	{
		$ta_obj = \ilObjectFactory::getInstanceByRefId($_GET["xtas_ref_id"]);
		$settings = $ta_obj->getSettings();
		$actions = $ta_obj->getActions();

		$pdf = new TalentAssessment\Observations\ilResultPDF($settings, $actions, $this->txt);
		$file_name = "TA_".$settings->getFirstname()."_".$settings->getLastname().".pdf";
		try {
			$pdf->show("D", $file_name);
		} catch (\Exception $e) {
			throw new \Exception($this->txt("pdf_to_long"));
		}

		$this->render();
	}

	protected function addObserver($data)
	{
		$usrs = $this->getAssignedUsers($data["obj_id"]);
		$usrs_names = array();
		$usrs_ids = array();
		foreach ($usrs as $key => $value) {
			$usrs_names[] = $value->getLastname().", ".$value->getFirstname();
			sort($usrs_names, SORT_STRING);
			$usrs_ids[] = $value->getId();
		}

		$data["observer"] = implode("<br />", $usrs_names);
		$data["observer_ids"] = $usrs_ids;

		return $data;
	}

	protected function addOrgUnitTitle($data)
	{
		$data["org_unit_title"] = ilObject2::_lookUpTitle($data["org_unit"]);

		return $data;
	}

	protected function addOrgUnitSupervisor($data)
	{
		if (array_key_exists("org_unit", $data) && $data["org_unit"] != "" && $data["org_unit"] != 0) {
			$ref_id = array_shift(\ilObject2::_getAllReferences($data["org_unit"]));

			foreach ($this->orgu_assgmnt_queries->getUserIdsOfOrgUnitsInPosition(
							[$ref_id],
							\ilOrgUnitPosition::CORE_POSITION_SUPERIOR
						) as $user_id) {
				$user = \ilObjectFactory::getInstanceByObjId($user_id);
				$names[] = $user->getLastname().", ".$user->getFirstname();
			}

			$data["supervisor"] = implode("<br />", $names);
		}

		return $data;
	}

	protected function calcStartDate($row)
	{
		$start_date = date("d.m.Y", strtotime($row["start_date"]));
		$end_date = date("d.m.Y", strtotime($row["end_date"]));

		if ($start_date[0] == $end_date[0]) {
			$row["start_date_text"] = $start_date;
		} else {
			$row["start_date_text"] = $start_date." - ".$end_date;
		}

		return $row;
	}

	public function observerIn(array $observers, $row)
	{
		return count(array_intersect($observers, $row["observer_ids"])) > 0;
	}

	/**
	 * @param 	string	$code
	 * @return	string
	 */
	public function txt($code)
	{
		assert('is_string($code)');

		$txt = $this->txt;

		return $txt($code);
	}

	public function getOrgUnitOptions()
	{
		$ret = array();

		$orgus = ilObject2::_getObjectsDataForType("orgu");
		$org_root_id = ilObjOrgUnit::getRootOrgId();
		$orgus = array_filter($orgus, function ($o) use ($org_root_id) {
			if ($o["id"] == $org_root_id) {
				return false;
			}

			return true;
		});

		foreach ($orgus as $key => $orgu) {
			$ret[$orgu["id"]] = $orgu["title"];
		}

		return $ret;
	}

	public function getCareerGoalOptions()
	{
		return $this->actions->getCareerGoalsOptions();
	}

	public function getResultOptions()
	{
		return array(self::TA_IN_PROGRESS=>$this->txt("ta_in_progress")
						,self::TA_PASSED=>$this->txt("ta_passed")
						,self::TA_MAYBE=>$this->txt("ta_maybe")
						,self::TA_FAILED=>$this->txt("ta_failed"));
	}

	public function getObserverOptions()
	{
		return $this->actions->getAllObserver();
	}

	protected function getAssignedUsers($obj_id)
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
	protected function getLocalRoleId($obj_id)
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

	protected function getLocalRoleNameFor($obj_id)
	{
		return self::OBSERVER_ROLE_NAME;
	}
}
