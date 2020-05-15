<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\DataChanges\Config\Checker;
use CaT\Plugins\DataChanges\Config\DataChangeHelper;
use CaT\Plugins\DataChanges\Config\Log\DB;
use CaT\Plugins\DataChanges\Config\RemoveCourseFromHistory\DB AS RMCFH_DB;

class ilRemoveCourseFromHistoryGUI
{
	use Checker;

	const CMD_SHOW = "show";
	const CMD_REMOVE_COURSE = "removeCourse";
	const CMD_AUTOCOMPLETE = "userfieldAutocomplete";

	const F_CRS_TITLE= "crs_title";
	const F_USERNAME = "user_name";
	const F_START_DATE = "start_date";
	const F_REASON = "reason";

	const ACTION = "remove_course_from_history";

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
	 * @var RMCFH_DB
	 */
	protected $remove_db;

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
		RMCFH_DB $remove_db,
		DataChangeHelper $helper
	) {
		$this->ctrl = $ctrl;
		$this->tpl = $tpl;
		$this->user = $user;
		$this->txt = $txt;
		$this->log_db = $log_db;
		$this->remove_db = $remove_db;
		$this->helper = $helper;
	}

	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		switch ($cmd) {
			case self::CMD_SHOW:
				$this->show();
				break;
			case self::CMD_REMOVE_COURSE:
				$this->removeCourse();
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
		$form->setTitle($this->txt("remove_course_from_history"));
		$form->setShowTopButtons(true);

		$ti = new ilTextInputGUI($this->txt("course_title"), self::F_CRS_TITLE);
		$ti->setRequired(true);
		$form->addItem($ti);

		$di = new ilDateTimeInputGUI($this->txt("start_date"), self::F_START_DATE);
		$di->setRequired(true);
		$form->addItem($di);

		$ti = new ilTextInputGUI($this->txt("username"), self::F_USERNAME);
		$ti->setDataSource($this->ctrl->getLinkTarget(
			$this,
			self::CMD_AUTOCOMPLETE,
			"",
			false,
			false
		));

		$form->addItem($ti);

		$ta = new ilTextAreaInputGUI($this->txt("reason"), self::F_REASON);
		$ta->setRequired(true);
		$form->addItem($ta);

		$form->addCommandButton(self::CMD_REMOVE_COURSE, $this->txt('update'));
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

		$username = $post[self::F_USERNAME];
		if (! is_null($username) && $username != '') {
			if (! $this->validateUserLogin($username)) {
				$error[self::F_USERNAME] = $this->txt('error_unknown_user');
				$ret = false;
			}
		}

		foreach ($error as $field => $msg) {
			ilUtil::sendFailure($this->txt('error'));
			$item = $form->getItemByPostVar($field);
			$item->setAlert($msg);
		}

		return $ret;
	}

	protected function removeCourse()
	{
		$post = $_POST;
		$form = $this->getForm();

		if (! $this->checkInputs($form)) {
			$form->setValuesByPost();
			$this->show($form);

			return;
		}

		$title = $post[self::F_CRS_TITLE];
		$start_date = new DateTime($post[self::F_START_DATE]);

		$courses = $this->remove_db->getDeletedHistCourses($title, $start_date);

		$cnt = count($courses);
		if ($cnt !== 1) {
			$msg = $this->txt('error_course_not_found_for_title');
			if ($cnt > 1) {
				$this->txt('error_double_deleted_courses');
			}
			ilUtil::sendFailure($msg);
			$form->setValuesByPost();
			$this->show($form);
			return;
		}

		if (! is_null($post[self::F_USERNAME]) && $post[self::F_USERNAME] != '') {
			$user_id = $this->helper->getUserIdByLogin($post[self::F_USERNAME]);
		}

		$course = array_shift($courses);
		if (! $this->remove_db->deleteCourseFromHist($course, $user_id)) {
			ilUtil::sendInfo($this->txt('info_no_row_affected'));
			$this->show();

			return;
		}

		$reason = htmlspecialchars($post[self::F_REASON]);

		$this->log_db->create(
			$this->txt(self::ACTION),
			$course->getCrsId(),
			(int)$this->user->getId(),
			$reason
		);

		ilUtil::sendSuccess($this->txt('success_delete_course_from_history'), true);
		$this->ctrl->redirect($this, self::CMD_SHOW);
	}

	protected function txt(string $code) : string
	{
		return call_user_func($this->txt, $code);
	}
}