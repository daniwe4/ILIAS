<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\DataChanges\Config\Checker;
use CaT\Plugins\DataChanges\Config\DataChangeHelper;
use CaT\Plugins\DataChanges\Config\Log\DB;

class ilUpdateUserCertificateGUI
{
	use Checker;

	const CMD_SHOW = "show";
	const CMD_UPDATE_USER_CERTIFICATE = "updateUserCertificate";
	const CMD_CANCEL = "cancel";
	const CMD_AUTOCOMPLETE = "userfieldAutocomplete";

	const F_USERNAMES = "usernames";
	const F_CRS_REF_ID = "crs_ref_id";
	const F_REASON = "reason";

	const ACTION = "update_user_certificate";

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
	 * @var DataChangeHelper
	 */
	protected $helper;

	public function __construct(
		ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
		ilObjUser $user,
		Closure $txt,
		DB $log_db,
		DataChangeHelper $helper
	) {
		$this->ctrl = $ctrl;
		$this->tpl = $tpl;
		$this->user = $user;
		$this->txt = $txt;
		$this->log_db = $log_db;
		$this->helper = $helper;
	}

	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		switch ($cmd) {
			case self::CMD_SHOW:
				$this->show();
				break;
			case self::CMD_UPDATE_USER_CERTIFICATE:
				$this->updateUserCertificate();
				break;
			case self::CMD_AUTOCOMPLETE:
				$this->helper->userfieldAutocomplete();
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
		$form->setTitle($this->txt("update_user_certificate"));
		$form->setShowTopButtons(true);

		$ni = new ilNumberInputGUI($this->txt("course_ref_id"), self::F_CRS_REF_ID);
		$ni->setRequired(true);
		$form->addItem($ni);

		$ti = new ilTextInputGUI($this->txt("usernames"), self::F_USERNAMES);
		$ti->setMulti(true);
		$ti->setRequired(true);
		$ti->setDataSource($this->ctrl->getLinkTarget($this, self::CMD_AUTOCOMPLETE, "", false, false));
		$form->addItem($ti);

		$ta = new ilTextAreaInputGUI($this->txt("reason"), self::F_REASON);
		$ta->setRequired(true);
		$form->addItem($ta);

		$form->addCommandButton(self::CMD_UPDATE_USER_CERTIFICATE, $this->txt('update'));
		$form->addCommandButton(self::CMD_SHOW, $this->txt('cancel'));

		return $form;
	}

	protected function checkInputs(ilPropertyFormGUI $form) : bool
	{
		$post = $_POST;
		$error = [];
		$ret = true;

		if (! $form->checkInput()) {
			$ret = false;
		}

		$crs_ref_id = (int)$post[self::F_CRS_REF_ID];
		if ($crs_ref_id === 0) {
			$error[self::F_CRS_REF_ID] = $this->txt('error_ref_id_equals_null');
			$ret = false;
		}

		if (! $this->isExistingCourse($crs_ref_id)) {
			$error[self::F_CRS_REF_ID] = $this->txt('error_unknown_crs');
			$ret = false;
		}

		$usernames = $post[self::F_USERNAMES];
		foreach ($usernames as $username) {
			if (! $this->validateUserLogin($username)) {
				$error[self::F_USERNAMES][] = sprintf($this->txt('error_unknown_user'), $username);
				$ret = false;
			}
		}

		foreach ($error as $field => $msg) {
			ilUtil::sendFailure($this->txt('error'));
			$item = $form->getItemByPostVar($field);
			if ($field == self::F_USERNAMES) {
				$msg = '<br/>'.join('<br/>', $msg);
			}
			$item->setAlert($msg);
		}

		return $ret;
	}

	protected function updateUserCertificate()
	{
		$post = $_POST;
		$form = $this->getForm();

		if (! $this->checkInputs($form)) {
			$form->setValuesByPost();
			$this->show($form);
			return;
		}

		$crs_ref_id = (int)$post[self::F_CRS_REF_ID];
		$crs_id = (int)ilObject::_lookupObjectId($crs_ref_id);

		if (! $this->helper->isCertificateActivated($crs_id)) {
			ilUtil::sendFailure($this->txt('error_certificate_not_activated'));
			$form->setValuesByPost();
			$this->show($form);
			return;
		}

		$usernames = $post[self::F_USERNAMES];
		$user_ids = ilObjUser::_lookupId($usernames);
		$reason = $post[self::F_REASON];

		$passed_members = array_filter($user_ids, function ($user_id) use ($crs_ref_id, $crs_id) {
			return
				$this->helper->isMember((int)$user_id, $crs_ref_id) &&
				$this->helper->hasPassed((int)$user_id, $crs_id)
			;
		});

		$no_passed_members = array_diff($user_ids, $passed_members);

		foreach ($passed_members as $passed_member) {
			$user_id = (int)$passed_member;

			$this->helper->updateUserCertificateForCourse($user_id, $crs_id);

			$this->log_db->create(
				$this->txt(self::ACTION),
				$user_id,
				(int)$this->user->getId(),
				$reason
			);
		}

		if (count($passed_members) > 0) {
			ilUtil::sendSuccess($this->txt('success_update_certificate'), true);
		}

		if (count($no_passed_members) > 0) {
			$not_passed_member_msg = '';
			foreach ($no_passed_members as $no_passed_member) {
				$login = ilObjUser::_lookupLogin($no_passed_member);
				$not_passed_member_msg .= sprintf($this->txt('error_not_passed_member'), $login) . '<br />';
			}
			$not_passed_member_msg = preg_replace('/<br \/>$/', '', $not_passed_member_msg);

			ilUtil::sendFailure($not_passed_member_msg);
			$form->setValuesByPost();
			$this->show($form);
			return;
		}

		$this->ctrl->redirect($this, self::CMD_SHOW);
	}

	protected function txt(string $code) : string
	{
		return call_user_func($this->txt, $code);
	}
}