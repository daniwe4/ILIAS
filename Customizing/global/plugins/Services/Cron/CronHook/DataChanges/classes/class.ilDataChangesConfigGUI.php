<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\DataChanges\DI;
use Pimple\Container;

/**
 * @ilCtrl_Calls ilDataChangesConfigGUI: ilUpdateUserCertificateGUI
 * @ilCtrl_Calls ilDataChangesConfigGUI: ilUpdateCourseCertificatesGUI
 * @ilCtrl_Calls ilDataChangesConfigGUI: ilRemoveUserFromCourseGUI
 * @ilCtrl_Calls ilDataChangesConfigGUI: ilMergeUsersGUI
 * @ilCtrl_Calls ilDataChangesConfigGUI: ilRemoveCourseFromHistoryGUI
 * @ilCtrl_Calls ilDataChangesConfigGUI: ilReopenCourseMemberOnlineSeminarGUI
 * @ilCtrl_Calls ilDataChangesConfigGUI: ilBWVUDFGUI
 * @ilCtrl_Calls ilDataChangesConfigGUI: ilDCSecurityGUI
 * @ilCtrl_Calls ilDataChangesConfigGUI: ilDCLogGUI
 * @ilCtrl_isCalledBy ilDataChangesConfigGUI: ilObjComponentSettingsGUI
 */
class ilDataChangesConfigGUI extends ilPluginConfigGUI
{
	use DI;

	const CMD_CONFIGURE = "configure";

	const ROOT_LOGIN = "root";

	const TAB_UPDATE_USER_CERTIFICATE = "update_user_certificate";
	const TAB_UPDATE_COURSE_CERTIFICATES = "update_course_certificates";
	const TAB_REMOVE_USER_FROM_COURSE = "remove_user_from_course";
	const TAB_MERGE_USERS = "merge_users";
	const TAB_REMOVE_COURSE_FROM_HISTORY = "remove_cours_from_history";
	const TAB_REOPEN_COURSE_MEMBER_ONLINE_SEMINAR = "reopen_course_member_online_seminar";
	const TAB_BWV_UDF = "bwv_udf";
	const TAB_SECURITY = "security";
	const TAB_LOG = "log";

	public function performCommand($cmd)
	{
		if (! $this->checkUserName()) {
			ilUtil::sendFailure($this->getPluginObject()->txt("no_permission"), true);
			$this->getDIC()["ilCtrl"]->redirectToURL($this->getDIC()["admin.plugin.link"]);
		}

		$this->setTabs();

		$next_class = $this->getDIC()["ilCtrl"]->getNextClass();
		switch($next_class) {
			case "ilupdateusercertificategui":
				$this->forwardUpdateUserCertificate();
				break;
			case "ilupdatecoursecertificatesgui":
				$this->forwardUpdateCourseCertificates();
				break;
			case "ilremoveuserfromcoursegui":
				$this->forwardRemoveUserFromCourse();
				break;
			case "ilmergeusersgui":
				$this->forwardMergeUsers();
				break;
			case "ilremovecoursefromhistorygui":
				$this->forwardRemoveCourseFromHistory();
				break;
			case "ilreopencoursememberonlineseminargui":
				$this->forwardReopenCourseMemberOnlineSeminar();
				break;
			case "ilbwvudfgui":
				$this->forwardBWVUDF();
				break;
			case "ildcsecuritygui":
				$this->forwardSecurityGUI();
				break;
			case "ildcloggui":
				$this->forwardLogGUI();
				break;
			default:
				switch ($cmd) {
				case self::CMD_CONFIGURE:
					$this->forwardSecurityGUI();
					break;
				default:
					throw new Exception("ilDataChangesConfigGUI:: Unknown command: ".$cmd);
			}
		}
	}

	protected function forwardUpdateUserCertificate()
	{
		$this->getDIC()['ilTabs']->activateTab(self::TAB_UPDATE_USER_CERTIFICATE);
		$gui = $this->getDIC()['update.user.certificate.gui'];
		$this->getDIC()["ilCtrl"]->forwardCommand($gui);
	}

	protected function forwardUpdateCourseCertificates()
	{
		$this->getDIC()['ilTabs']->activateTab(self::TAB_UPDATE_COURSE_CERTIFICATES);
		$gui = $this->getDIC()['update.course.certificates.gui'];
		$this->getDIC()["ilCtrl"]->forwardCommand($gui);
	}

	protected function forwardRemoveUserFromCourse()
	{
		$this->getDIC()['ilTabs']->activateTab(self::TAB_REMOVE_USER_FROM_COURSE);
		$gui = $this->getDIC()['remove.user.from.course.gui'];
		$this->getDIC()["ilCtrl"]->forwardCommand($gui);
	}

	protected function forwardMergeUsers()
	{
		$this->getDIC()['ilTabs']->activateTab(self::TAB_MERGE_USERS);
		$gui = $this->getDIC()['merge.users.gui'];
		$this->getDIC()["ilCtrl"]->forwardCommand($gui);
	}

