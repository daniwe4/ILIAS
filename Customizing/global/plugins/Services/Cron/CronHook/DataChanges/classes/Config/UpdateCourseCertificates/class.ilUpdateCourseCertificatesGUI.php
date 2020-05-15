<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\DataChanges\Config\Checker;
use CaT\Plugins\DataChanges\Config\DataChangeHelper;
use CaT\Plugins\DataChanges\Config\Log\DB;

class ilUpdateCourseCertificatesGUI
{
	use Checker;

	const CMD_SHOW = "show";
	const CMD_UPDATE_COURSE_CERTIFICATE = "updateCourseCertificate";
	const CMD_CANCEL = "cancel";

	const F_CRS_REF_ID = "crs_ref_id";
	const F_REASON = "reason";

	const ACTION = "update_course_certificates";

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
			case self::CMD_UPDATE_COURSE_CERTIFICATE:
				$this->updateCourseCertificate();
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
		$form->setTitle($this->txt("update_course_certificate"));
		$form->setShowTopButtons(true);

		$ni = new ilNumberInputGUI($this->txt("course_ref_id"), self::F_CRS_REF_ID);
		$ni->setRequired(true);
		$form->addItem($ni);

		$ta = new ilTextAreaInputGUI($this->txt("reason"), self::F_REASON);
		$ta->setRequired(true);
		$form->addItem($ta);

		$form->addCommandButton(self::CMD_UPDATE_COURSE_CERTIFICATE, $this->txt('update'));
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

		foreach ($error as $field => $msg) {
			ilUtil::sendFailure($this->txt('error'));
			$item = $form->getItemByPostVar($field);
			$item->setAlert($msg);
		}

		return $ret;
	}

	protected function updateCourseCertificate()
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

		$reason = htmlspecialchars($post[self::F_REASON]);

		$crs = ilObjectFactory::getInstanceByRefId($crs_ref_id);
		$member_ids = $crs->getMembersObject()->getMembers();
		$member_ids = array_map(
			function($id) {
				return (int)$id;
			},
			$member_ids
		);

		if (count($member_ids) == 0) {
			ilUtil::sendInfo(sprintf($this->txt('info_no_members_found'), $crs_ref_id));
			$form->setValuesByPost();
			$this->show($form);
			return;
		}

		$passed = array_filter($member_ids, function ($member) use ($crs_id) {
			return $this->helper->hasPassed($member, $crs_id);
		});

		$not_passed = array_diff($member_ids, $passed);

		foreach ($passed as $pass) {
			$user_id = (int)$pass;

			$this->helper->updateUserCertificateForCourse($user_id, $crs_id);

			$this->log_db->create(
				$this->txt(self::ACTION),
				$user_id,
				(int)$this->user->getId(),
				$reason
			);
		}

		if (count($passed)) {
			ilUtil::sendSuccess($this->txt('success_update_certificates'), true);
		}

		if (count($not_passed) > 0) {
			$not_passed_msg = '';
			foreach ($not_passed as $not_pass) {
				$login = ilObjUser::_lookupLogin($not_pass);
				$not_passed_msg .= sprintf($this->txt('error_not_passed'), $login) . '<br />';
			}
			$not_passed_msg = preg_replace('/<br \/>$/', '', $not_passed_msg);

			ilUtil::sendFailure($not_passed_msg);
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