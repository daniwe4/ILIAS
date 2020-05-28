<?php
include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once(__DIR__."/Report/class.ilReportGUI.php");
require_once(__DIR__."/Settings/class.ilTalentAssessmentReportSettingsGUI.php");

/**
 * User Interface class for career goal repository object.
 *
 * @ilCtrl_isCalledBy ilObjTalentAssessmentReportGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjTalentAssessmentReportGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjTalentAssessmentReportGUI: ilReportGUI, ilTalentAssessmentReportSettingsGUI
 *
 * @author 		Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilObjTalentAssessmentReportGUI extends ilObjectPluginGUI
{
	const TAB_CONTENT = "content";
	const TAB_SETTINGS = "settings";
	/**
	 * Initialisation
	 */
	protected function afterConstructor()
	{
		global $ilAccess, $ilTabs, $ilCtrl, $ilToolbar;

		$this->gAccess = $ilAccess;
		$this->gTabs = $ilTabs;
		$this->gCtrl = $ilCtrl;
		$this->plugin = $this->getPlugin();
	}

	/**
	 * Get type.
	 */
	final public function getType()
	{
		return "xtar";
	}

	/**
	 * Handles all commmands of this class, centralizes permission checks
	 */
	public function performCommand($cmd)
	{
		$next_class = $this->gCtrl->getNextClass($this);
		$this->setTitleAndDescription();

		switch ($next_class) {
			case "ilreportgui":
				$this->gTabs->activateTab(self::TAB_CONTENT);
				$gui = new ilReportGUI(
					$this,
					$this->plugin->txtClosure(),
					$this->object->getActions(),
					\ilOrgUnitUserAssignmentQueries::getInstance()
				);
				$this->gCtrl->forwardCommand($gui);
				break;
			case "iltalentassessmentreportsettingsgui":
				$this->gTabs->activateTab(self::TAB_SETTINGS);
				$gui = new ilTalentAssessmentReportSettingsGUI($this, $this->object->getActions(), $this->plugin->txtClosure());
				$this->gCtrl->forwardCommand($gui);
				break;
			default:
				switch ($cmd) {
					case ilReportGUI::CMD_SHOWCONTENT:
						$this->redirectReportGUI();
						break;
					case ilTalentAssessmentReportSettingsGUI::CMD_EDIT_PROPERTIES:
						$this->redirectSettings();
						break;
					default:
						throw new LogicException(__METHOD__." not known command ". $cmd);
				}
		}
	}

	/**
	 * After object has been created -> jump to this command
	 */
	public function getAfterCreationCmd()
	{
		return ilTalentAssessmentReportSettingsGUI::CMD_EDIT_PROPERTIES;
	}

	/**
	 * Get standard command
	 */
	public function getStandardCmd()
	{
		return ilReportGUI::CMD_SHOWCONTENT;
	}

	public function initCreateForm($a_new_type)
	{
		$form = parent::initCreateForm($a_new_type);

		$cb = new ilCheckBoxInputGUI($this->plugin->txt("is_admin"), "is_admin");
		$form->addItem($cb);

		$cb = new ilCheckBoxInputGUI($this->plugin->txt("is_online"), "is_online");
		$form->addItem($cb);

		return $form;
	}

	public function afterSave(ilObject $newObj)
	{
		$post = $_POST;
		$is_admin = (bool)$post["is_admin"];
		$is_online = (bool)$post["is_online"];
		$actions = $newObj->getActions();
		$actions->update($newObj->getTitle(), $newObj->getDescription(), $is_admin, $is_online);
		$newObj->update();

		parent::afterSave($newObj);
	}

	/**
	 * Set tabs
	 */
	protected function setTabs()
	{
		$this->addInfoTab();

		if ($this->gAccess->checkAccess("read", "", $this->object->getRefId())) {
			$this->gTabs->addTab(
				self::TAB_CONTENT,
				$this->plugin->txt(self::TAB_CONTENT),
				$this->gCtrl->getLinkTargetByClass(
					array("ilObjTalentAssessmentReportGUI", "ilReportGUI"),
					ilReportGUI::CMD_SHOWCONTENT,
					"",
					false,
					false
				)
			);
		}

		if ($this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
			$this->gTabs->addTab(
				self::TAB_SETTINGS,
				$this->plugin->txt(self::TAB_SETTINGS),
				$this->gCtrl->getLinkTargetByClass(
					array("ilObjTalentAssessmentReportGUI", "ilTalentAssessmentReportSettingsGUI"),
					ilTalentAssessmentReportSettingsGUI::CMD_EDIT_PROPERTIES,
					"",
					false,
					false
				)
			);
		}

		$this->addPermissionTab();
	}

	protected function redirectReportGUI()
	{
		$link = $this->gCtrl->getLinkTargetByClass(
			array("ilObjTalentAssessmentReportGUI", "ilReportGUI"),
			ilReportGUI::CMD_SHOWCONTENT,
			"",
			false,
			false
		);
		\ilUtil::redirect($link);
	}

	protected function redirectSettings()
	{
		$link = $this->gCtrl->getLinkTargetByClass(
			array("ilObjTalentAssessmentReportGUI", "ilTalentAssessmentReportSettingsGUI"),
			ilTalentAssessmentReportSettingsGUI::CMD_EDIT_PROPERTIES,
			"",
			false,
			false
		);
		\ilUtil::redirect($link);
	}
}
