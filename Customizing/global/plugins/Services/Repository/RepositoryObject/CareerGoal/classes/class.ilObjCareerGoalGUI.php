<?php
use CaT\Plugins\CareerGoal;

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once(__DIR__."/class.ilCareerGoalSettingsGUI.php");
require_once(__DIR__."/class.ilCareerGoalRequirementsGUI.php");
require_once(__DIR__."/class.ilCareerGoalObservationsGUI.php");

/**
 * User Interface class for career goal repository object.
 *
 * @ilCtrl_isCalledBy ilObjCareerGoalGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjCareerGoalGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjCareerGoalGUI: ilCareerGoalSettingsGUI, ilCareerGoalRequirementsGUI, ilCareerGoalObservationsGUI
 *
 * @author 		Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilObjCareerGoalGUI extends ilObjectPluginGUI
{
	use CareerGoal\Settings\ilFormHelper;

	const CMD_PROPERTIES = "editProperties";
	const CMD_SHOWCONTENT = "showContent";

	const TAB_SETTINGS = "tab_settings";
	const TAB_REQUIREMENT = "tab_requirements";
	const TAB_OBSERVATIONS = "tab_observations";

	/**
	 * Initialisation
	 */
	protected function afterConstructor()
	{
		global $DIC;

		$this->g_access = $DIC->access();
		$this->g_tabs = $DIC->tabs();
		$this->g_ctrl = $DIC->ctrl();
	}

	/**
	 * Get type.
	 */
	final public function getType()
	{
		return "xcgo";
	}

	/**
	 * Handles all commmands of this class, centralizes permission checks
	 */
	public function performCommand($cmd)
	{
		$next_class = $this->g_ctrl->getNextClass();
		switch ($next_class) {
			case "ilcareergoalsettingsgui":
				$this->redirectOnMissingPermission("write");
				$this->g_tabs->setTabActive(self::TAB_SETTINGS);
				$actions = $this->object->getActions();
				$gui = new ilCareerGoalSettingsGUI($actions, $this->plugin->txtClosure());
				$this->g_ctrl->forwardCommand($gui);
				break;
			case "ilcareergoalrequirementsgui":
			 	$this->redirectOnMissingPermission("write");
				$this->g_tabs->setTabActive(self::TAB_REQUIREMENT);
				$actions = $this->object->getActions();
				$gui = new ilCareerGoalRequirementsGUI($actions, $this->plugin->txtClosure(), $this->object->getId());
				$this->g_ctrl->forwardCommand($gui);
				break;
			case "ilcareergoalobservationsgui":
				$this->redirectOnMissingPermission("write");
				$this->g_tabs->setTabActive(self::TAB_OBSERVATIONS);
				$actions = $this->object->getActions();
				$gui = new ilCareerGoalObservationsGUI($actions, $this->plugin->txtClosure(), $this->object->getId());
				$this->g_ctrl->forwardCommand($gui);
				break;
			default:
				switch ($cmd) {
					case self::CMD_PROPERTIES:
					case self::CMD_SHOWCONTENT:
						if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
							$this->redirectToSettings();
						} else {
							$this->redirectInfoTab();
						}
						break;
				}
		}
	}

	/**
	 * Checks write permission on objetct
	 *
	 * @param string 	$permission
	 *
	 * @return bool
	 */
	protected function redirectOnMissingPermission($permission)
	{
		if (!$this->g_access->checkAccess("write", "", $this->object->getRefId())) {
			\ilUtil::sendFailure($this->plugin->txt('obj_permission_denied'), true);
			$this->g_ctrl->redirectByClass("ilPersonalDesktopGUI", "jumpToSelectedItems");
		}
	}

	/**
	 * Redirect to settings gui to keep next_class options
	 *
	 * @return void
	 */
	protected function redirectToSettings()
	{
		$link = $this->ctrl->getLinkTargetByClass(
			array("ilObjCareerGoalGUI", "ilCareerGoalSettingsGUI"),
			ilCareerGoalSettingsGUI::CMD_SHOW,
			"",
			false,
			false
		);
		\ilUtil::redirect($link);
	}

	/**
	 * Redirect via link to Info tab
	 *
	 * @return null
	 */
	protected function redirectInfoTab()
	{
		$link = $this->ctrl->getLinkTargetByClass(
			array("ilObjCareerGoalGUI", "ilInfoScreenGUI"),
			"showSummary",
			"",
			false,
			false
		);
		\ilUtil::redirect($link);
	}

	/**
	 * After object has been created -> jump to this command
	 */
	public function getAfterCreationCmd()
	{
		return self::CMD_PROPERTIES;
	}

	/**
	 * Get standard command
	 */
	public function getStandardCmd()
	{
		return self::CMD_SHOWCONTENT;
	}

	public function initCreateForm($a_new_type)
	{
		$form = parent::initCreateForm($a_new_type);
		$this->addSettingsFormItems($form);

		return $form;
	}

	public function afterSave(\ilObject $newObj)
	{
		$post = $_POST;
		$db = $this->plugin->getSettingsDB();
		$settings = $db->create((int)$newObj->getId(), 0, 0, "text", "text", "text");
		$newObj->setSettings($settings);
		$actions = $newObj->getActions();
		$actions->update($post);

		parent::afterSave($newObj);
	}

	/**
	 * Set tabs
	 */
	protected function setTabs()
	{
		$this->addInfoTab();

		if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
			$this->g_tabs->addTab(
				self::TAB_SETTINGS,
				$this->txt("properties"),
				$this->g_ctrl->getLinkTargetByClass(
					"ilCareerGoalSettingsGUI",
					ilCareerGoalSettingsGUI::CMD_SHOW
				)
			);

			$this->g_tabs->addTab(
				self::TAB_REQUIREMENT,
				$this->txt("requirements"),
				$this->g_ctrl->getLinkTargetByClass(
					"ilCareerGoalRequirementsGUI",
					ilCareerGoalRequirementsGUI::CMD_SHOW
				)
			);

			$this->g_tabs->addTab(
				self::TAB_OBSERVATIONS,
				$this->txt("observations"),
				$this->g_ctrl->getLinkTargetByClass(
					"ilCareerGoalObservationsGUI",
					ilCareerGoalObservationsGUI::CMD_SHOW
				)
			);
		}

		$this->addPermissionTab();
	}
}
