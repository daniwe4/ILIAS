<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "./Services/Repository/classes/class.ilRepositoryObjectPlugin.php";
require_once __DIR__ . "/../vendor/autoload.php";

use \CaT\Plugins\ScaledFeedback;

class ilScaledFeedbackPlugin extends ilRepositoryObjectPlugin
{
    use ScaledFeedback\DI;

    const COPY_OPERATION_ID = 58;

    public function getPluginName() : string
    {
        return "ScaledFeedback";
    }

    protected function getDIC()
    {
        global $DIC;
        return $this->getPluginDIC($this, $DIC);
    }

    protected function uninstallCustom()
    {
    }

    /**
     * @throws ilPluginException
     */
    protected function beforeActivation() : bool
    {
        parent::beforeActivation();

        $type = $this->getId();

        if (!$this->isRepositoryPlugin($type)) {
            throw new ilPluginException("Object plugin type must start with an x. Current type is " . $type . ".");
        }

        $type_id = $this->getTypeId($type);
        if ($type_id == 0) {
            $type_id = (int) $this->createTypeId($type);
        }

        $this->assignCopyPermissionToPlugin($type_id);
        $this->createPluginPermissions($type_id);

        ScaledFeedback\UnboundGlobalProvider::createGlobalProvider();

        return true;
    }

    protected function createPluginPermissions(int $type_id)
    {
        require_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

        $db = $this->getDIC()["ilDB"];

        $new_rbac_options = [
            ["view_evaluation", "User is able to view the evaluation", "object", 2700]
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

    /**
     * Check the permission is already created
     */
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

    protected function afterDeactivation()
    {
        ScaledFeedback\UnboundGlobalProvider::deleteGlobalProvider();
    }

    protected function isRepositoryPlugin(string $type) : bool
    {
        return substr($type, 0, 1) == "x";
    }

    protected function getTypeId(string $type) : int
    {
        $db = $this->getDIC()["ilDB"];

        $sql =
             "SELECT obj_id FROM object_data" . PHP_EOL
            . "WHERE type = " . $db->quote("typ", "text") . PHP_EOL
            . "AND title = " . $db->quote($type, "text") . PHP_EOL
        ;

        $result = $db->query($sql);

        if ($db->numRows($result) == 0) {
            return 0;
        }

        $rec = $db->fetchAssoc($result);
        return (int) $rec["obj_id"];
    }

    protected function createTypeId(string $type) : int
    {
        $db = $this->getDIC()["ilDB"];
        $type_id = $db->nextId("object_data");

        $sql =
             "INSERT INTO object_data" . PHP_EOL
            . "(obj_id, type, title, description, owner, create_date, last_update)" . PHP_EOL
            . "VALUES (" . PHP_EOL
            . $db->quote($type_id, "integer") . "," . PHP_EOL
            . $db->quote("typ", "text") . "," . PHP_EOL
            . $db->quote($type, "text") . "," . PHP_EOL
            . $db->quote("Plugin " . $this->getPluginName(), "text") . "," . PHP_EOL
            . $db->quote(-1, "integer") . "," . PHP_EOL
            . $db->quote(ilUtil::now(), "timestamp") . "," . PHP_EOL
            . $db->quote(ilUtil::now(), "timestamp") . PHP_EOL
            . ")" . PHP_EOL
        ;

        $db->manipulate($sql);

        return $type_id;
    }

    protected function assignCopyPermissionToPlugin(int $type_id)
    {
        $db = $this->getDIC()["ilDB"];

        $ops = array(self::COPY_OPERATION_ID);

        foreach ($ops as $op) {
            if (!$this->permissionIsAssigned($type_id, $op)) {
                $sql =
                     "INSERT INTO rbac_ta" . PHP_EOL
                    . "(typ_id, ops_id)" . PHP_EOL
                    . "VALUES" . PHP_EOL
                    . "(" . PHP_EOL
                    . $db->quote($type_id, "integer") . "," . PHP_EOL
                    . $db->quote($op, "integer") . PHP_EOL
                    . ")" . PHP_EOL
                ;

                $db->manipulate($sql);
            }
        }
    }

    /**
     * Checks permission is not assigned to plugin
     */
    protected function permissionIsAssigned(int $type_id, int $op_id) : bool
    {
        $db = $this->getDIC()["ilDB"];

        $set = $db->query("SELECT count(typ_id) as cnt FROM rbac_ta " .
                " WHERE typ_id = " . $db->quote($type_id, "integer") .
                " AND ops_id = " . $db->quote($op_id, "integer"));

        $rec = $db->fetchAssoc($set);

        return $rec["cnt"] > 0;
    }

    public function allowCopy() : bool
    {
        return true;
    }

    public function getPluginActions() : ScaledFeedback\ilPluginActions
    {
        if ($this->plugin_actions === null) {
            global $DIC;
            $db = $DIC->database();

            $this->plugin_actions = new ScaledFeedback\ilPluginActions(
                $this,
                $this->getConfigDB($db)
            );
        }

        return $this->plugin_actions;
    }

    public function getConfigDB() : ScaledFeedback\Config\ilDB
    {
        return $this->getDIC()["config.db"];
    }

    public function txtClosure()
    {
        return function ($code) {
            return $this->txt($code);
        };
    }
}
