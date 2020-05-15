<?php

use CaT\Plugins\EnhancedLPReport\Settings as Settings;

require_once 'Services/Repository/classes/class.ilObjectPlugin.php';
require_once 'Services/Tracking/classes/class.ilTrQuery.php';
require_once './Services/User/classes/class.ilUserDefinedFields.php';

class ilObjEnhancedLPReport extends ilObjectPlugin
{

	protected static $relevant_role_title = 'Extern - Deutsche Bahn';
	protected static $usr_data_columns = array(
		'email', 'firstname', 'lastname', 'login', 'gender', 'last_login', 'active', 'usr_id', 'institution');
	protected static $min_collumns = array('usr_id');

	protected $g_tree;
	protected $g_ildb;
	protected $g_rbacreview;

	protected $s_f;
	protected $settings_data_handler;


	public function __construct($a_ref_id = 0)
	{
		parent::__construct($a_ref_id);
		global $ilDB, $tree, $rbacreview;
		$this->g_tree = $tree;
		$this->g_ildb = $ilDB;
		$this->g_rbacreview = $rbacreview;
		$this->s_f = new Settings\SettingFactory($ilDB);
		$this->settings_data_handler = $this->s_f->reportSettingsDataHandler();
		$this->createReportSettings();
	}

	public function getParentCourseId()
	{
		if ($this->parent_crs === null) {
			$this->parent_crs = $this->getParentObjectOfTypeIds('crs')['ref_id'];
		}
		return $this->parent_crs;
	}

	public function getRelevantRoles()
	{
		$roles = array(-1 => '--');
		foreach ($this->g_rbacreview->getGlobalRoles() as $rol_id) {
			$roles[$rol_id] = ilObject::_lookupTitle($rol_id, 'role');
		}
		return $roles;
	}

	protected function getParentObjectOfTypeIds($type = null)
	{
		return $this->getParentObjectOfObjOfTypeIds($this->getRefId(), $type);
	}

	protected function getParentObjectOfObjOfTypeIds($ref_id, $type = null)
	{
		$data = $this->g_tree->getParentNodeData($ref_id);
		while (null !== $type && $type !== $data['type'] && (string)ROOT_FOLDER_ID !== (string)$data['ref_id']) {
			$data = $this->g_tree->getParentNodeData($data['ref_id']);
		}
		return (null === $type || $type === $data['type'] )
			? array('obj_id' => $data['obj_id'], 'ref_id' => $data['ref_id']) : array();
	}

	protected function createReportSettings()
	{

		$this->report_settings =
			$this->s_f->reportSettings('rep_xlpr_data')
				->addSetting($this->s_f
								->settingBool('is_online', $this->plugin()->txt('is_online')))
				->addSetting($this->s_f
								->settingListInt('target_role', $this->plugin()->txt('target_role'))
								->setDefaultValue(-1)
								->setOptions($this->getRelevantRoles()));
	}

	public function initType()
	{
		 $this->setType("xlpr");
	}

	public function setSettingsData(array $settings)
	{
		$this->settings = $settings;
	}

	public function doCreate()
	{
		$this->settings_data_handler->createObjEntry($this->getId(), $this->report_settings);
	}

	public function doRead()
	{
		$this->settings = $this->settings_data_handler->readObjEntry($this->getId(), $this->report_settings);
	}

	public function doUpdate()
	{
		$this->settings_data_handler->updateObjEntry($this->getId(), $this->report_settings, $this->settings);
	}

	public function doDelete()
	{
		$this->settings_data_handler->deleteObjEntry($this->getId(), $this->report_settings);
	}

	public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
	{
		$new_obj->settings = $this->settings;
		$new_obj->setDescription($this->getDescription());
		$new_obj->update();
	}

	public function getData(array $filter_settings, array $vis_columns)
	{
		$rel_columns = $this->getRelevantColumns($filter_settings, $vis_columns);
		return $this->getUserData($rel_columns, $filter_settings);
	}

	protected function getRelevantColumns(array $filters, array $vis_columns)
	{
		$filter_columns = array_keys(array_filter(
			$filters,
			function ($val) {
				return $val !== false;
			}
		));
		return array_unique(array_merge(array_keys($vis_columns), $filter_columns));
	}

	protected function getUserData(array $rel_columns, array $filter_settings)
	{
		return $this->maybeAddCourseAndMetadataAndFilter(
			$this->relevantUsersData($rel_columns, $filter_settings),
			$rel_columns,
			$filter_settings
		);
	}

	protected function relevantUsersData(array $rel_columns, array $filter_settings)
	{
		$fetch_fields = array_unique(array_merge(array_intersect($rel_columns, self::$usr_data_columns), self::$min_collumns));
		$usr_ids = $this->g_rbacreview->assignedUsers(
			$this->settings['target_role']
		);
		return $this->enrichByUsrData($usr_ids,$fetch_fields);
	}

	protected function enrichByUsrData(array $usr_ids, array $fetch_fields)
	{
		if(count($usr_ids) === 0) {
			return array();
		}
		$q = 'SELECT '.implode(',', $fetch_fields)
			.'	FROM usr_data'
			.'	WHERE '.$this->g_ildb->in('usr_id',$usr_ids,false,'integer');
		$res = $this->g_ildb->query($q);
		$return = [];
		while($rec = $this->g_ildb->fetchAssoc($res)) {
			$return[] = $rec;
		}
		return $return;
	}

