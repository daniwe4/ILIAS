<?php
require_once __DIR__ . "/../vendor/autoload.php";

use CaT\Plugins\BookingAcknowledge\BookingAcknowledge;

/**
 * Plugin base class. Keeps all information the plugin needs.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class ilBookingAcknowledgePlugin extends ilRepositoryObjectPlugin
{
    const COPY_OPERATION_ID = 58;

    public function getPluginName() : string
    {
        return BookingAcknowledge::PLUGIN_NAME;
    }

    public function allowCopy() : bool
    {
        return true;
    }

    public function useOrguPermissions() : bool
    {
        return true;
    }

    protected function uninstallCustom()
    {
    }

    /**
     * Assign permission copy to current plugin
     *
     * @param 	int 	$type_id
     * @param 			$db
     * @return 	int
     */
    protected function assignCopyPermissionToPlugin($type_id, $db)
    {
        $ops = array(self::COPY_OPERATION_ID);

        foreach ($ops as $op) {
            // check whether type exists in object data, if not, create the type
            if (!$this->permissionIsAssigned($type_id, $op, $db)) {
                $db->manipulate(
                    "INSERT INTO" . PHP_EOL
                    . "    rbac_ta" . PHP_EOL
                    . "    (typ_id, ops_id)" . PHP_EOL
                    . "VALUES" . PHP_EOL
                    . "    ("
                    . $db->quote($type_id, "integer") . ","
                    . $db->quote($op, "integer")
                    . "    )" . PHP_EOL
                );
            }
        }
    }

    /**
     * @inheritdoc
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

        $this->assignCopyPermissionToPlugin($type_id, $db);

        return true;
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
            "INSERT INTO" . PHP_EOL
            . "    object_data" . PHP_EOL
            . "    (" . PHP_EOL
            . "        obj_id," . PHP_EOL
            . "        type," . PHP_EOL
            . "        title," . PHP_EOL
            . "        description," . PHP_EOL
            . "        owner," . PHP_EOL
            . "        create_date," . PHP_EOL
            . "        last_update" . PHP_EOL
            . "    )" . PHP_EOL
            . "VALUES" . PHP_EOL
            . "    ("
            . $db->quote($type_id, "integer") . ","
            . $db->quote("typ", "text") . ","
            . $db->quote($type, "text") . ","
            . $db->quote("Plugin " . $this->getPluginName(), "text") . ","
            . $db->quote(-1, "integer") . ","
            . $db->quote(ilUtil::now(), "timestamp") . ","
            . $db->quote(ilUtil::now(), "timestamp")
            . "    )" . PHP_EOL
        );

        return $type_id;
    }

    protected function isRepositoryPlugin(string $type) : bool
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
            "SELECT" . PHP_EOL
            . "    obj_id" . PHP_EOL
            . "FROM" . PHP_EOL
            . "    object_data" . PHP_EOL
            . "WHERE" . PHP_EOL
            . "    type = " . $db->quote("typ", "text") . PHP_EOL
            . "AND" . PHP_EOL
            . "    title = " . $db->quote($type, "text") . PHP_EOL
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
            "SELECT" . PHP_EOL
            . "    count(typ_id) as cnt" . PHP_EOL
            . "FROM" . PHP_EOL
            . "    rbac_ta" . PHP_EOL
            . "WHERE" . PHP_EOL
            . "    typ_id = " . $db->quote($type_id, "integer") . PHP_EOL
            . "AND" . PHP_EOL
            . "    ops_id = " . $db->quote($op_id, "integer") . PHP_EOL
        );

        $rec = $db->fetchAssoc($set);

        return $rec["cnt"] > 0;
    }
}
