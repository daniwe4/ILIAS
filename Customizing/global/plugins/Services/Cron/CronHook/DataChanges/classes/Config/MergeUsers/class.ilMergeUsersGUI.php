<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\DataChanges\Config\Checker;
use CaT\Plugins\DataChanges\Config\DataChangeHelper;
use CaT\Plugins\DataChanges\Config\Log\DB;
use CaT\Plugins\DataChanges\Config\MergeUsers\DB as MergeUserDB;

class ilMergeUsersGUI
{
	use Checker;

	const CMD_SHOW = "show";
	const CMD_MERGE_USERS = "mergeUsers";
	const CMD_MERGE_USERS_CONFIRM = "mergeUsersConfirm";
	const CMD_AUTOCOMPLETE = "userfieldAutocomplete";

	const F_USER_TO_DEACTIVATE = "user_to_deactivate";
	const F_USER_TO_ACTIVATE = "user_to_activate";
	const F_REASON = "reason";

	const ACTION = "migrate_users";

	const MSG_FAILURE = "failure";
	const MSG_SUCCESS = "success";
	const MSG_INFO = "info";

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilGlobalTemplateInterface
	 */
	protected $tpl;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var Closure
	 */
	protected $txt;

	/**
	 * @var DB
	 */
	protected $log_db;

	/**
	 * @var MergeUserDB
	 */
	protected $merge_user_db;

	/**
	 * @var DataChangeHelper
	 */
	protected $helper;