	public function getSelectableUDF()
	{
		$return = array();
		foreach (ilUserDefinedFields::_getInstance()->getCourseExportableFields() as $udf_id => $data) {
			$return['udf_'.$udf_id] = $data;
		}
		return $return;
	}

	protected function maybeAddCourseAndMetadataAndFilter(array $assigned_users_data, array $rel_columns, array $filter_settings)
	{
		$sel_udfs = $this->getSelectableUDF();

		$rel_udfs = array_intersect(array_keys($sel_udfs), $rel_columns);
		$udf_data = array();
		if (count($rel_udfs) > 0) {
			$should_fetch_udf = true;
			foreach ($rel_udfs as $id) {
				$udf_data[$id] = $sel_udfs[$id]['field_id'];
			}
		}

		if (in_array('status', $rel_columns)) {
			$should_fetch_status = true;
		}
		$filter = false;
		foreach ($filter_settings as $value) {
			if (is_bool($value)) {
				$aux = $value;
			} else {
				$aux = true;
			}
			$filter = $filter || $aux;
		}
		if (in_array('member', $rel_columns)) {
			$should_check_member = true;
		}
		if (!$should_fetch_status && !$should_fetch_udf && !$filter && !$should_check_member) {
			return $assigned_users_data;
		}
		$return = array();
		foreach ($assigned_users_data as $values) {
			if ($should_check_member) {
				$usr_id = $values['usr_id'];
				$is_member = $this->isMember($usr_id);
				if ($filter_settings['member'] !== false) {
					if ($filter_settings['member'] === ilEnhancedLPTableGUI::YES && !$is_member) {
						continue;
					}
					if ($filter_settings['member'] === ilEnhancedLPTableGUI::NO && $is_member) {
						continue;
					}
				}
				$values['member'] = $is_member;
			}
			if ($should_fetch_status) {
				$aux_s = $this->lookupStatusOfUsrAtCourse($usr_id);
				if ($filter_settings['status'] !== false
					&& $filter_settings['status'] !==  ilEnhancedLPTableGUI::LP_ALL
					&& (string)$aux_s !== (string)$filter_settings['status']) {
					continue;
				}
				$values['status'] = $aux_s;
			}
			foreach ($udf_data as $f_id => $udf_id) {
				$values[$f_id] = $this->lookupUDFOfUser($usr_id, $udf_id);
			}
			if ($filter) {
				if (!$this->appropriateSet($values, $filter_settings)) {
					continue;
				}
			}
			$return[] = $values;
		}
		return $return;
	}

	protected function lookupUDFOfUser($usr_id, $udf_id)
	{
		$res = $this->g_ildb->query('SELECT value FROM udf_text'
			.'	WHERE field_id = '.$this->g_ildb->quote($udf_id, 'integer')
			.'		AND usr_id = '.$this->g_ildb->quote($usr_id, 'integer'));
		return $this->g_ildb->fetchAssoc($res)['value'];
	}

	protected function appropriateSet($values, $filter_settings)
	{
		foreach ($filter_settings as $field => $f_value) {
			if ($f_value === false || $field === 'status' || $field === 'member' || $field === 'active') {
				continue;
			}
			if (strpos(strtolower($values[$field]), strtolower($f_value)) !== 0) {
				return false;
			}
		}
		if ($filter_settings['acitve'] !== false) {
			$active = (bool)$values['active'];
			if ($filter_settings['active'] === ilEnhancedLPTableGUI::YES && !$active) {
				return false;
			}
			if ($filter_settings['active'] === ilEnhancedLPTableGUI::NO && $active) {
				return false;
			}
		}
		return true;
	}

	protected function lookupStatusOfUsrAtCourse($usr_id)
	{
		if (isset($this->getParentCourseParticipants()[$usr_id])) {
			return $this->getParentCourseParticipants()[$usr_id];
		}
		return ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
	}

	protected function isMember($usr_id)
	{
		return isset($this->getParentCourseParticipants()[$usr_id]);
	}

	protected function getParentCourseParticipants()
	{
		if ($this->parent_course_p_s === null) {
			$this->parent_course_p_s = $this->loadParentCourseParticipants();
		}
		return $this->parent_course_p_s;
	}

	protected function loadParentCourseParticipants()
	{
		$parent_crs_id = $this->getParentCourseId();
		$return = array();
		foreach (ilTrQuery::getUserDataForObject(
			$parent_crs_id,
			'',
			'',
			0,
			9999,
			null,
			array('status')
		)["set"] as $usr_crs_data) {
			$return[$usr_crs_data['usr_id']] = $usr_crs_data['status'];
		}
		return $return;
	}

	protected function filter($filter, $usr_data)
	{
		return $usr_data;
	}

	public function plugin()
	{
		return $this->plugin;
	}

	public function validRoleSet()
	{
		return in_array($this->settings['target_role'], $this->g_rbacreview->getGlobalRoles());
	}
}