	protected function forwardRemoveCourseFromHistory()
	{
		$this->getDIC()['ilTabs']->activateTab(self::TAB_REMOVE_COURSE_FROM_HISTORY);
		$gui = $this->getDIC()['remove.course.from.history.gui'];
		$this->getDIC()["ilCtrl"]->forwardCommand($gui);
	}

	protected function forwardReopenCourseMemberOnlineSeminar()
	{
		$this->getDIC()['ilTabs']->activateTab(self::TAB_REOPEN_COURSE_MEMBER_ONLINE_SEMINAR);
		$gui = $this->getDIC()['reopen.course.member.online_seminar.gui'];
		$this->getDIC()["ilCtrl"]->forwardCommand($gui);
	}

	protected function forwardBWVUDF()
	{
		$this->getDIC()['ilTabs']->activateTab(self::TAB_BWV_UDF);
		$gui = $this->getDIC()['bwv.udf.gui'];
		$this->getDIC()["ilCtrl"]->forwardCommand($gui);
	}

	protected function forwardSecurityGUI()
	{
		$this->getDIC()["ilTabs"]->activateTab(self::TAB_SECURITY);
		$gui = $this->getDIC()["security.gui"];
		$this->getDIC()["ilCtrl"]->forwardCommand($gui);
	}

	protected function forwardLogGUI()
	{
		$this->getDIC()["ilTabs"]->activateTab(self::TAB_LOG);
		$gui = $this->getDIC()["log.gui"];
		$this->getDIC()["ilCtrl"]->forwardCommand($gui);
	}

	protected function setTabs()
	{
		$security_link = $this->getDIC()["security.gui.link"];
		$this->getDIC()["ilTabs"]->addTab(
			self::TAB_SECURITY,
			$this->plugin_object->txt("security_settings"),
			$security_link
		);

		$update_user_certificate_link = $this->getDIC()["update.user.certificate.gui.link"];
		$this->getDIC()["ilTabs"]->addTab(
			self::TAB_UPDATE_USER_CERTIFICATE,
			$this->plugin_object->txt("update_user_certificate"),
			$update_user_certificate_link
		);

		$update_course_certificates_link = $this->getDIC()["update.course.certificates.gui.link"];
		$this->getDIC()["ilTabs"]->addTab(
			self::TAB_UPDATE_COURSE_CERTIFICATES,
			$this->plugin_object->txt("update_course_certificate"),
			$update_course_certificates_link
		);

		$remove_user_from_course_link = $this->getDIC()["remove.user.from.course.gui.link"];
		$this->getDIC()["ilTabs"]->addTab(
			self::TAB_REMOVE_USER_FROM_COURSE,
			$this->plugin_object->txt("remove_user_from_course"),
			$remove_user_from_course_link
		);

		$merge_users_link = $this->getDIC()["merge.users.gui.link"];
		$this->getDIC()["ilTabs"]->addTab(
			self::TAB_MERGE_USERS,
			$this->plugin_object->txt("merge_users"),
			$merge_users_link
		);

		$remove_course_from_history_link = $this->getDIC()["remove.course.from.history.gui.link"];
		$this->getDIC()["ilTabs"]->addTab(
			self::TAB_REMOVE_COURSE_FROM_HISTORY,
			$this->plugin_object->txt("remove_course_from_history"),
			$remove_course_from_history_link
		);

		$reopen_course_member_online_seminar_link = $this->getDIC()["reopen.course.member.online_seminar.gui.link"];
		$this->getDIC()["ilTabs"]->addTab(
			self::TAB_REOPEN_COURSE_MEMBER_ONLINE_SEMINAR,
			$this->plugin_object->txt("reopen_course_member_online_seminar"),
			$reopen_course_member_online_seminar_link
		);

		$bwv_udf_link = $this->getDIC()['bwv.udf.gui.link'];
		$this->getDIC()["ilTabs"]->addTab(
			self::TAB_BWV_UDF,
			$this->plugin_object->txt("bwv_udf"),
			$bwv_udf_link
		);

		$log_link = $this->getDIC()["log.gui.link"];
		$this->getDIC()["ilTabs"]->addTab(
			self::TAB_LOG,
			$this->plugin_object->txt("log"),
			$log_link
		);
	}

	protected function checkUserName()
	{
		$security_db = $this->getDIC()["security.db"];
		if(!$security_db->loginEnabled($this->getDIC()["plugin.id"])) {
			return true;
		}

		$username = $this->getDIC()["ilUser"]->getLogin();
		if(
			$username == self::ROOT_LOGIN ||
			$security_db->checkUsername($username, $this->getDIC()["plugin.id"])
		) {
			return true;
		}

		return false;
	}

	protected function getDIC() : Container
	{
		global $DIC;
		return $this->getPluginDIC($this->plugin_object, $DIC);
	}
}