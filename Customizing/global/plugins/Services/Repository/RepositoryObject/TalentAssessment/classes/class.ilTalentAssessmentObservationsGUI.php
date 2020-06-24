<?php

use CaT\Plugins\TalentAssessment;

class ilTalentAssessmentObservationsGUI
{
	const CMD_OBSERVATIONS = "showObservations";
	const CMD_OBSERVATIONS_LIST = "showObservationsList";
	const CMD_OBSERVATIONS_OVERVIEW = "showObservationsOverview";
	const CMD_OBSERVATIONS_CUMULATIVE = "showObservationsCumulative";
	const CMD_OBSERVATIONS_DIAGRAMM = "showObservationsDiagramm";
	const CMD_OBSERVATIONS_REPORT = "showObservationsReport";

	const CMD_OBSERVATION_START = "startObservation";
	const CMD_OBSERVATION_SAVE_VALUES = "saveObservationValues";
	const CMD_OBSERVATION_SAVE_REPORT = "saveObservationReport";
	const CMD_OBSERVATION_PREVIEW_REPORT = "showReportPreview";
	const CMD_CONFIRM_FINISH_TA = "confirmFinishTA";
	const CMD_FINISH_TA = "finishTA";
	const CMD_CANCEL = "cancel";



	public function __construct($parent_obj, $actions, Closure $txt, \CaT\Plugins\TalentAssessment\Settings\TalentAssessment $settings, $obj_id)
	{
		global $DIC;

		$this->g_tpl = $DIC->ui()->mainTemplate();
		$this->g_ctrl = $DIC->ctrl();
		$this->g_toolbar = $DIC->toolbar();
		$this->g_tabs = $DIC->tabs();
		$this->g_access = $DIC->access();

		$this->parent_obj = $parent_obj;
		$this->actions = $actions;
		$this->txt = $txt;
		$this->settings = $settings;
		$this->obj_id = $obj_id;

		$this->possible_cmd = array("CMD_OBSERVATION_SAVE_VALUES"=>self::CMD_OBSERVATION_SAVE_VALUES
								  , "CMD_OBSERVATION_SAVE_REPORT"=>self::CMD_OBSERVATION_SAVE_REPORT);

		$this->defineUserPermissions();
	}

	public function executeCommand()
	{
		$cmd = $this->g_ctrl->getCMD(self::CMD_OBSERVATIONS);

		switch ($cmd) {
			case self::CMD_OBSERVATIONS:
			case self::CMD_FINISH_TA:
			case self::CMD_CONFIRM_FINISH_TA:
				$this->$cmd();
				break;
			case self::CMD_OBSERVATION_PREVIEW_REPORT:
				$this->$cmd();
				break;
			case self::CMD_OBSERVATIONS_LIST:
			case self::CMD_OBSERVATIONS:
			case self::CMD_OBSERVATIONS_OVERVIEW:
			case self::CMD_OBSERVATIONS_CUMULATIVE:
			case self::CMD_OBSERVATIONS_DIAGRAMM:
			case self::CMD_OBSERVATIONS_REPORT:
			case self::CMD_OBSERVATION_START:
			case self::CMD_OBSERVATION_SAVE_VALUES:
			case self::CMD_OBSERVATION_SAVE_REPORT:
				$this->setSubtabs($cmd);
				$this->$cmd();
				break;
			default:
				throw new \Exception("ilTalentAssessmentObservationsGUI:: Unknown command ".$cmd);
		}
	}

	protected function showObservations()
	{
		$this->setToolbarObservations();
		$gui = new TalentAssessment\Observations\ilObservationsTableGUI($this);
		$this->g_tpl->setContent($gui->getHtml());
	}

	protected function showObservationsList()
	{
		if ($this->edit_observations) {
			$gui = new TalentAssessment\Observations\ilObservationsListGUI($this);
			$this->g_tpl->setContent($gui->render());
			return;
		}

		if ($this->view_observations) {
			$this->showObservationsOverview();
			return;
		}

		if ($this->ta_manager) {
			$this->showObservationsReport();
			return;
		}
	}

