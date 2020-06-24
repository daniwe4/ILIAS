<?php

use CaT\Plugins\TalentAssessmentReport\ilActions;

class ilTalentAssessmentReportSettingsGUI
{
	const CMD_EDIT_PROPERTIES = "editProperties";
	const CMD_SAVE_PROPERTIES = "saveProperties";
	const CMD_CANCEL = "cancel";

	public function __construct(\ilObjTalentAssessmentReportGUI $parent_obj, ilActions $actions, \Closure $txt)
	{
		global $DIC;

		$this->parent_obj = $parent_obj;
		$this->txt = $txt;
		$this->actions = $actions;

		$this->g_tpl = $DIC->ui()->mainTemplate();
		$this->g_ctrl = $DIC->ctrl();
		$this->g_rbacreview = $DIC->rbac()->review();
		$this->g_user = $DIC->user();
	}

	public function executeCommand()
	{
		$cmd = $this->g_ctrl->getCmd(self::CMD_EDIT_PROPERTIES);

		switch ($cmd) {
			case self::CMD_EDIT_PROPERTIES:
			case self::CMD_CANCEL:
				$this->showSettings();
				break;
			case self::CMD_SAVE_PROPERTIES:
				$this->save();
				break;
			default:
				throw new LogicException(__METHOD__." unknown command ".$cmd);
		}
	}

	/**
	 * Shows current settings
	 *
	 * @param ilPropertyFormGUI | null 	$form
	 *
	 * @return null
	 */
	protected function showSettings($form = null)
	{
		if ($form === null) {
			$form = $this->initForm();
			$this->fillForm($form);
		}

		$this->g_tpl->setContent($form->getHtml());
	}

	/**
	 * Saves settings
	 *
	 * @return null
	 */
	protected function save()
	{
		$post = $_POST;
		$title = $post["title"];
		$description = $post["description"];
		$is_admin = (bool)$post["is_admin"];
		$is_online = (bool)$post["is_online"];

		$this->actions->update($title, $description, $is_admin, $is_online);
		ilUtil::sendSuccess($this->txt("settings_successful_saved"), true);
		$this->g_ctrl->redirect($this);
	}

	protected function fillForm(ilPropertyFormGUI &$form)
	{
		$object = $this->actions->getObject();

		$values = ["title" => $object->getTitle(),
				   "description" => $object->getDescription(),
				   "is_admin" => $object->getSettings()->getIsAdmin(),
				   "is_online" => $object->getSettings()->getIsOnline()
		];

		$form->setValuesByArray($values);
	}

	protected function initForm()
	{
		require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->g_ctrl->getFormActionByClass(array("ilObjTalentAssessmentReportGUI", "ilTalentAssessmentReportSettingsGUI")));
		$form->addCommandButton(self::CMD_SAVE_PROPERTIES, $this->txt("save"));
		$form->addCommandButton(self::CMD_CANCEL, $this->txt("cancel"));

		$ti = new ilTextInputGUI($this->txt("title"), "title");
		$ti->setRequired(true);
		$form->addItem($ti);

		$ta = new ilTextAreaInputGUI($this->txt("description"), "description");
		$form->addItem($ta);

		$cb = new ilCheckboxInputGUI($this->txt("is_admin"), "is_admin");
		$form->addItem($cb);

		$cb = new ilCheckboxInputGUI($this->txt("is_online"), "is_online");
		$form->addItem($cb);

		return $form;
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
}
