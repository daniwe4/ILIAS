<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/Repository/classes/class.ilObjectPluginAccess.php";

/**
 * Access checker for each plugin object.
 */
class ilObjTrainingSearchAccess extends ilObjectPluginAccess
{
    /**
     * @inheritdoc
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
                if (self::_isOffline($a_obj_id) &&
                    !$ilAccess->checkAccessOfUser($a_user_id, "write", "", $a_ref_id)
                ) {
                    return false;
                }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public static function _isOffline($a_obj_id)
    {
        $obj = ilObjectFactory::getInstanceByObjId($a_obj_id);
        return !$obj->getSettings()->getIsOnline();
    }
}
