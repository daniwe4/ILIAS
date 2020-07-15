<?php

use CaT\Plugins\TalentAssessment;

include_once("Services/Form/classes/class.ilPropertyFormGUI.php");

class ilTalentAssessmentSettingsGUI
{
	use TalentAssessment\Settings\ilFormHelper;

	const CMD_SHOW = "showSettings";
	const CMD_SAVE = "saveSettings";
	const CMD_EDIT = "editProperties";
	const CMD_AUTOCOMPLETE = "userfieldAutocomplete";

	/**
	 * @var Closure
	 */
	protected $txt;

	/**
	 * @var ilActions
	 */
	protected $actions;

	public function __construct(TalentAssessment\ilActions $actions, \Closure $txt, $obj_id, $potential, array $org_unit_options)
	{
		global $DIC;

		$this->g_ctrl = $DIC->ctrl();
		$this->g_tpl = $DIC->ui()->mainTemplate();

		$this->actions = $actions;
		$this->txt = $txt;
		$this->obj_id = $obj_id;
		$this->potential = $potential;
		$this->org_unit_options = $org_unit_options;
	}

	public function executeCommand()
	{
		$cmd = $this->g_ctrl->getCmd();
		switch ($cmd) {
			case self::CMD_SHOW:
			case self::CMD_SAVE:
			case self::CMD_AUTOCOMPLETE:
			case self::CMD_EDIT:
				$this->$cmd();
				break;
			default:
				throw new \Exception("ilTalentAssessmentSettingsGUI:: Unknown command ".$cmd);
		}
	}

	protected function editProperties()
	{
		$this->showSettings();
	}


	protected function showSettings()
	{
		$form = $this->initSettingsForm();
		$this->fillSettingsForm($form);
		$this->g_tpl->setContent($form->getHTML());
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

	protected function initSettingsForm()
	{
		$form = new \ilPropertyFormGUI();
		$form->setTitle($this->txt('obj_edit_settings'));

		$ti = new \ilTextInputGUI($this->txt('obj_title'), TalentAssessment\ilActions::F_TITLE);
		$ti->setRequired(true);
		$form->addItem($ti);

		$ta = new \ilTextAreaInputGUI($this->txt('obj_description'), TalentAssessment\ilActions::F_DESCRIPTION);
		$form->addItem($ta);

		$career_goal_options = $this->actions->getCareerGoalsOptions();

		$autocomplete_link = $this->g_ctrl->getLinkTarget($this, self::CMD_AUTOCOMPLETE, "", true);
		$this->addSettingsFormItemsUpdate($form, $career_goal_options, $this->org_unit_options, $this->actions->observationStarted($this->obj_id), $autocomplete_link);

		$form->addCommandButton(self::CMD_SAVE, $this->txt('obj_save'));
		$form->addCommandButton(self::CMD_SHOW, $this->txt("cancel"));
		$form->setFormAction($this->g_ctrl->getFormAction($this));

		return $form;
	}

	protected function fillSettingsForm(\ilPropertyFormGUI $form)
	{
		$values = $this->actions->read();
		$values = $this->actions->setPotentialToValues($values, $this->txt($this->actions->potentialText()));
		if ($values[TalentAssessment\ilActions::F_FIRSTNAME] === "") {
			\ilUtil::sendFailure($this->txt("no_valid_username"));
		}
		$form->setValuesByArray($values);
	}

	protected function saveSettings()
	{
		$form = $this->initSettingsForm();
		if ($form->checkInput()) {
			$post = $_POST;
			$this->actions->update($post);
			\ilUtil::sendSuccess($this->txt("saved"), true);
			$this->g_ctrl->redirect($this, self::CMD_SHOW);
		} else {
			$form->setValuesByPost();
			\ilUtil::sendFailure($this->txt("not_saved"), true);
			$this->g_tpl->setContent($form->getHTML());
		}
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
}