	protected function showObservationsOverview()
	{
		$gui = new TalentAssessment\Observations\ilObservationsOverviewGUI($this);
		$this->g_tpl->setContent($gui->render());
	}

	protected function showObservationsCumulative()
	{
		$gui = new TalentAssessment\Observations\ilObservationsCumulativeGUI($this);
		$this->g_tpl->setContent($gui->render());
	}

	protected function showObservationsDiagramm()
	{
		$gui = new TalentAssessment\Observations\ilObservationsDiagrammGUI($this->getSettings(), $this->getActions(), $this->txt);
		$this->g_tpl->setContent($gui->render());
	}

	protected function showObservationsReport()
	{
		$this->setToolbarReport();
		$gui = new TalentAssessment\Observations\ilObservationsReportGUI($this);
		$gui->show();
	}

	protected function saveObservationReport()
	{
		$this->actions->saveReportData($_POST);
		$red = $this->g_ctrl->getLinkTarget($this->parent_obj, self::CMD_OBSERVATIONS_REPORT, "", false, false);
		\ilUtil::redirect($red);
	}

	protected function showReportPreview()
	{
		$pdf = new TalentAssessment\Observations\ilResultPDF($this->getSettings(), $this->getActions(), $this->getTXTClosure());
		try {
			$pdf->show('I', $this->getActions()->getCareerGoalTitle($this->getSettings()->getCareerGoalId().".pdf"));
		} catch (\Exception $e) {
			var_dump($e->getMessage());
		}
	}

	protected function confirmFinishTA()
	{
		$confirmation = $this->getConfirmationForm(self::CMD_FINISH_TA, $this->txt("confirm_finish_ta"));

		$confirmation->setCancel($this->txt("cancel"), self::CMD_OBSERVATIONS_REPORT);
		$confirmation->setConfirm($this->txt("finish_ta"), self::CMD_FINISH_TA);

		$this->g_tpl->setContent($confirmation->getHtml());
	}

	/**
	 * Get instance of confirmation form
	 *
	 * @param string 	$cmd
	 * @param string 	$header_text
	 *
	 * @return \ilConfirmationGUI
	 */
	protected function getConfirmationForm($cmd, $header_text)
	{
		require_once "./Services/Utilities/classes/class.ilConfirmationGUI.php";
		$confirmation = new ilConfirmationGUI();
		$confirmation->setFormAction($this->g_ctrl->getFormAction($this, $cmd));
		$confirmation->setHeaderText($header_text);

		return $confirmation;
	}

	protected function finishTA()
	{
		$this->actions->finishTA();
		$this->actions->updatePotential($this->actions->requestsMiddle());

		$red = $this->g_ctrl->getLinkTarget($this->parent_obj, self::CMD_OBSERVATIONS_REPORT, "", false, false);
		\ilUtil::redirect($red);
	}

	protected function setToolbarObservations()
	{
		$observer = $this->actions->getAssignedUsers($this->getObjId());
		$observations = $this->actions->getBaseObservations($this->settings->getCareerGoalId());

		$show = true;
		$msg = array();

		if (!$observations || ($observations && count($observations) == 0)) {
			$msg[] = $this->txt("no_observations_on_career_goal");
			$show = false;
		}

		if (!$observer || ($observer && count($observer) == 0)) {
			$msg[] = $this->txt("no_observator_defined");
			$show = false;
		}

		if ($show && $this->ta_manager) {
			$start_observation_link = $this->g_ctrl->getLinkTarget($this->parent_obj, self::CMD_OBSERVATION_START);
			$this->g_toolbar->addButton($this->txt("start_observation"), $start_observation_link);
		} elseif (!$show && $this->ta_manager) {
			\ilUtil::sendInfo(implode("<br />", $msg));
		}
	}

	protected function setToolbarReport()
	{
		$start_observation_link = $this->g_ctrl->getLinkTarget($this->parent_obj, self::CMD_OBSERVATION_PREVIEW_REPORT);
		$this->g_toolbar->addButton($this->txt("preview_report"), $start_observation_link, "blank");

		if (!$this->settings->Finished()) {
			$finish_ta_link = $this->g_ctrl->getLinkTarget($this, self::CMD_CONFIRM_FINISH_TA);
			$this->g_toolbar->addButton($this->txt("finish_ta"), $finish_ta_link);
		}
	}

