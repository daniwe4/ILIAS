<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once 'Services/Repository/classes/class.ilObjectPluginAccess.php';

/**
 * Access checker for each plugin object.
 */
class ilObjScaledFeedbackAccess extends ilObjectPluginAccess
{
    /**
    * Checks wether a user may invoke a command or not
    * (this method is called by ilAccessHandler::checkAccess)
    *
    * Please do not check any preconditions handled by
    * ilConditionHandler here. Also don't do usual RBAC checks.
    *
    * @param 	string 		$cmd 				command (not permission!)
    * @param 	string 		$permission 		permission
    * @param 	int 		$ref_id 			reference id
    * @param 	int 		$obj_id 			object id
    * @param 	int 		$user_id 			user id (default is current user)
    *
    * @return 	boolean 						true, if everything is ok
    */
    public function _checkAccess($cmd, $permission, $ref_id, $obj_id, $user_id = "")
    {
        global $ilUser, $ilAccess;

        /*
        * This Routine is called within ilAccess::checkAccessOfUser::doStatusCheck.
        * We rely on standart ilAccess::checkAccessOfUser procedure, i.e. return true here, except when the object is offline,
        * then redirect to read-permission check.
        */
        if ($user_id == "") {
            $user_id = $ilUser->getId();
        }

        switch ($permission) {
            case "read":
            case "visible":
                if (self::_isOffline($obj_id)
                    && !$ilAccess->checkAccessOfUser($user_id, "write", "", $ref_id)) {
                    return false;
                }
        }

        return true;
    }

    /**
    * Check online status of object
    */
    public static function _isOffline($id)
    {
        $object = ilObjectFactory::getInstanceByObjId($id);
        return !$object->getSettings()->getOnline();
    }
}
