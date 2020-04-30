<?php

/* Copyright (c) 2018 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use \CaT\Plugins\Agenda\DI;

class ilAgendaPlugin extends ilRepositoryObjectPlugin
{
    use DI;

    const COPY_OPERATION_ID = 58;

    /**
     * @var Pimple\Container
     */
    protected $dic;

    public function getPluginName()
    {
        return 'Agenda';
    }

    public function uninstallCustom()
    {
    }

    /**
     * @return bool
     * @throws ilPluginException
     */
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

        $this->assignCopyPermissionToPlugin((int) $type_id, $db);
        $this->createPluginPermissions((int) $type_id, $db);
        $this->deletePluginPermissions();

        return true;
    }

    protected function createPluginPermissions(int $type_id, ilDBInterface $db)
    {
        $new_rbac_options = array(
            array("edit_agenda_entries", "Edit entries", "object", 8251),
            array("delete_agenda_entries", "Delete entries", "object", 8252),
            array("view_agenda_entries", "View entries", "object", 8254)
        );

        foreach ($new_rbac_options as $value) {
            if (!$this->permissionExists($value[0], $db)) {
                require_once "Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php";

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

    protected function deletePluginPermissions()
    {
        require_once "Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php";

        $id = ilDBUpdateNewObjectType::getCustomRBACOperationId("add_agenda_entries");

        if (!is_null($id) && $id != "") {
            ilDBUpdateNewObjectType::deleteRBACOperation("xage", (int) $id);
        }
    }

    protected function permissionExists(string $permission, ilDBInterface $db) : bool
    {
        $query =
             "SELECT count(ops_id) AS cnt FROM rbac_operations" . PHP_EOL
            . " WHERE operation = " . $db->quote($permission, 'text') . PHP_EOL
        ;

        $res = $db->query($query);
        $row = $db->fetchAssoc($res);

        return $row["cnt"] > 0;
    }

    protected function isRepositoryPlugin(string $type) : bool
    {
        return substr($type, 0, 1) == "x";
    }

    /**
     * @param string $type
     * @param $db
     * @return int | null
     */
    protected function getTypeId(string $type, ilDBInterface $db)
    {
        $sql =
             "SELECT obj_id FROM object_data" . PHP_EOL
            . "WHERE type = " . $db->quote("typ", "text") . PHP_EOL
            . "AND title = " . $db->quote($type, "text") . PHP_EOL
        ;

        $result = $db->query($sql);

        if ($db->numRows($result) == 0) {
            return null;
        }

        $rec = $db->fetchAssoc($result);
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
    protected function createTypeId(string $type, ilDBInterface $db)
    {
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

    protected function assignCopyPermissionToPlugin(int $type_id, ilDBInterface $db)
    {
        $ops = array(self::COPY_OPERATION_ID);

        foreach ($ops as $op) {
            if (!$this->permissionIsAssigned($type_id, $op, $db)) {
                $sql =
                     "INSERT INTO rbac_ta" . PHP_EOL
                    . "(typ_id, ops_id)" . PHP_EOL
                    . "VALUES (" . PHP_EOL
                    . $db->quote($type_id, "integer") . "," . PHP_EOL
                    . $db->quote($op, "integer") . PHP_EOL
                    . ")" . PHP_EOL
                ;

                $db->manipulate($sql);
            }
        }
    }

    protected function permissionIsAssigned(int $type_id, int $op_id, ilDBInterface $db) : bool
    {
        $sql =
             "SELECT count(typ_id) as cnt" . PHP_EOL
            . "FROM rbac_ta" . PHP_EOL
            . "WHERE typ_id = " . $db->quote($type_id, "integer") . PHP_EOL
            . "AND ops_id = " . $db->quote($op_id, "integer") . PHP_EOL
        ;

        $set = $db->query($sql);
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

    public function editFixedBlocks() : bool
    {
        return $this->getDic()["config.blocks.db"]->selectBlockConfig()->isEditFixedBlocks();
    }

    protected function getDic() : Pimple\Container
    {
        if (is_null($this->dic)) {
            global $DIC;
            $this->dic = $this->getPluginDIC($this, $DIC);
        }
        return $this->dic;
    }
}
