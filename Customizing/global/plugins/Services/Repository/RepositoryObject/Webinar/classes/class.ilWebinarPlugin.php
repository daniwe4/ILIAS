<?php
include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");
require_once(__DIR__ . "/../vendor/autoload.php");

use \CaT\Plugins\Webinar\ilPluginActions;
use \CaT\Plugins\Webinar\DI;
use \CaT\Plugins\Webinar\UnboundGlobalProvider;

/**
 * Plugin base class. Keeps all information the plugin needs
 */
class ilWebinarPlugin extends ilRepositoryObjectPlugin
{
    use DI;

    const COPY_OPERATION_ID = 58;

    /**
     * @var ilPluginActions
     */
    protected $actions;

    /**
     * Get the name of the Plugin
     *
     * @return string
     */
    public function getPluginName()
    {
        return "Webinar";
    }

    /**
     * Defines custom uninstall action like delete table or something else
     */
    protected function uninstallCustom()
    {
    }

    protected function beforeActivation()
    {
        parent::beforeActivation();
        global $DIC;
        $db = $DIC->database();

        $type = $this->getId();

        if (!$this->isRepositoryPlugin($type)) {
            throw new ilPluginException("Object plugin type must start with an x. Current type is " . $type . ".");
        }

        $type_id = (int) $this->getTypeId($type, $db);
        if (!$type_id) {
            $type_id = (int) $this->createTypeId($type, $db);
        }

        $this->assignCopyPermissionToPlugin($type_id, $db);
        $this->createPluginPermissions($type_id, $db);

        //on activation, also install global provider
        UnboundGlobalProvider::createGlobalProvider();

        return true;
    }

    protected function afterDeactivation()
    {
        //on deactivation, also de-install global provider
        UnboundGlobalProvider::deleteGlobalProvider();
    }

    /**
     * Creates permissions the plugin needs
     *
     * @param int 		$type_id
     * @param \ilDBInterface	$db
     *
     * @return null
     */
    protected function createPluginPermissions($type_id, \ilDBInterface $db)
    {
        assert('is_int($type_id)');
        include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
        $new_rbac_options = array(array("edit_member", "Edit Member and download file", "object", 2700),
            array("edit_participation", "Change participation status", "object", 2710)
        );

        foreach ($new_rbac_options as $value) {
            if (!$this->permissionExists($value[0], $db)) {
                $new_ops_id = \ilDBUpdateNewObjectType::addCustomRBACOperation($value[0], $value[1], $value[2], $value[3]);
                \ilDBUpdateNewObjectType::addRBACOperation($type_id, $new_ops_id);
            }
        }
    }

    /**
     * Check the permission is already created
     *
     * @param string 	$permission
     * @param \ilDBInterface	$db
     *
     * @return bool
     */
    protected function permissionExists($permission, \ilDBInterface $db)
    {
        assert('is_string($permission)');

        $query = "SELECT count(ops_id) AS cnt FROM rbac_operations\n"
                . " WHERE operation = " . $db->quote($permission, 'text');

        $res = $db->query($query);
        $row = $db->fetchAssoc($res);

        return $row["cnt"] > 0;
    }

    /**
     * Check current plugin is repository plgind
     *
     * @param string 	$type
     *
     * @return bool
     */
    protected function isRepositoryPlugin($type)
    {
        return substr($type, 0, 1) == "x";
    }

    /**
     * Get id of current type
     *
     * @param string 	$type
     * @param 			$db
     *
     * @return int | null
     */
    protected function getTypeId($type, $db)
    {
        $set = $db->query("SELECT obj_id FROM object_data " .
            " WHERE type = " . $db->quote("typ", "text") .
            " AND title = " . $db->quote($type, "text"));

        if ($db->numRows($set) == 0) {
            return null;
        }

        $rec = $db->fetchAssoc($set);
        return $rec["obj_id"];
    }

    /**
     * Create a new entry in object data
     *
     * @param string 	$type
     * @param 			$db
     *
     * @return int
     */
    protected function createTypeId($type, $db)
    {
        $type_id = $db->nextId("object_data");
        $db->manipulate("INSERT INTO object_data " .
            "(obj_id, type, title, description, owner, create_date, last_update) VALUES (" .
            $db->quote($type_id, "integer") . "," .
            $db->quote("typ", "text") . "," .
            $db->quote($type, "text") . "," .
            $db->quote("Plugin " . $this->getPluginName(), "text") . "," .
            $db->quote(-1, "integer") . "," .
            $db->quote(ilUtil::now(), "timestamp") . "," .
            $db->quote(ilUtil::now(), "timestamp") .
            ")");

        return $type_id;
    }

    /**
     * Assign permission copy to current plugin
     *
     * @param int 		$type_id
     * @param 			$db
     *
     * @return int
     */
    protected function assignCopyPermissionToPlugin($type_id, $db)
    {
        $ops = array(self::COPY_OPERATION_ID);

        foreach ($ops as $op) {
            // check whether type exists in object data, if not, create the type

            if (!$this->permissionIsAssigned($type_id, $op, $db)) {
                $db->manipulate("INSERT INTO rbac_ta " .
                    "(typ_id, ops_id) VALUES (" .
                    $db->quote($type_id, "integer") . "," .
                    $db->quote($op, "integer") .
                    ")");
            }
        }
    }

