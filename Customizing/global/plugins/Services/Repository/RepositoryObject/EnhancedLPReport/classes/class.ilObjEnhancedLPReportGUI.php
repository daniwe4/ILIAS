<?php

use CaT\Plugins\EnhancedLPReport\Settings as Settings;

require_once 'Services/Repository/classes/class.ilObjectPluginGUI.php';
require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/EnhancedLPReport/classes/class.ilObjEnhancedLPReport.php';
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/EnhancedLPReport/classes/class.ilEnhancedLPTableGUI.php';

/**
 * @ilCtrl_isCalledBy ilObjEnhancedLPreportGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjEnhancedLPreportGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
 * @ilCtrl_Calls ilObjEnhancedLPreportGUI: ilCommonActionDispatcherGUI, ilEnhancedLPTableGUI
 */
class ilObjEnhancedLPReportGUI extends ilObjectPluginGUI
{

	protected $g_lng;
	protected $g_ctrl;
	protected $g_tpl;
	protected $g_user;
	protected $g_log;
	protected $g_access;
	protected $g_tabs;

	protected $s_f;
	protected $settings_form_handler;

	protected function afterConstructor()
	{
		global $lng, $ilCtrl, $tpl, $ilUser, $ilLog, $ilAccess, $ilTabs;
		$this->g_lng = $lng;
		$this->g_ctrl = $ilCtrl;
		$this->g_tpl = $tpl;
		$this->g_user = $ilUser;
		$this->g_log = $ilLog;
		$this->g_access = $ilAccess;
		$this->g_tabs = $ilTabs;
		$this->s_f = new Settings\SettingFactory($ilDB);
		$this->settings_form_handler = $this->s_f->reportSettingsFormHandler();
		// TODO: this is crapy. The root cause of this problem is, that the
		// filter should no need to know about it's action. The _rendering_
		// of the filter needs to know about the action.
		$this->g_lng->loadLanguageModule('trac');
		$this->title = null;
	}

	public function getType()
	{
		return 'xlpr';
	}



	public function setTabs()
	{
		$write = $this->g_access->checkAccess("write", "", $this->object->getRefId());
		$perm = $this->g_access->checkAccess("edit_permission", "", $this->object->getRefId());
		if ($write || $perm) {
			// tab for the "show content" command
			if ($this->g_access->checkAccess("read", "", $this->object->getRefId())) {
				$this->g_tabs->addTab(
					"content",
					$this->object->plugin()->txt($this->getType()."_content"),
					$this->g_ctrl->getLinkTarget($this, "showContent")
				);
			}

			// standard info screen tab
			$this->addInfoTab();

			// a "properties" tab
			if ($write) {
				$this->g_tabs->addTab(
					"properties",
					$this->object->plugin()->txt($this->getType()."_properties"),
					$this->g_ctrl->getLinkTarget($this, "settings")
				);
			}
			// standard epermission tab
			$this->addPermissionTab();
		}
	}


	protected function setSubTab($name, $link_target)
	{
		$this->g_tabs->addSubTabTarget(
			$name,
			$this->g_ctrl->getLinkTarget($this, $link_target),
			"write",
			get_class($this)
		);
	}


