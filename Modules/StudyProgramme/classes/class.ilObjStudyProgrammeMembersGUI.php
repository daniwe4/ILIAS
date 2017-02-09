<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */


/**
 * Class ilObjStudyProgrammeMembersGUI
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 *
 * @ilCtrl_Calls ilObjStudyProgrammeMembersGUI: ilStudyProgrammeRepositorySearchGUI
 * @ilCtrl_Calls ilObjStudyProgrammeMembersGUI: ilObjStudyProgrammeIndividualPlanGUI
 * @ilCtrl_Calls ilObjStudyProgrammeMembersGUI: ilObjFileGUI
 */

class ilObjStudyProgrammeMembersGUI
{
	/**
	 * @var ilCtrl
	 */
	public $ctrl;

	/**
	 * @var ilTemplate
	 */
	public $tpl;

	/**
	 * @var ilAccessHandler
	 */
	protected $ilAccess;

	/**
	 * @var ilObjStudyProgramme
	 */
	public $object;

	/**
	 * @var ilLog
	 */
	protected $ilLog;

	/**
	 * @var Ilias
	 */
	public $ilias;

	/**
	 * @var ilLng
	 */
	public $lng;

	/**
	 * @var ilToolbarGUI
	 */
	public $toolbar;

	/**
	 * @var ilObjUser
	 */
	public $user;

	protected $parent_gui;

