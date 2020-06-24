<?php
include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");


use \CaT\Plugins\RoomSetup;

/**
 * Plugin base class. Keeps all information the plugin needs
 */
class ilRoomSetupPlugin extends ilRepositoryObjectPlugin
{
    const COPY_OPERATION_ID = 58;

    /**
     * @var RoomSetup\ilPluginActions
     */
    protected $actions;

    /**
     * @var RoomSetup\ServiceOptions\DB
     */
    protected $service_options_db;

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

        $type_id = $this->getTypeId($type, $db);
        if (!$type_id) {
            $type_id = $this->createTypeId($type, $db);
        }

        $this->assignCopyPermissionToPlugin($type_id, $db);
        $this->createPluginPermissions((int) $type_id, $db);

        RoomSetup\UnboundGlobalProvider::createGlobalProvider();

        return true;
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
     * Creates permissions the plugin needs
     *
     * @param int 		$type_id
     * @param \ilDBInterface	$db
     *
     * @return null
     */
    protected function createPluginPermissions(int $type_id, \ilDBInterface $db)
    {
        include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
        $new_rbac_options = array(array("edit_equipment", "User can edit equipment", "object", 2700));

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
    protected function permissionExists(string $permission, \ilDBInterface $db)
    {
        $query = "SELECT count(ops_id) AS cnt FROM rbac_operations\n"
                . " WHERE operation = " . $db->quote($permission, 'text');

        $res = $db->query($query);
        $row = $db->fetchAssoc($res);

        return $row["cnt"] > 0;
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
     * Get the name of the Plugin
     *
     * @return string
     */
    public function getPluginName()
    {
        return "RoomSetup";
    }

    /**
     * Get an intance of ilPluginActions
     *
     * @return ilPluginActions
     */
    public function getActions()
    {
        if ($this->actions === null) {
            global $DIC;
            $db = $DIC->database();
            $this->actions = new RoomSetup\ilPluginActions($this, $this->getServiceOptionDB($db));
        }

        return $this->actions;
    }

    /**
     * Get db implementation for service options
     *
     * @param 	$db
     */
    protected function getServiceOptionDB($db)
    {
        if ($this->service_options_db === null) {
            $this->service_options_db = new RoomSetup\ServiceOptions\ilDB($db);
        }

        return $this->service_options_db;
    }

    /**
     * Get a closure to get txts from plugin.
     *
     * @return \Closure
     */
    public function txtClosure()
    {
        return function ($code) {
            return $this->txt($code);
        };
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

    public function handleEvent(string $a_component, string $a_event, $a_parameter)
    {
        global $DIC;
        $logger = $DIC['ilLog'];

        //deal with course-update: re-schedule events
        if ($a_component == 'Modules/Course' && $a_event == 'update') {
            $crs_ref = $a_parameter['object']->getRefId();
            if (!$crs_ref) {
                $refs = \ilObject::_getAllReferences($a_parameter['object']->getId());
                if (!$refs) {
                    return;
                } else {
                    $crs_ref = array_shift($refs);
                }
            }
            $xrse_objs = $this->getRoomSetupsBelowCourse($crs_ref);
            foreach ($xrse_objs as $xrse_obj) {
                $logger->write('update roomsetup events on course-change for ref ' . $crs_ref);
                $xrse_obj->scheduleMailingEvents();
            }
            return;
        }
    }

    protected function getRoomSetupsBelowCourse($ref_id)
    {
        global $DIC;
        $tree = $DIC->repositoryTree();
        $children = $tree->getSubTree(
            $tree->getNodeData($ref_id),
            true,
            'xrse'
        );
        return array_map(
            function ($node) {
                return ilObjectFactory::getInstanceByRefId($node["child"]);
            },
            $children
        );
    }
}