	/**
	* Besides usual report commands (exportXLS, view, ...) showMenu goes here
	*/
	public function performCommand()
	{
		$cmd = $this->g_ctrl->getCmd("showContent");
		switch ($cmd) {
			case "saveSettings":
				if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
					$this->g_tabs->activateTab("properties");
					return $this->saveSettings();
				}
				break;
			case "settings":
				if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
					$this->setSubTab("edit_settings", "settings");
					$this->g_tabs->activateTab("properties");
					$this->g_tabs->activateSubTab("edit_settings");
					return $this->renderSettings();
				}
				break;
			case "showContent":
				$this->g_tabs->activateTab("content");
				$this->showContent();
				break;
			default:
				if (!$this->performCustomCommand($cmd)) {
					throw new ilException("Unknown Command '$cmd'.");
				}
		}
	}

	public function executeCommand()
	{
		$next_class = $this->g_ctrl->getNextClass($this);
		switch ($next_class) {
			case 'ilenhancedlptablegui':
				$gui = new ilEnhancedLPTableGUI($this->object, $this, $cmd);
				$this->g_ctrl->forwardCommand($gui);
				break;
			default:
				parent::executeCommand();
		}
	}

	public function getAfterCreationCmd()
	{
		return 'settings';
	}

	public function getStandardCmd()
	{
		return 'showContent';
	}

	protected function showContent()
	{
		if ($this->object->getParentCourseId() === null) {
			ilUtil::sendFailure($this->object->plugin()->txt('no_parent_course_error'));
			return;
		}
		if (!$this->object->validRoleSet()) {
			ilUtil::sendFailure($this->object->plugin()->txt('invalid_role_error'));
			return;
		}
		$cmd = $this->g_ctrl->getCmd("showContent");
		$table = new ilEnhancedLPTableGUI($this->object, $this, $cmd);
		$this->g_tpl->setContent($table->getHTML().$this->legend());
	}

	protected function legend()
	{

		$tpl = new ilTemplate("tpl.lp_legend.html", true, true, "Services/Tracking");
		$tpl->setVariable(
			"IMG_NOT_ATTEMPTED",
			ilUtil::getImagePath("scorm/not_attempted.png")
		);
		$tpl->setVariable(
			"IMG_IN_PROGRESS",
			ilUtil::getImagePath("scorm/incomplete.png")
		);
		$tpl->setVariable(
			"IMG_COMPLETED",
			ilUtil::getImagePath("scorm/completed.png")
		);
		$tpl->setVariable(
			"IMG_FAILED",
			ilUtil::getImagePath("scorm/failed.png")
		);
		$tpl->setVariable(
			"TXT_NOT_ATTEMPTED",
			$this->g_lng->txt("trac_not_attempted")
		);
		$tpl->setVariable(
			"TXT_IN_PROGRESS",
			$this->g_lng->txt("trac_in_progress")
		);
		$tpl->setVariable(
			"TXT_COMPLETED",
			$this->g_lng->txt("trac_completed")
		);
		$tpl->setVariable(
			"TXT_FAILED",
			$this->g_lng->txt("trac_failed")
		);
		return $tpl->get();
	}


	protected function renderSettings()
	{
		if ($this->object->getParentCourseId() === null) {
			ilUtil::sendFailure($this->object->plugin()->txt('no_parent_course_error'));
		}
		$settings_form = $this->fillSettingsFormFromDatabase($this->settingsForm());
		$this->g_tpl->setContent($settings_form->getHtml());
	}

	protected function fillSettingsFormFromDatabase($settings_form)
	{
		$data = $this->object->settings;
		$title = $this->object->getTitle();
		$desc = $this->object->getDescription();

		$settings_form->getItemByPostVar('title')->setValue($title);
		$settings_form->getItemByPostVar('description')->setValue($desc);

		$settings_form = $this->settings_form_handler->insertValues($data, $settings_form, $this->object->report_settings);
		return $settings_form;
	}

	protected function saveSettings()
	{
		$settings_form = $this->settingsForm();
		$settings_form->setValuesByPost();
		if ($settings_form->checkInput()) {
			$this->saveSettingsData($settings_form);
			$red = $this->g_ctrl->getLinkTarget($this, "settings", "", false, false);
			ilUtil::redirect($red);
		}
		$this->g_tpl->setContent($settings_form->getHtml());
	}

	protected function saveSettingsData($settings_form)
	{
		$this->object->setTitle($settings_form->getItemByPostVar('title')->getValue());
		$this->object->setDescription($settings_form->getItemByPostVar('description')->getValue());

		$settings = array_merge($this->settings_form_handler->extractValues($settings_form, $this->object->report_settings));
		$this->object->setSettingsData($settings);

		$this->object->doUpdate();
		$this->object->update();
	}

	protected function settingsForm()
	{
		$settings_form = new ilPropertyFormGUI();
		$settings_form->setFormAction($this->g_ctrl->getFormAction($this));
		$settings_form->addCommandButton("saveSettings", $this->g_lng->txt("save"));

		$title = new ilTextInputGUI($this->g_lng->txt('title'), 'title');
		$title->setRequired(true);
		$settings_form->addItem($title);

		$description = new ilTextAreaInputGUI($this->g_lng->txt('description'), 'description');
		$settings_form->addItem($description);

		$this->settings_form_handler->addToForm($settings_form, $this->object->report_settings);
		return $settings_form;
	}
}
