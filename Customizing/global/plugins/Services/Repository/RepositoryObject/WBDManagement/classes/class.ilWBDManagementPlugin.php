<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\WBDManagement\DI;
use CaT\Plugins\WBDManagement\GutBeraten;

require_once __DIR__ . "/../vendor/autoload.php";
require_once "./Services/Repository/classes/class.ilRepositoryObjectPlugin.php";

class ilWBDManagementPlugin extends ilRepositoryObjectPlugin
{
    const COPY_OPERATION_ID = 58;
    const TP_BILDUNGSDIENSTLEISTER = "Bildungsdienstleister";

    use DI;

    /**
     * @var \Pimple\Container
     */
    protected $dic;

    /**
     * @return bool
     */
    public function allowCopy()
    {
        return true;
    }

    /**
     * Get the name of the Plugin
     *
     * @return 	string
     */
    public function getPluginName()
    {
        return "WBDManagement";
    }

    /**
     * @return \Closure
     */
    public function txtClosure()
    {
        return function ($code) {
            return $this->txt($code);
        };
    }

    /**
     * @inheritdoc
     */
    protected function beforeActivation()
    {
        parent::beforeActivation();

        $db = $this->getDIC()["ilDB"];

        $type = $this->getId();

        if (!$this->isRepositoryPlugin($type)) {
            throw new ilPluginException("Object plugin type must start with an x. Current type is " . $type . ".");
        }

        $type_id = $this->getTypeId($type, $db);
        if (!$type_id) {
            $type_id = $this->createTypeId($type, $db);
        }

        $this->assignCopyPermissionToPlugin((int) $type_id, $db);
        $this->createPluginPermissions((int) $type_id);

        return true;
    }

    protected function assignCopyPermissionToPlugin(int $type_id, ilDBInterface $db)
    {
        $ops = array(self::COPY_OPERATION_ID);

        foreach ($ops as $op) {
            // check whether type exists in object data, if not, create the type
            if (!$this->permissionIsAssigned($type_id, $op, $db)) {
                $db->manipulate(
                    "INSERT INTO" . PHP_EOL .
                    "    rbac_ta" . PHP_EOL .
                    "    (typ_id, ops_id)" . PHP_EOL .
                    "VALUES" . PHP_EOL .
                    "    (" .
                    $db->quote($type_id, "integer") . "," .
                    $db->quote($op, "integer") .
                    "    )" . PHP_EOL
                );
            }
        }
    }

    protected function createPluginPermissions(int $type_id)
    {
        require_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

        $db = $this->getDIC()["ilDB"];

        $new_rbac_options = [
            ["order_time_transmission", "Place an order for time transmisission", "object", 8000],
            ["view_wbd_report", "User is able to view the report", "object", 2700]
        ];

        foreach ($new_rbac_options as $value) {
            if (!$this->permissionExists($value[0], $db)) {
                $new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation(
                    $value[0],
                    $value[1],
                    $value[2],
                    $value[3]
                );
                ilDBUpdateNewObjectType::addRBACOperation($type_id, $new_ops_id);
            }
        }
    }

    protected function permissionExists(string $permission) : bool
    {
        $db = $this->getDIC()["ilDB"];

        $sql =
             "SELECT count(ops_id) AS cnt FROM rbac_operations" . PHP_EOL
            . " WHERE operation = " . $db->quote($permission, 'text') . PHP_EOL
        ;

        $res = $db->query($sql);
        $row = $db->fetchAssoc($res);

        return $row["cnt"] > 0;
    }

    /**
     * Create a new entry in object data.
     *
     * @param 	string 	$type
     * @param 			$db
     * @return 	int
     */
    protected function createTypeId($type, $db)
    {
        $type_id = $db->nextId("object_data");
        $db->manipulate(
            "INSERT INTO" . PHP_EOL .
            "    object_data" . PHP_EOL .
            "    (" . PHP_EOL .
            "        obj_id," . PHP_EOL .
            "        type," . PHP_EOL .
            "        title," . PHP_EOL .
            "        description," . PHP_EOL .
            "        owner," . PHP_EOL .
            "        create_date," . PHP_EOL .
            "        last_update" . PHP_EOL .
            "    )" . PHP_EOL .
            "VALUES" . PHP_EOL .
            "    (" .
            $db->quote($type_id, "integer") . "," .
            $db->quote("typ", "text") . "," .
            $db->quote($type, "text") . "," .
            $db->quote("Plugin " . $this->getPluginName(), "text") . "," .
            $db->quote(-1, "integer") . "," .
            $db->quote(ilUtil::now(), "timestamp") . "," .
            $db->quote(ilUtil::now(), "timestamp") .
            "    )" . PHP_EOL
        );

        return $type_id;
    }

