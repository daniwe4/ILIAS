<?php
require_once 'Services/Repository/classes/class.ilObjectPluginAccess.php';

class ilObjEnhancedLPReportAccess extends ilObjectPluginAccess
{

	public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
	{
		global $ilUser, $ilAccess;

		/*
		* This Routine is called within ilAccess::checkAccessOfUser::doStatusCheck.
		* We rely on standart ilAccess::checkAccessOfUser procedure, i.e. return true here, except when the object is offline,
		* then redirect to read-permission check.
		*/

		if ($a_user_id == "") {
			$a_user_id = $ilUser->getId();
		}
		switch ($a_permission) {
			case "visible":
			case "read":
				if (static::_isOffline($a_obj_id) &&
				!$ilAccess->checkAccessOfUser($a_user_id, "write", "", $a_ref_id)) {
					return false;
				}
				break;
		}

		return true;
	}

	/**
	* Check online status of object
	*/
	public static function _isOffline($a_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT is_online FROM rep_xlpr_data"
			." WHERE id = ".$ilDB->quote($a_id, "integer"));

		$rec  = $ilDB->fetchAssoc($set);
		return (boolean) $rec["is_online"] !== true;
	}
}
