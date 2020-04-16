<?php

declare(strict_types=1);

/**
 * Access checker for each plugin object.
 *
 * @author
 * @copyright Extended GPL, see LICENSE
 */
class ilObjTrainingStatisticsByOrgUnitsAccess extends ilOrgUnitExtensionAccess
{
    /**
     * Checks wether a user may invoke a command or not
     * (this method is called by ilAccessHandler::checkAccess)
     *
     * Please do not check any preconditions handled by
     * ilConditionHandler here. Also don't do usual RBAC checks.
     *
     * @param 	string 		$a_cmd 				command (not permission!)
     * @param 	string 		$a_permission 		permission
     * @param 	int 		$a_ref_id 			reference id
     * @param 	int 		$a_obj_id 			object id
     * @param 	int 		$a_user_id 			user id (default is current user)
     *
     * @return 	bool 							true, if everything is ok
     */
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
            case "read":
            case "visible":
                if (self::_isOffline($a_obj_id)
                    && !$ilAccess->checkAccessOfUser($a_user_id, "write", "", $a_ref_id)) {
                    return false;
                }
        }

        return true;
    }

    /**
     * Check online status of object.
     *
     * @return 	bool
     */
    public static function _isOffline($a_id)
    {
        require_once __DIR__ . '/class.ilObjTrainingStatisticsByOrgUnits.php';
        $ref_id = array_shift(ilObject::_getAllReferences($a_id));
        $obj = new ilObjTrainingStatisticsByOrgUnits($ref_id);
        return !$obj->getSettings()->isOnline();
    }
}