    /**
     * Check current plugin is repository plugin.
     *
     * @param 	string 	$type
     * @return 	bool
     */
    protected function isRepositoryPlugin($type)
    {
        return substr($type, 0, 1) == "x";
    }

    /**
     * Get id of current type.
     *
     * @param 	string 		$type
     * @param 				$db
     * @return 	int|null
     */
    protected function getTypeId($type, $db)
    {
        $set = $db->query(
            "SELECT" . PHP_EOL .
            "    obj_id" . PHP_EOL .
            "FROM" . PHP_EOL .
            "    object_data" . PHP_EOL .
            "WHERE" . PHP_EOL .
            "    type = " . $db->quote("typ", "text") . PHP_EOL .
            "AND" . PHP_EOL .
            "    title = " . $db->quote($type, "text") . PHP_EOL
        );

        if ($db->numRows($set) == 0) {
            return null;
        }

        $rec = $db->fetchAssoc($set);
        return $rec["obj_id"];
    }

    /**
     * Checks whether permission is not assigned to plugin.
     *
     * @param 	int 		$type_id
     * @param 	int 		$op_id
     * @param 				$db
     * @return 	bool
     */
    protected function permissionIsAssigned($type_id, $op_id, $db)
    {
        $set = $db->query(
            "SELECT" . PHP_EOL .
            "    count(typ_id) as cnt" . PHP_EOL .
            "FROM" . PHP_EOL .
            "    rbac_ta" . PHP_EOL .
            "WHERE" . PHP_EOL .
            "    typ_id = " . $db->quote($type_id, "integer") . PHP_EOL .
            "AND" . PHP_EOL .
            "    ops_id = " . $db->quote($op_id, "integer") . PHP_EOL
        );

        $rec = $db->fetchAssoc($set);

        return $rec["cnt"] > 0;
    }

    /**
     * Defines custom uninstall action like delete table or something else.
     *
     * @return 	void
     */
    protected function uninstallCustom()
    {
    }

    /**
     * Handle an event
     * @param string	$a_component
     * @param string	$a_event
     * @param mixed		$a_parameter
     */
    public function handleEvent($a_component, $a_event, $a_parameter)
    {
        if (
            $a_component == "Services/User" &&
            $a_event == "afterUpdate"
        ) {
            $usr = $a_parameter["user_obj"];
            $usr_id = (int) $usr->getId();

            /** @var GutBeraten\DB $gutberate_db */
            $gutberate_db = $this->getDIC()["gut.beraten.db"];

            $wbd_id_udf_id = $this->getDIC()["udf.wbd.id.key"];
            $wbd_status_udf_id = $this->getDIC()["udf.wbd.status.key"];

            /** @var GutBeraten\WBDData $wbd_data */
            $wbd_data = $gutberate_db->selectFor($usr_id);

            $wbd_id = "";
            $wbd_status = "";
            if (!is_null($wbd_data)) {
                $wbd_id = $wbd_data->getWbdId();
                $wbd_status = $wbd_data->getStatus();
            }

            $udf = new ilUserDefinedData($usr_id);
            $udf->set("f_" . $wbd_id_udf_id, $wbd_id);
            $udf->set("f_" . $wbd_status_udf_id, $wbd_status);
            $udf->update();
        }
    }

    protected function getDIC() : \Pimple\Container
    {
        if (is_null($this->dic)) {
            global $DIC;
            $this->dic = $this->getPluginDIC($this, $DIC);
        }
        return $this->dic;
    }

    public function getGutBeratenDB() : GutBeraten\DB
    {
        return $this->getDIC()["gut.beraten.db"];
    }
}