	public function __construct(
		ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
		ilObjUser $user,
		Closure $txt,
		DB $log_db,
		MergeUserDB $merge_user_db,
		DataChangeHelper $helper
	) {
		$this->ctrl = $ctrl;
		$this->tpl = $tpl;
		$this->user = $user;
		$this->txt = $txt;
		$this->log_db = $log_db;
		$this->merge_user_db = $merge_user_db;
		$this->helper = $helper;
	}

	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		switch ($cmd) {
			case self::CMD_SHOW:
				$this->show();
				break;
			case self::CMD_MERGE_USERS:
				$this->mergeUsers();
				break;
			case self::CMD_AUTOCOMPLETE:
				$this->helper->userfieldAutocomplete();
				break;
			case self::CMD_MERGE_USERS_CONFIRM:
				$this->mergeUsersConfirm();
				break;
			default:
				throw new Exception("Unknown command: ".$cmd);
		}
	}

	protected function show(ilPropertyFormGUI $form = null)
	{
		if (is_null($form)) {
			$form = $this->getForm();
		}

		$this->tpl->setContent($form->getHtml());
	}

	protected function getForm() : ilPropertyFormGUI
	{
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->txt("merge_users"));
		$form->setShowTopButtons(true);

		$ti = new ilTextInputGUI($this->txt("user_to_deactivate"), self::F_USER_TO_DEACTIVATE);
		$ti->setInfo($this->txt('user_to_deactivate_info'));
		$ti->setRequired(true);
		$ti->setDataSource($this->ctrl->getLinkTarget($this, self::CMD_AUTOCOMPLETE, "", false, false));
		$form->addItem($ti);

		$ti = new ilTextInputGUI($this->txt("user_to_activate"), self::F_USER_TO_ACTIVATE);
		$ti->setInfo($this->txt('user_to_activate_info'));
		$ti->setRequired(true);
		$ti->setDataSource($this->ctrl->getLinkTarget($this, self::CMD_AUTOCOMPLETE, "", false, false));
		$form->addItem($ti);

		$ta = new ilTextAreaInputGUI($this->txt("reason"), self::F_REASON);
		$ta->setRequired(true);
		$form->addItem($ta);

		$form->addCommandButton(self::CMD_MERGE_USERS_CONFIRM, $this->txt('update'));
		$form->addCommandButton(self::CMD_SHOW, $this->txt('cancel'));

		return $form;
	}

	protected function checkInputs(\ilPropertyFormGUI $form) : bool
	{
		$post = $_POST;
		$error = [];
		$ret = true;

		if (! $form->checkInput()) {
			$ret = false;
		}

		if (! $this->validateUserLogin($post[self::F_USER_TO_DEACTIVATE])) {
 			$error[self::F_USER_TO_DEACTIVATE] = $this->txt('error_unknown_user');
 			$ret = false;
		}

		if (! $this->validateUserLogin($post[self::F_USER_TO_ACTIVATE])) {
			$error[self::F_USER_TO_ACTIVATE] = $this->txt('error_unknown_user');
			$ret = false;
		}

		foreach ($error as $field => $msg) {
			ilUtil::sendFailure($this->txt('error'));
			$item = $form->getItemByPostVar($field);
			$item->setAlert($msg);
		}

		return $ret;
	}

	protected function mergeUsersConfirm()
	{
		$post = $_POST;
		$form = $this->getForm();

		if (! $this->checkInputs($form)) {
			$form->setValuesByPost();
			$this->show($form);
			return;
		}

		$login_active = $post[self::F_USER_TO_ACTIVATE];
		$login_deactive = $post[self::F_USER_TO_DEACTIVATE];
		$reason = htmlspecialchars($post[self::F_REASON]);
		$user_to_activate_id = $this->helper->getUserIdByLogin($login_active);
		$user_to_deactivate_id = $this->helper->getUserIdByLogin($login_deactive);

		if ($this->checkForOpenCourses($user_to_deactivate_id)) {
			$form->setValuesByPost();
			$this->show($form);
			return;
		}

		if ($this->checkForSameBookedCourses($user_to_activate_id, $user_to_deactivate_id)) {
			$form->setValuesByPost();
			$this->show($form);
			return;
		}

		$form = new ilConfirmationGUI();
		$form->setHeaderText($this->txt('merge_info_text'));
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->addItem(
			self::F_USER_TO_DEACTIVATE,
			$user_to_deactivate_id,
			$this->txt('user_to_deactivate').': '.$login_deactive
		);
		$form->addItem(
			self::F_USER_TO_ACTIVATE,
			$user_to_activate_id,
			$this->txt('user_to_activate').': '.$login_active
		);
		$form->addItem(
			self::F_REASON,
			$reason,
			$this->txt('reason').': '.$reason
		);
		$form->setConfirm($this->txt("update"), self::CMD_MERGE_USERS);
		$form->setCancel($this->txt("cancel"), self::CMD_SHOW);

		$this->tpl->setContent($form->getHTML());
	}

	protected function mergeUsers()
	{
		$post = $_POST;
		$user_to_activate_id = (int)$post[self::F_USER_TO_ACTIVATE];
		$user_to_deactivate_id = (int)$post[self::F_USER_TO_DEACTIVATE];
		if (! $this->merge($user_to_activate_id, $user_to_deactivate_id)) {
			return;
		}

		$reason = $post[self::F_REASON];
		$this->log_db->create(
			$this->txt(self::ACTION),
			$user_to_activate_id,
			(int)$this->user->getId(),
			$reason
		);

		$messages[] = $this->txt('success_merge');
		$messages[] = sprintf($this->txt('info_deactivate_user'), ilObjUser::_lookupLogin($user_to_deactivate_id));
		$this->reply(
			self::MSG_SUCCESS,
			join('<br>', $messages),
			null
		);

		$this->ctrl->redirect($this, self::CMD_SHOW);
	}

	protected function checkForOpenCourses(int $user_id) : bool
	{
		$crs_ids = $this->merge_user_db->getOpenCourses($user_id);

		if (count($crs_ids) > 0) {
			$this->reply(
				self::MSG_FAILURE,
				$this->txt('error_user_has_open_courses'),
				ilObjUser::_lookupLogin($user_id),
				join('<br />', $crs_ids)
			);
			return true;
		}

		return false;
	}

	protected function checkForSameBookedCourses(
		int $user_to_activate_id,
		int $user_to_deactivate_id
	) : bool {
		$crs_ids = $this->merge_user_db->getSameBookedCourses(
			$user_to_deactivate_id,
			$user_to_activate_id
		);

		if (count($crs_ids) > 0) {
			$this->reply(
				self::MSG_FAILURE,
				$this->txt('error_same_courses'),
				join('<br />', $crs_ids)
			);
			return true;
		}

		return false;
	}

	protected function merge(
		int $user_to_activate_id,
		int $user_to_deactivate_id
	) : bool {
		$this->deactivateUser($user_to_deactivate_id);
		$merge = $this->merge_user_db->mergeUserData($user_to_deactivate_id, $user_to_activate_id);

		if (! $merge) {
			$messages[] = $this->txt('info_deactivate_user');
			$messages[] = $this->txt('info_nothing_to_do');
			$this->reply(
				self::MSG_INFO,
				join('<br>', $messages)
			);
			return false;
		}

		return true;
	}


	protected function reply(string $type, ...$vars)
	{
		$msg = call_user_func_array('sprintf', $vars);

		switch ($type) {
			case self::MSG_SUCCESS:
				ilUtil::sendSuccess($msg, true);
				break;
			case self::MSG_INFO:
				ilUtil::sendInfo($msg, true);
				break;
			case self::MSG_FAILURE:
				ilUtil::sendFailure($msg, true);
				break;
		}
	}

	protected function isUserActive(int $user_id) : bool
	{
		return ilObjUser::_lookupActive($user_id);
	}

	protected function deactivateUser(int $user_id)
	{
		$user = ilObjectFactory::getInstanceByObjId($user_id);
		$user->setActive(false);
		$user->update();
	}

	protected function txt(string $code) : string
	{
		return call_user_func($this->txt, $code);
	}
}