<?php
/* @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de> */
require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Modules/ManualAssessment/classes/Members/class.ilManualAssessmentMembersStorageDB.php';
require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
require_once 'Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';
require_once 'Services/Tracking/classes/class.ilLPStatus.php';
class ilManualAssessmentMembersTableGUI extends ilTable2GUI
{
	public function __construct($a_parent_obj, array $filter_users = null, $a_parent_cmd = "", $a_template_context = "")
	{
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);
		global $ilCtrl, $lng, $ilUser;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->setEnableTitle(true);
		$this->setTopCommands(true);
		$this->setEnableHeader(true);
		$this->setExternalSorting(false);
		$this->setExternalSegmentation(true);
		$this->setRowTemplate("tpl.members_table_row.html", "Modules/ManualAssessment");
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, "view"));
		$this->parent_obj = $a_parent_obj;

		$this->usr_utils = gevUserUtils::getInstanceByObj($ilUser);
		$this->settings = $this->parent_obj->object->getSettings();
		$this->access = $this->parent_obj->object->accessHandler();

		$this->cachePermissions($this->usr_utils, $this->settings, $this->access);

		$this->columns = $this->visibleColumns();
		$this->viewer_id = $ilUser->getId();
		foreach ($this->columns as $lng_var => $params) {
			$this->addColumn($this->lng->txt($lng_var), $params[0]);
		}

		$members = iterator_to_array($a_parent_obj->object->loadMembers());
		if ($filter_users !== null) {
			$members = array_filter($members, function ($member) use ($filter_users) {
				if (in_array($member[ilManualAssessmentMembers::FIELD_USR_ID], $filter_users)) {
					return $member;
				}
			});
		}

		$this->setData($members);
	}

	protected function visibleColumns()
	{
		$columns = array( 'name' 				=> array('name')
						, 'login' 				=> array('login'));
		if ($this->may_view_grades || $this->may_edit_grades) {
			$columns['grading'] = array('lp_status');
			$columns['mass_graded_by'] = array('mass_graded_by');
		}
		$columns['actions'] = array(null);
		return $columns;
	}

	protected function fillRow($a_set)
	{
		$this->tpl->setVariable("FULLNAME", $a_set[ilManualAssessmentMembers::FIELD_LASTNAME].', '.$a_set[ilManualAssessmentMembers::FIELD_FIRSTNAME]);
		$this->tpl->setVariable("LOGIN", $a_set[ilManualAssessmentMembers::FIELD_LOGIN]);
		if (!ilObjUser::_lookupActive($a_set[ilManualAssessmentMembers::FIELD_USR_ID])) {
			$this->tpl->setCurrentBlock('access_warning');
			$this->tpl->setVariable('PARENT_ACCESS', $this->lng->txt('usr_account_inactive'));
			$this->tpl->parseCurrentBlock();
		}
		if ($this->may_view_grades || $this->may_edit_grades) {
			$this->tpl->setCurrentBlock('lp_info');
			$status = $a_set[ilManualAssessmentMembers::FIELD_FINALIZED] == 1 ? $a_set[ilManualAssessmentMembers::FIELD_LEARNING_PROGRESS] : ilManualAssessmentMembers::LP_IN_PROGRESS;
			$this->tpl->setVariable("LP_STATUS", $this->getEntryForStatus($status));
			$this->tpl->setVariable(
				"GRADED_BY",
				$a_set[ilManualAssessmentMembers::FIELD_EXAMINER_ID] && $a_set[ilManualAssessmentMembers::FIELD_FINALIZED]
				?	$a_set[ilManualAssessmentMembers::FIELD_EXAMINER_LASTNAME].", "
						.$a_set[ilManualAssessmentMembers::FIELD_EXAMINER_FIRSTNAME]
				: 	''
			);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("ACTIONS", $this->buildActionDropDown($a_set));
	}


	protected function getImagetPathForStatus($a_status)
	{
		switch ($a_status) {
			case ilManualAssessmentMembers::LP_IN_PROGRESS:
				$status = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
				break;
			case ilManualAssessmentMembers::LP_COMPLETED:
				$status = ilLPStatus::LP_STATUS_COMPLETED_NUM;
				break;
			case ilManualAssessmentMembers::LP_FAILED:
				$status = ilLPStatus::LP_STATUS_FAILED_NUM;
				break;
			default:
				$status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED ;
				break;
		}
		return ilLearningProgressBaseGUI::_getImagePathForStatus($status);
	}

	protected function getEntryForStatus($a_status)
	{
		switch ($a_status) {
			case ilManualAssessmentMembers::LP_IN_PROGRESS:
				return $this->lng->txt('mass_status_pending');
				break;
			case ilManualAssessmentMembers::LP_COMPLETED:
				return $this->lng->txt('mass_status_completed');
				break;
			case ilManualAssessmentMembers::LP_FAILED:
				return $this->lng->txt('mass_status_failed');
				break;
		}
	}

	protected function buildActionDropDown($a_set)
	{

		$t_usr_id = $a_set[ilManualAssessmentMembers::FIELD_USR_ID];
		$finalized = $a_set[ilManualAssessmentMembers::FIELD_FINALIZED];

		$l = new ilAdvancedSelectionListGUI();
		$l->setListTitle($this->lng->txt("actions"));
		$l->setId($t_usr_id);

		$this->ctrl->setParameterByClass('ilManualAssessmentMemberGUI', 'usr_id', $t_usr_id);
		$edited_by_other = $this->setWasEditedByOtherUser($a_set);

		$target_is_employee = $this->userIsEmployee($t_usr_id);
		$target_is_self = $t_usr_id == $this->viewer_id;

		$may_grade_this_user = $this->may_grade_any_user
								|| ($this->may_grade_employees && $target_is_employee)
								|| ($may_grade_self && $target_is_self);


		$may_view_this_user = $this->may_view_any_user
								|| ($this->may_view_employees && $target_is_employee);

		if (($finalized && !$edited_by_other && $may_grade_this_user)
			|| $this->may_view_this_user) {
			$target = $this->ctrl->getLinkTargetByClass('ilManualAssessmentMemberGUI', 'view');
			$l->addItem($this->lng->txt('mass_usr_view'), 'view', $target);
		}
		if (!$finalized && !$edited_by_other && $may_grade_this_user) {
			$target = $this->ctrl->getLinkTargetByClass('ilManualAssessmentMemberGUI', 'edit');
			$l->addItem($this->lng->txt('mass_usr_edit'), 'edit', $target);
		}
		if (!$finalized && $this->may_edit_members) {
			$this->ctrl->setParameter($this->parent_obj, 'usr_id', $t_usr_id);
			$target = $this->ctrl->getLinkTarget($this->parent_obj, 'removeUserConfirmation');
			$this->ctrl->setParameter($this->parent_obj, 'usr_id', null);
			$l->addItem($this->lng->txt('mass_usr_remove'), 'removeUser', $target);
		}
		if ($finalized && $this->may_amend_grades) {
			$target = $this->ctrl->getLinkTargetByClass('ilManualAssessmentMemberGUI', 'amend');
			$l->addItem($this->lng->txt('mass_usr_amend'), 'amend', $target);
		}
		$this->ctrl->setParameterByClass('ilManualAssessmentMemberGUI', 'usr_id', null);
		return $l->getHTML();
	}

	protected function userIsEmployee($a_usr_id)
	{
		return in_array($a_usr_id, $this->usr_utils->getEmployees());
	}

	protected function cachePermissions($usr_utils, $settings, $access)
	{
		$this->may_grade_employees = $usr_utils->isSuperior() && $settings->superiorExaminate();
		$this->may_edit_employees = $usr_utils->isSuperior() && $settings->superiorView();
		$this->may_grade_self = $settings->gradeSelf();
		$this->may_edit_members = $access->checkAccessToObj($this->parent_obj->object, 'edit_members');
		$this->may_amend_grades = $access->checkAccessToObj($this->parent_obj->object, 'amend_grading');
		$this->may_view_any_user = $access->checkAccessToObj($this->parent_obj->object, 'read_learning_progress');
		$this->may_grade_any_user = $access->checkAccessToObj($this->parent_obj->object, 'edit_learning_progress');
	}

	protected function setWasEditedByOtherUser($set)
	{
		return (int)$set[ilManualAssessmentMembers::FIELD_EXAMINER_ID] !== (int)$this->viewer_id
				&& 0 !== (int)$set[ilManualAssessmentMembers::FIELD_EXAMINER_ID];
	}
}
