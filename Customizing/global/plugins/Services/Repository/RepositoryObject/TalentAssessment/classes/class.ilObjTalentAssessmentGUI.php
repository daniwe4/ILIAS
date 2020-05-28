<?php
use CaT\Plugins\TalentAssessment;
use CaT\Plugins\TalentAssessment\Observer as Observer;

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once(__DIR__."/class.ilTalentAssessmentSettingsGUI.php");
require_once(__DIR__."/class.ilTalentAssessmentObserverGUI.php");
require_once(__DIR__."/class.ilTalentAssessmentObservationsGUI.php");
/**
 * User Interface class for career goal repository object.
 *
 * @ilCtrl_isCalledBy ilObjTalentAssessmentGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjTalentAssessmentGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjTalentAssessmentGUI: ilTalentAssessmentSettingsGUI, ilTalentAssessmentObserverGUI, ilRepositorySearchGUI
 * @ilCtrl_Calls ilObjTalentAssessmentGUI: ilTalentAssessmentObservationsGUI
 *
 * @author 		Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilObjTalentAssessmentGUI extends ilObjectPluginGUI
{
	use TalentAssessment\Settings\ilFormHelper;

	const CMD_SHOWCONTENT = "showContent";
	const CMD_SUMMARY = "showSummary";
	const CMD_AUTOCOMPLETE = "userfieldAutocomplete";

	const TAB_SETTINGS = "tab_settings";
	const TAB_OBSERVATIONS = "tab_observations";
	const TAB_OBSERVER = "tab_observer";

	/**
	 * Initialisation
	 */
	protected function afterConstructor()
	{
		global $DIC;

		$this->g_access = $DIC->access();
		$this->g_tabs = $DIC->tabs();
		$this->g_ctrl = $DIC->ctrl();

		$this->tpl->addJavaScript('./Services/Form/js/date_duration.js');
	}

	/**
	 * Get type.
	 */
	final public function getType()
	{
		return "xtas";
	}

	/**
	 * Handles all commmands of this class, centralizes permission checks
	 */
	public function performCommand($cmd)
	{
		$next_class = $this->g_ctrl->getNextClass($this);

		switch ($next_class) {
			case "ilrepositorysearchgui":
				include_once "./Services/Search/classes/class.ilRepositorySearchGUI.php";
				$rep_search = new ilRepositorySearchGUI();
				$obj = new ilTalentAssessmentObserverGUI($this, $this->object->getActions(), $this->plugin->txtClosure(), $this->object->getId());
				$rep_search->setCallback(
					$obj,
					"addObserver",
					$status
				);

				$this->g_ctrl->setReturn($this, "showObserver");
				$this->g_ctrl->forwardCommand($rep_search);
				break;
			default:
				switch ($cmd) {
					case ilTalentAssessmentSettingsGUI::CMD_SHOW:
					case ilTalentAssessmentSettingsGUI::CMD_SAVE:
					case ilTalentAssessmentSettingsGUI::CMD_EDIT:
						$this->forwardSettings();
						break;
					case ilTalentAssessmentObserverGUI::CMD_SHOW:
						$this->showObserver();
						break;
					case ilTalentAssessmentObserverGUI::CMD_ADD:
					case ilTalentAssessmentObserverGUI::CMD_DELETE:
					case ilTalentAssessmentObserverGUI::CMD_DELETE_CONFIRM:
					case ilTalentAssessmentObserverGUI::CMD_DELETE_SELECTED_CONFIRM:
						$this->forwardObserver();
						break;
					case self::CMD_SHOWCONTENT:
						$this->showContent();
						break;
					case ilTalentAssessmentObservationsGUI::CMD_OBSERVATIONS:
					case ilTalentAssessmentObservationsGUI::CMD_OBSERVATIONS_LIST:
					case ilTalentAssessmentObservationsGUI::CMD_OBSERVATIONS_OVERVIEW:
					case ilTalentAssessmentObservationsGUI::CMD_OBSERVATIONS_CUMULATIVE:
					case ilTalentAssessmentObservationsGUI::CMD_OBSERVATIONS_DIAGRAMM:
					case ilTalentAssessmentObservationsGUI::CMD_OBSERVATIONS_REPORT:
					case ilTalentAssessmentObservationsGUI::CMD_OBSERVATION_START:
					case ilTalentAssessmentObservationsGUI::CMD_OBSERVATION_SAVE_VALUES:
					case ilTalentAssessmentObservationsGUI::CMD_OBSERVATION_SAVE_REPORT:
					case ilTalentAssessmentObservationsGUI::CMD_OBSERVATION_PREVIEW_REPORT:
					case ilTalentAssessmentObservationsGUI::CMD_CONFIRM_FINISH_TA:
					case ilTalentAssessmentObservationsGUI::CMD_FINISH_TA:
						$this->forwardObservations();
						break;
					case self::CMD_AUTOCOMPLETE:
						$this->$cmd();
						break;
					default:
						throw new Exception("Unknown command '".$cmd."'");
				}
		}
	}

	/**
	 * After object has been created -> jump to this command
	 */
	public function getAfterCreationCmd()
	{
		return "editProperties";
	}

	/**
	 * Get standard command
	 */
	public function getStandardCmd()
	{
		return "showContent";
	}

	public function initCreateForm($a_new_type)
	{
		$form = parent::initCreateForm($a_new_type);

		$db = $this->plugin->getSettingsDB();
		$career_goal_options = $db->getCareerGoalsOptions();
		$autocomplete_link = $this->g_ctrl->getLinkTarget($this, self::CMD_AUTOCOMPLETE, "", true);
		$org_unit_options = $this->getOrgUnitOptions();
		$this->addSettingsFormItems($form, $career_goal_options, $org_unit_options, $autocomplete_link);

		return $form;
	}

	public function afterSave(\ilObject $newObj)
	{
		$post = $_POST;
		$db = $this->plugin->getSettingsDB();
		$settings = $db->create(
			(int)$newObj->getId(),
			\CaT\Plugins\TalentAssessment\Settings\TalentAssessment::IN_PROGRESS,
			0,
			"text",
			"text",
			"text",
			"text",
			new \ilDateTime(date("Y-m-d H:i:s"), IL_CAL_DATETIME),
			new \ilDateTime(date("Y-m-d H:i:s"), IL_CAL_DATETIME),
			"",
			"",
			false,
			0.0,
			0.0,
			0.0,
			"",
			"",
			"",
			""
		);

		$newObj->setSettings($settings);
		$actions = $newObj->getActions();
		$actions->update($post);

		$title = $newObj->getActions()->getLocalRoleNameFor($newObj->getId());
		$description = $newObj->getActions()->getLocalRoleDescriptionFor($newObj->getId());

		$actions->createLocalRole($newObj, $title, $description);

		parent::afterSave($newObj);
	}

	/**
	 * Set tabs
	 */
	protected function setTabs()
	{
		$this->addInfoTab();

		$view_observations = $this->g_access->checkAccess("view_observations", "", $this->object->getRefId());
		$edit_observations = $this->g_access->checkAccess("edit_observation", "", $this->object->getRefId());
		$finish_ta = $this->g_access->checkAccess("ta_manager", "", $this->object->getRefId());
		if ($view_observations || $edit_observations || $finish_ta) {
			if (!$this->object->getActions()->observationStarted($this->object->getId())) {
				$this->g_tabs->addTab(self::TAB_OBSERVATIONS, $this->txt("observations"), $this->g_ctrl->getLinkTarget($this, ilTalentAssessmentObservationsGUI::CMD_OBSERVATIONS));
			} else {
				$this->g_tabs->addTab(self::TAB_OBSERVATIONS, $this->txt("observations"), $this->g_ctrl->getLinkTarget($this, ilTalentAssessmentObservationsGUI::CMD_OBSERVATIONS_LIST));
			}
		}

		if ($this->g_access->checkAccess("edit_observer", "", $this->object->getRefId())) {
			$this->g_tabs->addTab(self::TAB_OBSERVER, $this->txt("observer"), $this->g_ctrl->getLinkTarget($this, ilTalentAssessmentObserverGUI::CMD_SHOW));
		}

		if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
			$this->g_tabs->addTab(self::TAB_SETTINGS, $this->txt("properties"), $this->g_ctrl->getLinkTarget($this, ilTalentAssessmentSettingsGUI::CMD_EDIT));
		}

		$this->addPermissionTab();
	}

	protected function forwardSettings()
	{
		if (!$this->g_access->checkAccess("write", "", $this->object->getRefId())) {
			\ilUtil::sendFailure($this->plugin->txt('obj_permission_denied'), true);
			$this->g_ctrl->redirectByClass("ilPersonalDesktopGUI", "jumpToSelectedItems");
		} else {
			$this->g_tabs->setTabActive(self::TAB_SETTINGS);
			$actions = $this->object->getActions();
			$gui = new ilTalentAssessmentSettingsGUI($actions, $this->plugin->txtClosure(), $this->object->getId(), $this->object->getSettings()->getPotential(), $this->getOrgUnitOptions());
			$this->g_ctrl->forwardCommand($gui);
		}
	}

	protected function showContent()
	{
		if ($this->g_access->checkAccess("read", "", $this->object->getRefId())) {
			$this->g_ctrl->redirectByClass("ilinfoscreengui", "showSummary");
		} elseif ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
			$_GET["cmd"] = ilTalentAssessmentSettingsGUI::CMD_SHOW;
			$this->forwardSettings();
		} else {
			\ilUtil::sendFailure($this->plugin->txt('obj_permission_denied'), true);
			$this->g_ctrl->redirectByClass("ilPersonalDesktopGUI", "jumpToSelectedItems");
		}
	}

	protected function forwardObservations()
	{
		$view_observations = $this->g_access->checkAccess("view_observations", "", $this->object->getRefId());
		$edit_observations = $this->g_access->checkAccess("edit_observation", "", $this->object->getRefId());
		$ta_manager = $this->g_access->checkAccess("ta_manager", "", $this->object->getRefId());

		if (!($view_observations || $edit_observations || $ta_manager)) {
			\ilUtil::sendFailure($this->plugin->txt('obj_permission_denied'), true);
			$this->g_ctrl->redirectByClass("ilPersonalDesktopGUI", "jumpToSelectedItems");
		} else {
			$this->g_tabs->setTabActive(self::TAB_OBSERVATIONS);
			$actions = $this->object->getActions();
			$gui = new ilTalentAssessmentObservationsGUI($this, $actions, $this->plugin->txtClosure(), $this->object->getSettings(), $this->object->getId());
			$this->g_ctrl->forwardCommand($gui);
		}
	}

	protected function showObserver()
	{
		$this->renderUserSearch();
		$this->forwardObserver();
	}

	protected function renderUserSearch()
	{
		include_once "./Services/Search/classes/class.ilRepositorySearchGUI.php";
			ilRepositorySearchGUI::fillAutoCompleteToolbar(
				$this,
				$this->gToolbar,
				array(
					"auto_complete_name"	=> $this->txt("user"),
					"user_type"				=> $types,
					"submit_name"			=> $this->txt("add"),
					"add_search"			=> false
				)
			);
	}

	protected function forwardObserver()
	{
		if (!$this->g_access->checkAccess("edit_observer", "", $this->object->getRefId())) {
			\ilUtil::sendFailure($this->plugin->txt('obj_permission_denied'), true);
			$this->g_ctrl->redirectByClass("ilPersonalDesktopGUI", "jumpToSelectedItems");
		} else {
			$this->g_tabs->setTabActive(self::TAB_OBSERVER);
			$actions = $this->object->getActions();
			$gui = new ilTalentAssessmentObserverGUI($this, $actions, $this->plugin->txtClosure(), $this->object->getId());
			$this->g_ctrl->forwardCommand($gui);
		}
	}

	public function addInfoItems($info)
	{
		$settings = $this->object->getSettings();
		$actions = $this->object->getActions();
		$career_goal_obj = \ilObjectFactory::getInstanceByObjId($settings->getCareerGoalId());
		$observer = $actions->getAssignedUsers($this->object->getId());
		$obsv_names = array_map(function ($obsv) {
			return $obsv->getFirstname()." ".$obsv->getLastname();
		}, $observer);

		$info->addSection($this->txt('ta_info'));
		$info->addProperty($this->txt('title'), $this->object->getTitle());
		$info->addProperty($this->txt('description'), $this->object->getDescription());
		$info->addProperty($this->txt('state'), $this->txt($actions->potentialText()));


		$info->addProperty($this->txt('career_goal'), $career_goal_obj->getTitle());
		$info->addProperty($this->txt('venue'), $settings->getVenue());
		$info->addProperty($this->txt('observer'), implode(", ", $obsv_names));

		$start_date = $settings->getStartDate()->get(IL_CAL_DATE);
		$end_date = $settings->getEndDate()->get(IL_CAL_DATE);
		if ($start_date == $end_date) {
			$date = $start_date;
		} else {
			$date = $start_date." ".$this->txt("to")." ".$end_date;
		}

		$start_time = explode(" ", $settings->getStartDate());
		$end_time = explode(" ", $settings->getEndDate());

		$info->addProperty($this->txt('date'), $date);
		$info->addProperty($this->txt('start_time'), $start_time[1]);
		$info->addProperty($this->txt('end_time'), $end_time[1]);

		return $info;
	}

	public function userfieldAutocomplete()
	{
		include_once './Services/User/classes/class.ilUserAutoComplete.php';
		$auto = new ilUserAutoComplete();
		$auto->setSearchFields(array('login','firstname','lastname','email'));
		$auto->enableFieldSearchableCheck(false);
		if (($_REQUEST['fetchall'])) {
			$auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
		}
		echo $auto->getList($_REQUEST['term']);
		exit();
	}

	protected function getOrgUnitOptions()
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
}
