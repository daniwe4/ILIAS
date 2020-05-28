<?php

use CaT\Plugins\TalentAssessment\Observer;

class ilTalentAssessmentObserverGUI
{
	const CMD_SHOW = "showObserver";
	const CMD_ADD = "addObserver";
	const CMD_DELETE = "deleteObservers";
	const CMD_DELETE_CONFIRM = "deleteObserverConfirm";
	const CMD_DELETE_SELECTED_CONFIRM = "deleteSelectedObserverConfirm";

	public function __construct($parent_obj, $actions, Closure $txt, $obj_id)
	{
		global $DIC;

		$this->g_tpl = $DIC->ui()->mainTemplate();
		$this->g_ctrl = $DIC->ctrl();
		$this->actions = $actions;
		$this->txt = $txt;
		$this->obj_id = $obj_id;
		$this->parent_obj = $parent_obj;

		$this->possible_cmd = array(
				"CMD_SHOW" => self::CMD_SHOW
				,"CMD_ADD" => self::CMD_ADD
				,"CMD_DELETE_CONFIRM" => self::CMD_DELETE_CONFIRM
				,"CMD_DELETE_SELECTED_CONFIRM" => self::CMD_DELETE_SELECTED_CONFIRM
			);
	}

	public function executeCommand()
	{
		$cmd = $this->g_ctrl->getCMD(self::CMD_SHOW);

		switch ($cmd) {
			case self::CMD_SHOW:
			case self::CMD_ADD:
			case self::CMD_DELETE:
			case self::CMD_DELETE_CONFIRM:
			case self::CMD_DELETE_SELECTED_CONFIRM:
				$this->$cmd();
				break;
			default:
				throw new \Exception("ilTalentAssessmentObserverGUI:: Unknown command ".$cmd);
		}
	}

	public function addObserver(array $user_ids = null, $a_status = null)
	{
		if (!sizeof($user_ids)) {
			\ilUtil::sendFailure($this->txt("no_users_selected"), true);
			$this->g_ctrl->redirect($this, self::CMD_SHOW);
		}

		foreach ($user_ids as $user_id) {
			$this->actions->assignObserver($user_id, $this->obj_id);
		}

		\ilUtil::sendSuccess($this->txt("add_observer_success"), true);
		$this->g_ctrl->redirect($this, self::CMD_SHOW);
	}

	protected function showObserver()
	{
		$gui = new Observer\ilObserverTableGUI($this);
		$this->g_tpl->setContent($gui->getHTML());
	}

	protected function deleteSelectedObserverConfirm()
	{
		$usr_ids = $_POST["id"];
		if (count($usr_ids) == 0) {
			\ilUtil::sendFailure($this->txt("no_user_selected"), true);
			$this->g_ctrl->redirect($this, self::CMD_SHOW);
		}

		$this->showConfirmationGUI($this->txt("confirm_delete_observers"), $usr_ids);
	}

	protected function deleteObserverConfirm()
	{
		$usr_id = $_GET["usr_id"];

		if (is_null($usr_id)) {
			\ilUtil::sendFailure($this->txt("no_user_selected"), true);
			$this->g_ctrl->redirect($this, self::CMD_SHOW);
		}

		$this->showConfirmationGUI($this->txt("confirm_delete_observer"), [$usr_id]);
	}

	protected function showConfirmationGUI($title, array $usr_ids)
	{
		$confirmation = new \ilConfirmationGUI();
		$confirmation->setFormAction($this->g_ctrl->getFormAction($this, $cmd));
		$confirmation->setHeaderText($title);
		$confirmation->setConfirm($this->txt("delete"), self::CMD_DELETE);
		$confirmation->setCancel($this->txt("cancel"), self::CMD_SHOW);

		foreach ($usr_ids as $usr_id) {
			$name_data = ilObjUser::_lookupName($usr_id);
			$votes = $this->actions->getNumberOfVotes($usr_id);
			$confirmation->addItem("usr_ids[]", $usr_id, sprintf($this->txt("observer_delete_row"), $name_data["lastname"], $name_data["firstname"], $name_data["login"], $votes));
		}

		$this->g_tpl->setContent($confirmation->getHTML());
	}

	protected function deleteObservers()
	{
		$usr_ids = $_POST["usr_ids"];

		foreach ($usr_ids as $usr_id) {
			$this->actions->deassignObserver($usr_id, $this->obj_id);
		}
		\ilUtil::sendSuccess($this->txt("delete_observer_success"), true);
		$this->g_ctrl->redirect($this, self::CMD_SHOW);
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
}
