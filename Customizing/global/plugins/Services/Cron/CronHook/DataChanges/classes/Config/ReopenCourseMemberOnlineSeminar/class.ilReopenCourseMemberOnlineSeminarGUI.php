<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\DataChanges\Config\DataChangeHelper;
use CaT\Plugins\DataChanges\Config\Log\DB;

class ilReopenCourseMemberOnlineSeminarGUI
{
	const CMD_SHOW = "show";
	const CMD_REOPEN_ONLINE_SEMINAR = "reopenOnlineSeminar";
	const CMD_CANCEL = "cancel";

	const F_REF_ID = "ref_id";
	const F_REASON = "reason";
	const ACTION = "reopen_course_member_online_seminar";

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
			case self::CMD_REOPEN_ONLINE_SEMINAR:
				$this->reopenOnlineSeminar();
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
		$form->setTitle($this->txt("reopen_course_member_online_seminar"));
		$form->setShowTopButtons(true);

		$ni = new ilNumberInputGUI($this->txt("ref_id"), self::F_REF_ID);
		$ni->setRequired(true);
		$form->addItem($ni);

		$ta = new ilTextAreaInputGUI($this->txt("reason"), self::F_REASON);
		$ta->setRequired(true);
		$form->addItem($ta);

		$form->addCommandButton(self::CMD_REOPEN_ONLINE_SEMINAR, $this->txt('update'));
		$form->addCommandButton(self::CMD_SHOW, $this->txt('cancel'));

		return $form;
	}

	protected function reopenOnlineSeminar()
	{
		$post = $_POST;

		$form = $this->getForm();
		if (!$form->checkInput()) {
			$form->setValuesByPost();
			$this->show($form);

			return;
		}

		$ref_id = (int)$post[self::F_REF_ID];
		$obj = $this->getObjectForRefId($ref_id);

		if (is_null($obj)) {
			$this->error($form, self::F_REF_ID, $this->txt('error_ref_id_no_object'));
		}

		switch ($obj->getType()) {
			case 'xcmb':
				$fnc = function ($s) {
					return $s->withClosed(false);
				};
				break;
			case 'xwbr':
				$fnc = function ($s) {
					return $s->withFinished(false);
				};
				break;
			default:
				$this->error($form, self::F_REF_ID, $this->txt('error_only_xwbr_xcmb'));
				return;
		}

		$obj->updateSettings($fnc);
		$obj->update();

		$reason = htmlspecialchars($post['reason']);

		$this->log_db->create(
			$this->txt(self::ACTION),
			$ref_id,
			(int)$this->user->getId(),
			$reason
		);

		ilUtil::sendSuccess($this->txt('success'));
		$this->show();
	}

	protected function error(ilPropertyFormGUI $form, string $field, string $msg)
	{
		ilUtil::sendFailure($this->txt('error'));
		$item = $form->getItemByPostVar($field);
		$item->setAlert($msg);
		$form->setValuesByPost();
		$this->show($form);
	}

	protected function getObjectForRefId(int $ref_id)
	{
		try {
			$obj = ilObjectFactory::getInstanceByRefId($ref_id);
		} catch (Exception $e) {
			return null;
		}

		return $obj;
	}

	protected function txt(string $code) : string
	{
		return call_user_func($this->txt, $code);
	}
}