	public function __construct($a_parent_gui, $a_ref_id)
	{
		global $tpl, $ilCtrl, $ilAccess, $ilToolbar, $ilLocator, $tree, $lng, $ilLog, $ilias, $ilUser;

		$this->ref_id = $a_ref_id;
		$this->parent_gui = $a_parent_gui;
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->ilAccess = $ilAccess;
		$this->ilLocator = $ilLocator;
		$this->tree = $tree;
		$this->toolbar = $ilToolbar;
		$this->ilLog = $ilLog;
		$this->ilias = $ilias;
		$this->lng = $lng;
		$this->user = $ilUser;
		$this->progress_object = array();

		$this->object = null;

		$lng->loadLanguageModule("prg");
	}

	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);


		if ($cmd == "") {
			$cmd = "view";
		}

		# TODO: Check permission of user!!

		switch ($next_class) {
			case "ilstudyprogrammerepositorysearchgui":
				require_once("./Modules/StudyProgramme/classes/class.ilStudyProgrammeRepositorySearchGUI.php");
				$rep_search = new ilStudyProgrammeRepositorySearchGUI();
				$rep_search->setCallback($this, "addUsers");

				$this->ctrl->setReturn($this, "view");
				$this->ctrl->forwardCommand($rep_search);
				return;
			case "ilobjstudyprogrammeindividualplangui":
				require_once("./Modules/StudyProgramme/classes/class.ilObjStudyProgrammeIndividualPlanGUI.php");
				$individual_plan_gui = new ilObjStudyProgrammeIndividualPlanGUI($this, $this->ref_id);
				$this->ctrl->forwardCommand($individual_plan_gui);
				return;
			case false:
				switch ($cmd) {
					case "view":
					case "markAccredited":
					case "markAccreditedMulti":
					case "unmarkAccredited":
					case "unmarkAccreditedMulti":
					case "removeUser":
					case "removeUserMulti":
					case "addUsersWithAcknowledgedCourses":
					case "markNotRelevantMulti":
					case "markRelevantMulti":
					case "updateFromCurrentPlanMulti":
						$cont = $this->$cmd();
						break;
					default:
						throw new ilException("ilObjStudyProgrammeMembersGUI: ".
											  "Command not supported: $cmd");
				}
				break;
			default:
				throw new ilException("ilObjStudyProgrammeMembersGUI: Can't forward to next class $next_class");
		}

		$this->tpl->setContent($cont);
	}

	protected function view()
	{
		require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeMembersTableGUI.php");

		if ($this->getStudyProgramme()->isActive()) {
			$this->initSearchGUI();
		}

		if (!$this->getStudyProgramme()->isActive()) {
			ilUtil::sendInfo($this->lng->txt("prg_no_members_not_active"));
		}

		$prg_id = ilObject::_lookupObjId($this->ref_id);
		$table = new ilStudyProgrammeMembersTableGUI($prg_id, $this->ref_id, $this, "view");
		return $table->getHTML();
	}

	public function addUsers($a_users)
	{
		$prg = $this->getStudyProgramme();

		$completed_courses = array();

		foreach ($a_users as $user_id) {
			$completed_crss = $prg->getCompletedCourses($user_id);
			if ($completed_crss) {
				$completed_courses[$user_id] = $completed_crss;
			}
		}

		if (count($completed_courses) > 0) {
			$this->viewCompletedCourses($completed_courses, $a_users);
			return true;
		}

		$this->_addUsers($a_users);

		$this->ctrl->redirect($this, "view");
	}

	public function viewCompletedCourses($a_completed_courses, $a_users)
	{
		require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeAcknowledgeCompletedCoursesTableGUI.php");

		$tpl = new ilTemplate("tpl.acknowledge_completed_courses.html", true, true, "Modules/StudyProgramme");
		$tpl->setVariable("TITLE", $this->lng->txt("prg_acknowledge_completed_courses"));
		$tpl->setVariable("CAPTION_ADD", $this->lng->txt("btn_next"));
		$tpl->setVariable("CAPTION_CANCEL", $this->lng->txt("cancel"));
		$tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$tpl->setVariable("ADD_CMD", "addUsersWithAcknowledgedCourses");
		$tpl->setVariable("CANCEL_CMD", "view");

		foreach ($a_completed_courses as $user_id => $completed_courses) {
			$names = ilObjUser::_lookupName($user_id);
			$tpl->setCurrentBlock("usr_section");
			$tpl->setVariable("FIRSTNAME", $names["firstname"]);
			$tpl->setVariable("LASTNAME", $names["lastname"]);
			$table = new ilStudyProgrammeAcknowledgeCompletedCoursesTableGUI($this, $user_id, $completed_courses);
			$tpl->setVariable("TABLE", $table->getHTML());
			$tpl->parseCurrentBlock();
		}

		foreach ($a_users as $usr_id) {
			$tpl->setCurrentBlock("usr_ids_section");
			$tpl->setVariable("USR_ID", $usr_id);
			$tpl->parseCurrentBlock();
		}

		$this->tpl->setContent($tpl->get());
	}

	public function addUsersWithAcknowledgedCourses()
	{
		$users = $_POST["users"];
		$assignments = $this->_addUsers($users);

		$completed_programmes = $_POST["courses"];
		foreach ($completed_programmes as $user_id => $prg_ref_ids) {
			$ass_id = $assignments[$user_id]->getId();
			foreach ($prg_ref_ids as $ids) {
				list($prg_ref_id, $crs_id, $crsr_id) = explode(";", $ids);
				$prg = $this->getStudyProgramme($prg_ref_id);
				$progress = $prg->getProgressForAssignment($ass_id);
				$progress->setLPCompleted($crsr_id, $user_id);
			}
		}

		$this->ctrl->redirect($this, "view");
	}

	protected function _addUsers($a_users)
	{
		$prg = $this->getStudyProgramme();

		$assignments = array();

		foreach ($a_users as $user_id) {
			$assignments[$user_id] = $prg->assignUser($user_id);
		}

		if (count($a_users) == 1) {
			ilUtil::sendSuccess($this->lng->txt("prg_added_member"), true);
		}
		if (count($a_users) > 1) {
			ilUtil::sendSuccess($this->lng->txt("prg_added_members"), true);
		}

		return $assignments;
	}

	public function markAccredited()
	{
		require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeUserProgress.php");
		$prgrs_id = $this->getPrgrsId();
		$this->accredit($prgrs_id);
		$this->showSuccessMessage("mark_accredited_success");
		$this->ctrl->redirect($this, "view");
	}

	public function markAccreditedMulti()
	{
		$prgrs_ids = $_POST["prgs_ids"];

		foreach ($prgrs_ids as $key => $prgrs_id) {
			$this->accredit((int)$prgrs_id);
		}

		$this->showSuccessMessage("mark_accredited_multi_success");
		$this->ctrl->redirect($this, "view");
	}

	protected function accredit($prgrs_id)
	{
		$prgrs = $this->getProgressObject($prgrs_id);
		$prgrs->markAccredited($this->user->getId());
	}

	public function unmarkAccredited()
	{
		require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeUserProgress.php");
		$prgrs_id = $this->getPrgrsId();
		$this->deaccredit($prgrs_id);
		$this->showSuccessMessage("unmark_accredited_success");
		$this->ctrl->redirect($this, "view");
	}

	public function unmarkAccreditedMulti()
	{
		$prgrs_ids = $_POST["prgs_ids"];

		foreach ($prgrs_ids as $key => $prgrs_id) {
			$this->deaccredit((int)$prgrs_id);
		}

		$this->showSuccessMessage("unmark_accredited_success");
		$this->ctrl->redirect($this, "view");
	}

	protected function deaccredit($prgrs_id)
	{
		$prgrs = $this->getProgressObject($prgrs_id);
		$prgrs->unmarkAccredited();
	}

	public function markRelevantMulti()
	{
		$prgrs_ids = $_POST["prgs_ids"];

		foreach ($prgrs_ids as $key => $prgrs_id) {
			$prgrs = $this->getProgressObject((int)$prgrs_id);
			if (!$prgrs->isRelevant()) {
				$prgrs->markRelevant($this->user->getId());
			}
		}

		$this->showSuccessMessage("mark_relevant_multi_success");
		$this->ctrl->redirect($this, "view");
	}

	public function markNotRelevantMulti()
	{
		$prgrs_ids = $_POST["prgs_ids"];

		foreach ($prgrs_ids as $key => $prgrs_id) {
			$prgrs = $this->getProgressObject((int)$prgrs_id);

			if ($prgrs_id->isRelevant()) {
				$prgrs->markNotRelevant($this->user->getId());
			}
		}

		$this->showSuccessMessage("mark_not_relevant_multi_success");
		$this->ctrl->redirect($this, "view");
	}

	public function updateFromCurrentPlanMulti()
	{
		$prgrs_ids = $_POST["prgs_ids"];
		$not_updated = array();

		foreach ($prgrs_ids as $key => $prgrs_id) {
			$prgrs = $this->getProgressObject((int)$prgrs_id);
			$ass = $prgrs->getAssignment();
			$prg = $ass->getStudyProgramme();
			if ($prg->getRefId() != $this->ref_id) {
				$not_updated[] = $prgrs_id;
				continue;
			}

			$ass->updateFromProgram();
		}

		if (count($not_updated) == count($prgrs_ids)) {
			$this->showInfoMessage("update_from_current_plan_not_possible");
		} elseif (count($not_updated) > 0) {
			$this->showSuccessMessage("update_from_current_plan_partitial_success");
		} else {
			$this->showSuccessMessage("update_from_current_plan_success");
		}

		$this->ctrl->redirect($this, "view");
	}

	public function removeUser()
	{
		require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeUserProgress.php");
		$prgrs_id = $this->getPrgrsId();
		$this->remove($prgrs_id);
		$this->showSuccessMessage("remove_user_success");
		$this->ctrl->redirect($this, "view");
	}

	protected function removeUserMulti()
	{
		$prgrs_ids = $_POST["prgs_ids"];
		$not_removed = array();

		foreach ($prgrs_ids as $key => $prgrs_id) {
			try {
				$this->remove((int)$prgrs_id);
			} catch (ilException $e) {
				$not_removed[] = $prgrs_id;
			}
		}

		if (count($not_removed) == count($prgrs_ids)) {
			$this->showInfoMessage("remove_users_not_possible");
		} elseif (count($not_removed) > 0) {
			$this->showSuccessMessage("remove_users_partitial_success");
		} else {
			$this->showSuccessMessage("remove_users_success");
		}

		$this->ctrl->redirect($this, "view");
	}

	protected function remove($prgrs_id)
	{
		$prgrs = $this->getProgressObject($prgrs_id);
		$ass = $prgrs->getAssignment();
		$prg = $ass->getStudyProgramme();
		if ($prg->getRefId() != $this->ref_id) {
			throw new ilException("Can only remove users from the node they where assigned to.");
		}
		$ass->deassign();
	}

	protected function getProgressObject($prgrs_id)
	{
		assert('is_int($prgrs_id)');
		if (!array_key_exists($prgrs_id, $this->progress_object)) {
			require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeUserProgress.php");
			$this->progress_object[$prgrs_id] = ilStudyProgrammeUserProgress::getInstanceById($prgrs_id);
		}
		return $this->progress_object[$prgrs_id];
	}

	protected function getPrgrsId()
	{
		if (!is_numeric($_GET["prgrs_id"])) {
			throw new ilException("Expected integer 'prgrs_id'");
		}

		return (int)$_GET["prgrs_id"];
	}

	protected function showSuccessMessage($a_lng_var)
	{
		require_once("Services/Utilities/classes/class.ilUtil.php");
		ilUtil::sendSuccess($this->lng->txt("prg_$a_lng_var"), true);
	}

	protected function showInfoMessage($a_lng_var)
	{
		require_once("Services/Utilities/classes/class.ilUtil.php");
		ilUtil::sendInfo($this->lng->txt("prg_$a_lng_var"), true);
	}

	protected function initSearchGUI()
	{
		require_once("./Modules/StudyProgramme/classes/class.ilStudyProgrammeRepositorySearchGUI.php");
		ilStudyProgrammeRepositorySearchGUI::fillAutoCompleteToolbar(
			$this,
			$this->toolbar,
			array(
				"auto_complete_name"	=> $this->lng->txt("user"),
				"submit_name"			=> $this->lng->txt("add"),
				"add_search"			=> true
			)
		);
	}

	public function getStudyProgramme($a_ref_id = null)
	{
		if ($a_ref_id === null) {
			$a_ref_id = $this->ref_id;
		}
		require_once("Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");
		return ilObjStudyProgramme::getInstanceByRefId($a_ref_id);
	}

	/**
	 * Get the link target for an action on user progress.
	 *
	 * @param	int		$a_action		One of ilStudyProgrammeUserProgress::ACTION_*
	 * @param	int		$a_prgrs_id		Id of the progress object to act on.
	 * @param	int		$a_ass_id		Id of the assignment object to act on.
	 * @return	string					The link to the action.
	 */
	public function getLinkTargetForAction($a_action, $a_prgrs_id, $a_ass_id)
	{
		require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeUserProgress.php");

		switch ($a_action) {
			case ilStudyProgrammeUserProgress::ACTION_MARK_ACCREDITED:
				$target_name = "markAccredited";
				break;
			case ilStudyProgrammeUserProgress::ACTION_UNMARK_ACCREDITED:
				$target_name = "unmarkAccredited";
				break;
			case ilStudyProgrammeUserProgress::ACTION_SHOW_INDIVIDUAL_PLAN:
				require_once("Modules/StudyProgramme/classes/class.ilObjStudyProgrammeIndividualPlanGUI.php");
				return ilObjStudyProgrammeIndividualPlanGUI::getLinkTargetView($this->ctrl, $a_ass_id);
			case ilStudyProgrammeUserProgress::ACTION_REMOVE_USER:
				$target_name = "removeUser";
				break;
			default:
				throw new ilException("Unknown action: $action");
		}

		$this->ctrl->setParameter($this, "prgrs_id", $a_prgrs_id);
		$link = $this->ctrl->getLinkTarget($this, $target_name);
		$this->ctrl->setParameter($this, "prgrs_id", null);
		return $link;
	}
}
