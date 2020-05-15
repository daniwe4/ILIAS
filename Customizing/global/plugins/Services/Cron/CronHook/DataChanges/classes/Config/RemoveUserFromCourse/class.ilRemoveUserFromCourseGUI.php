<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\DataChanges\Config\Checker;
use CaT\Plugins\DataChanges\Config\DataChangeHelper;
use CaT\Plugins\DataChanges\Config\RemoveUserFromCourse\DB;
use CaT\Plugins\DataChanges\Config\Log\DB as LogDB;
use CaT\Plugins\DataChanges\Config\UDF\DB as UDFDB;


class ilRemoveUserFromCourseGUI
{
	use Checker;

	const CMD_SHOW = "show";
	const CMD_REMOVE_USER = "removeUser";
	const CMD_AUTOCOMPLETE = "userfieldAutocomplete";

	const F_USERNAMES = "usernames";
	const F_CRS_REF_ID = "crs_ref_id";
	const F_REASON = "reason";

	const ACTION = "remove_user_from_course";

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
	 * @var ilRbacAdmin
	 */
	protected $rbacadmin;

	/**
	 * @var ilRbacReview
	 */
	protected $rbacreview;

	/**
	 * @var Closure
	 */
	protected $txt;

	/**
	 * @var DB
	 */
	protected $db;

	/**
	 * @var LogDB
	 */
	protected $log_db;

	/**
	 * @var UDFDB
	 */
	protected $udf_db;

	/**
	 * @var DataChangeHelper
	 */
	protected $helper;

	public function __construct(
		ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
		ilObjUser $user,
		ilRbacAdmin $rbacadmin,
		ilRbacReview $rbacreview,
		Closure $txt,
		DB $db,
		LogDB $log_db,
		UDFDB $udf_db,
		DataChangeHelper $helper
	) {
		$this->ctrl = $ctrl;
		$this->tpl = $tpl;
		$this->user = $user;
		$this->rbacadmin = $rbacadmin;
		$this->rbacreview = $rbacreview;
		$this->txt = $txt;
		$this->db = $db;
		$this->log_db = $log_db;
		$this->udf_db = $udf_db;
		$this->helper = $helper;
	}

	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		switch ($cmd) {
			case self::CMD_SHOW:
				$this->show();
				break;
			case self::CMD_REMOVE_USER:
				$this->removeUser();
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
		$form->setTitle($this->txt("remove_user_from_course"));
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

		$form->addCommandButton(self::CMD_REMOVE_USER, $this->txt('update'));
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

	protected function removeUser()
	{
		$post = $_POST;
		$form = $this->getForm();

		if (! $this->checkInputs($form)) {
			$form->setValuesByPost();
			$this->show($form);
			return;
		}

		$crs_ref_id = (int)$post[self::F_CRS_REF_ID];

		if (! $this->helper->isCourseStarted($crs_ref_id)) {
			ilUtil::sendFailure($this->txt('error_course_not_started'));
			$form->setValuesByPost();
			$this->show($form);
			return;
		}

		$usernames = $post[self::F_USERNAMES];
		$reason = $post[self::F_REASON];
		$user_ids = ilObjUser::_lookupId($usernames);
		$crs_id = ilObject::_lookupObjectId((int)$crs_ref_id);
		$type = ilObject::_lookupType($crs_id);
		$msg = [];

		$members = array_filter($user_ids, function ($user_id) use ($crs_ref_id) {
			return $this->helper->isMember((int)$user_id, $crs_ref_id);
		});

		$no_members = array_diff($user_ids, $members);

		foreach ($members as $member) {
			$user_id = (int)$member;

			$this->db->deleteUsersFromHistForCrs($user_id, $crs_id);

			if ($this->db->hasUserAnnouncedIddTimes($user_id, $crs_id)) {
				$msg[] = $this->getIddIds($user_id, $crs_id);
			}

			$this->dropDesktopItem($user_id, $crs_ref_id, $type);
			$this->deleteUserFromCourse($user_id, $crs_ref_id, $crs_id);

			$this->log_db->create(
				$this->txt(self::ACTION),
				$user_id,
				(int)$this->user->getId(),
				$reason
			);
		}

		if (count($msg) > 0) {
			$info = "";
			foreach ($msg as $m) {
				$login = ilObjUser::_lookupLogin($m['user_id']);
				$info .= sprintf(
					$this->txt("info_idd_send"),
					$login,
					$m['bwv_id'],
					$m['booking_id']
				);
			}
			ilUtil::sendFailure($info, true);
		}

		if (count($members) > 0) {
			ilUtil::sendSuccess($this->txt('success_removed_user_from_course'), true);
		}

		if (count($no_members) > 0) {
			$no_member_msg = '';
			foreach ($no_members as $no_member) {
				$login = ilObjUser::_lookupLogin($no_member);
				$no_member_msg .= sprintf($this->txt('error_no_crs_member'), $login) . '<br />';
			}
			$no_member_msg = preg_replace('/<br \/>$/', '', $no_member_msg);

			ilUtil::sendInfo($no_member_msg, true);
		}

		$this->ctrl->redirect($this, self::CMD_SHOW);
	}

	protected function deleteUserFromCourse(int $user_id, int $crs_ref_id, int $crs_id)
	{
		$roles = $this->rbacreview->getRolesOfRoleFolder($crs_ref_id);

		foreach ($roles as $role) {
			$this->rbacadmin->deassignUser($role, $user_id);
		}

		$this->db->deleteUserFromCourse($user_id, $crs_id);
	}

	protected function dropDesktopItem(int $usr_id, int $crs_ref_id, string $type)
	{
		if (ilObjUser::_isDesktopItem($usr_id, $crs_ref_id, $type)) {
			ilObjUser::_dropDesktopItem($usr_id, $crs_ref_id, $type);
		}
	}

	protected function getIddIds(int $user_id, int $crs_id) : array
	{
		$udf_id = $this->udf_db->getUDFFieldIdForBWVID()->getFieldId();
		$booking_id = $this->db->getBookingId($user_id, $crs_id);

		$bwv_id = $this->db->getBwvId($user_id, $udf_id);
		return [
			'user_id' => $user_id,
			'bwv_id' => $bwv_id,
			'booking_id' => $booking_id
		];
	}

	protected function txt(string $code) : string
	{
		return call_user_func($this->txt, $code);
	}
}