    /**
     * Checks permission is not assigned to plugin
     *
     * @param int 		$type_id
     * @param int 		$op_id
     * @param 			$db
     *
     * @return bool
     */
    protected function permissionIsAssigned($type_id, $op_id, $db)
    {
        $set = $db->query("SELECT count(typ_id) as cnt FROM rbac_ta " .
                " WHERE typ_id = " . $db->quote($type_id, "integer") .
                " AND ops_id = " . $db->quote($op_id, "integer"));

        $rec = $db->fetchAssoc($set);

        return $rec["cnt"] > 0;
    }

    /**
     * decides if this repository plugin can be copied
     *
     * @return bool
     */
    public function allowCopy()
    {
        return true;
    }

    /**
     * Get ilPluginActions
     *
     * @return ilPluginActions
     */
    public function getActions()
    {
        if ($this->actions === null) {
            $this->actions = new ilPluginActions($this);
        }

        return $this->actions;
    }

    /**
     * Closure to get txt from plugin
     */
    public function txtClosure()
    {
        return function ($code) {
            return $this->txt($code);
        };
    }

    /**
     * Handle an event
     * @param string	$a_component
     * @param string	$a_event
     * @param mixed		$a_parameter
     */
    public function handleEvent($a_component, $a_event, $a_parameter)
    {
        if ($a_component === 'Services/AccessControl'
            && (
                $a_event === 'assignUser'
                || $a_event === 'deassignUser'
                )
            && $a_parameter['type'] === 'crs'
            && $this->memberRole($a_parameter['role_id'])
        ) {
            $user_id = (int) $a_parameter["usr_id"];
            $obj_id = (int) $a_parameter['obj_id'];
            $webs = $this->getAffectedWebinar($obj_id);

            if ($a_event === 'assignUser') {
                $this->bookOrPortUser($user_id, $webs);
            }

            if ($a_event === 'deassignUser') {
                $this->cancelUser($user_id, $webs);
            }
        }
    }

    /**
     * Books or ports user
     *
     * @param int 	$user_id
     * @param \ilObjWebinar[] 	$webs
     *
     * @return void
     */
    protected function bookOrPortUser($user_id, $webs)
    {
        foreach ($webs as $web) {
            if (!$web->getSettings()->isFinished()) {
                $actions = $web->getActions();
                $vc_actions = $web->getVCActions();

                $login = \ilObjUser::_lookupLogin($user_id);
                $unknown_user = $vc_actions->getUnknownParticipantByLogin($login);
                if (!is_null($unknown_user)) {
                    $actions->portUserToBookParticipant($user_id, $unknown_user);
                    $vc_actions->deleteUnknownParticipant($unknown_user->getId());
                } elseif (!$actions->isBookedUser($user_id)) {
                    $actions->bookParticipant($user_id, $login);
                }
            }
        }
    }

    /**
     * Cancel user
     *
     * @param int 	$user_id
     * @param \ilObjWebinar[] 	$webs
     *
     * @return void
     */
    protected function cancelUser($user_id, $webs)
    {
        foreach ($webs as $web) {
            if (!$web->getSettings()->isFinished()) {
                $actions = $web->getActions();
                $vc_actions = $web->getVCActions();

                $unknown_user = $vc_actions->getUnknownParticipantByLogin(\ilObjUser::_lookupLogin($user_id));
                if (!is_null($unknown_user)) {
                    $vc_actions->deleteUnknownParticipant($unknown_user->getId());
                }
                $actions->cancelParticipitation($user_id);
            }
        }
    }

    /**
     * Get all webinar objects below the course
     *
     * @return \ilObjWebinar[]
     */
    protected function getAffectedWebinar($obj_id)
    {
        global $DIC;
        $tree = $DIC->repositoryTree();
        $obj_definition = $DIC["objDefinition"];
        $crs_ref = $this->getCourseRefId($obj_id);

        return $this->getAllChildrenOfByType($crs_ref, "xwbr", $tree, $obj_definition);
    }

    /**
     * Get ref id of course user is booked to
     *
     * @param int 	$obj_id
     *
     * @return int
     */
    protected function getCourseRefId($obj_id)
    {
        return array_shift(\ilObject::_getAllReferences($obj_id));
    }

    /**
     * Check the assigned role is a member role
     *
     * @param int $role_id
     *
     * @return bool
     */
    private function memberRole($role_id)
    {
        return strpos(\ilObject::_lookupTitle($role_id), 'il_crs_member_') === 0;
    }

    /**
     * Get all children by type recursive
     *
     * @param int 	$ref_id
     * @param string 	$search_type
     *
     * @return Object 	of search type
     */
    protected function getAllChildrenOfByType($ref_id, $search_type, $tree, $obj_definition)
    {
        $childs = $tree->getChilds($ref_id);
        $ret = array();

        foreach ($childs as $child) {
            $type = $child["type"];
            if ($type == $search_type) {
                $ret[] = \ilObjectFactory::getInstanceByRefId($child["child"]);
            }

            if ($obj_definition->isContainer($type)) {
                $rec_ret = $this->getAllChildrenOfByType($child["child"], $search_type, $tree, $obj_definition);
                if (!is_null($rec_ret)) {
                    $ret = array_merge($ret, $rec_ret);
                }
            }
        }

        return $ret;
    }

    public function getReminderSettings() : \CaT\Plugins\Webinar\Config\Reminder\NotFinalized
    {
        global $DIC;
        return $this->getPluginDIC($this, $DIC)["config.notfinalized.db"]->select();
    }
}