	protected function startObservation()
	{
		$observer = $this->actions->getAssignedUsers($this->getObjId());

		if ($observer && count($observer) > 0) {
			$this->actions->setObservationStarted(true);
			$this->actions->copyClassificationValues($this->settings->getCareerGoalId());
			$this->actions->copyCopyDefaultText($this->settings->getCareerGoalId());
			$this->actions->copyObservations($this->getObjId(), $this->settings->getCareerGoalId());
			$red = $this->g_ctrl->getLinkTarget($this->parent_obj, self::CMD_OBSERVATIONS_LIST, "", false, false);
		} else {
			\ilUtil::sendInfo($this->txt("no_observer_cant_start"), true);
			$red = $this->g_ctrl->getLinkTarget($this->parent_obj, self::CMD_OBSERVATIONS, "", false, false);
		}

		\ilUtil::redirect($red);
	}

	protected function saveObservationValues()
	{
		if (!isset($_GET["obs_id"]) || $_GET["obs_id"] == "") {
			throw new \Exception("No observation id given");
		}

		$obs_id = $_GET["obs_id"];
		$this->actions->setNoticeFor($obs_id, $_POST["notice"]);
		$this->actions->setPoints($_POST);

		$red = $this->g_ctrl->getLinkTarget($this->parent_obj, self::CMD_OBSERVATIONS_LIST, "", false, false);
		$red = $red."#pos".$_GET["pos"];
		\ilUtil::redirect($red);
	}

	protected function setSubtabs($activate)
	{
		if ($this->edit_observations) {
			$this->g_tabs->addSubTab(self::CMD_OBSERVATIONS_LIST, $this->txt("observation_list"), $this->g_ctrl->getLinkTarget($this, self::CMD_OBSERVATIONS_LIST));
		}

		if ($this->view_observations) {
			$this->g_tabs->addSubTab(self::CMD_OBSERVATIONS_OVERVIEW, $this->txt("observation_overview"), $this->g_ctrl->getLinkTarget($this, self::CMD_OBSERVATIONS_OVERVIEW));
			$this->g_tabs->addSubTab(self::CMD_OBSERVATIONS_CUMULATIVE, $this->txt("observation_cumultativ"), $this->g_ctrl->getLinkTarget($this, self::CMD_OBSERVATIONS_CUMULATIVE));
			$this->g_tabs->addSubTab(self::CMD_OBSERVATIONS_DIAGRAMM, $this->txt("observation_diagramm"), $this->g_ctrl->getLinkTarget($this, self::CMD_OBSERVATIONS_DIAGRAMM));
		}

		if ($this->ta_manager) {
			$this->g_tabs->addSubTab(self::CMD_OBSERVATIONS_REPORT, $this->txt("observation_report"), $this->g_ctrl->getLinkTarget($this, self::CMD_OBSERVATIONS_REPORT));
		}

		$this->g_tabs->activateSubTab($activate);
	}

	/**
	 * Define permissions of observations for ilUser
	 */
	protected function defineUserPermissions()
	{
		$this->view_observations = $this->g_access->checkAccess("view_observations", "", $this->parent_obj->object->getRefId());
		$this->edit_observations = $this->g_access->checkAccess("edit_observation", "", $this->parent_obj->object->getRefId());
		$this->ta_manager = $this->g_access->checkAccess("ta_manager", "", $this->parent_obj->object->getRefId());
	}

	/**
	 * @param 	string	$code
	 * @return	string
	 */
	public function txt(string $code)
	{
		$txt = $this->txt;

		return $txt($code);
	}

	public function getTXTClosure()
	{
		return $this->txt;
	}

	public function getActions()
	{
		return $this->actions;
	}

	public function getObjId()
	{
		return $this->obj_id;
	}

	public function getPossibleCMD()
	{
		return $this->possible_cmd;
	}

	public function getSettings()
	{
		return $this->settings;
	}
}
