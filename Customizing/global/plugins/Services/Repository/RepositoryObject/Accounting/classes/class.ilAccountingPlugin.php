<?php declare(strict_types = 1);

use CaT\Plugins\Accounting;
use \CaT\Plugins\Accounting\DI;
use CaT\Plugins\Accounting\Fees\CancellationFee\CancellationFee;
use CaT\Plugins\Accounting\Config\Cancellation\Scale\Scale;

require_once __DIR__ . "/../vendor/autoload.php";

/**
 * Plugin base class. Keeps all information the plugin needs
 *
 * @author Daniel Weise 	<daniel.weise@concepts-and-training.de>
 */
class ilAccountingPlugin extends ilRepositoryObjectPlugin implements HistorizedRepositoryPlugin
{
    use DI;

    const COPY_OPERATION_ID = 58;

    /**
     * @var Accounting\ilObjectActions
     */
    protected $actions;

    /**
     * @var Accounting\Settings\ilDB
     */
    protected $settings_db;

    /**
     * @var Accounting\Config\CostType\ilDB
     */
    protected $costtype_db;

    /**
     * @var Accounting\Config\VatRate\ilDB
     */
    protected $vat_rate_db;

    /**
     * Get the name of the Plugin
     *
     * @return string
     */
    public function getPluginName()
    {
        return "Accounting";
    }

    /**
     * Defines custom uninstall action like delete table or something else
     */
    protected function uninstallCustom()
    {
    }

    protected function afterDeactivation()
    {
        //on deactivation, also de-install global provider
        Accounting\UnboundGlobalProvider::deleteGlobalProvider();
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function handleEvent($a_component, $a_event, $a_parameter)
    {
        if (
            $a_component == "Services/Tracking" &&
            $a_event == "updateStatus" &&
            $this->isCourse((int) $a_parameter["obj_id"])
        ) {
            $this->handleParticipationStatusEvent($a_parameter);
        }

        if (
            $a_component == "Services/AccessControl" &&
            $a_event == "deassignUser" &&
            $a_parameter["type"] == "crs"
        ) {
            $this->handleCancelEvent($a_parameter);
        }
    }

    protected function handleCancelEvent($a_parameter)
    {
        $user_id = (int) $a_parameter["usr_id"];
        $crs_id = (int) $a_parameter["obj_id"];

        $cancellation_fee = $this->getCancellationFeeFor($crs_id, $user_id);

        if (is_null($cancellation_fee)) {
            return;
        }

        $this->throwEvent($user_id, $crs_id, $cancellation_fee);
    }

    /**
     * @return float | null
     */
    public function getCancellationFeeFor(int $crs_id, int $usr_id)
    {
        if (!$this->hasValuableGlobalRole($usr_id)) {
            return null;
        }

        /**
         * @var ilObjCourse $crs
         */
        $crs = \ilObjectFactory::getInstanceByObjId($crs_id);
        if (is_null($crs->getCourseStart())) {
            return null;
        }

        $max_cancellation_fee = $this->getMaxCancellationFeeFor($crs_id);

        if (is_null($max_cancellation_fee)) {
            return null;
        }

        $cancellation_fee = $max_cancellation_fee;
        $start_date = new DateTime($crs->getCourseStart()->get(IL_CAL_DATE));
        $today = new DateTime(date("Y-m-d"));
        if ($start_date->format("Y-m-d") > $today->format("Y-m-d")) {
            $diff = $start_date->diff($today);
            $days = (int) $diff->format("%a");

            /**
             * @var Scale | null $sdale
             */
            $scale = $this->getScaleForDays($days);
            if (is_null($scale)) {
                return null;
            }

            $cancellation_fee = $this->calcCancellationFee($scale, $max_cancellation_fee);
        }

        return $cancellation_fee;
    }

    protected function handleParticipationStatusEvent($a_parameter)
    {
        $user_id = (int) $a_parameter["usr_id"];
        $crs_id = (int) $a_parameter["obj_id"];
        $status = (int) $a_parameter['status'];

        if (!$this->hasValuableGlobalRole($user_id)) {
            return;
        }

        if (!$this->hasValuableParticipationStatus($status)) {
            return;
        }

        /**
         * @var ilObjCourse $crs
         */
        $crs = \ilObjectFactory::getInstanceByObjId($crs_id);
        if (is_null($crs->getCourseStart())) {
            return;
        }

        $max_cancellation_fee = $this->getMaxCancellationFeeFor($crs_id);
        if (is_null($max_cancellation_fee)) {
            return;
        }

        $this->throwEvent($user_id, $crs_id, $max_cancellation_fee);
    }

    protected function throwEvent(int $user_id, int $crs_id, float $fee)
    {
        $param = [
            "crs_id" => $crs_id,
            "usr_id" => $user_id,
            "cancellation_fee" => $fee
        ];
        $this->getDic()["ilAppEventHandler"]->raise("Plugin/Accounting", "userCancellationFee", $param);
    }

    protected function hasValuableGlobalRole(int $user_id)
    {
        $dic = $this->getDIC();
        $config_roles = $dic["config.cancellation.roles.db"]->getRoles();

        if (count($config_roles) == 0) {
            return false;
        }

        $user_roles = $dic["rbacreview"]->assignedGlobalRoles($user_id);
        return count(array_intersect($user_roles, $config_roles)) > 0;
    }

    /**
     * @return Accounting\Config\Cancellation\Scale\Scale | null
     */
    protected function getScaleForDays(int $days)
    {
        try {
            return $this->getDIC()["config.cancellation.scale.db"]->getScaleFor($days);
        } catch (LogicException $e) {
            return null;
        }
    }

    /**
     * @param int $crs_id
     * @return float|null
     */
    protected function getMaxCancellationFeeFor(int $crs_id)
    {
        return $this->getDIC()["fees.cancel.db"]->selectForCourse($crs_id);
    }

    protected function calcCancellationFee(Scale $scale, float $max_cancellation_fee) : float
    {
        $percent = $scale->getPercent();
        return $max_cancellation_fee / 100 * $percent;
    }

    protected function hasValuableParticipationStatus(int $status) : bool
    {
        $states = $this->getDIC()["config.cancellation.states.db"]->getILIASStatesNum();
        return in_array($status, $states);
    }

    protected function isCourse(int $obj_id) : bool
    {
        return ilObject::_lookupType($obj_id) == "crs";
    }

    /**
     * Get an ilPluginActions object
     */
    public function getPluginActions()
    {
        if ($this->actions === null) {
            $costtype_db = $this->getAccountingConfigCostTypeDB();
            $vat_reate_db = $this->getAccountingConfigVatRateDB();
            $this->actions = new Accounting\ilPluginActions($costtype_db, $vat_reate_db);
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
     * Get a config cost type db object
     */
    protected function getAccountingConfigCostTypeDB()
    {
        global $ilDB, $ilUser;
        if ($this->costtype_db === null) {
            $this->costtype_db = new Accounting\Config\CostType\ilDB($ilDB, $ilUser);
        }
        return $this->costtype_db;
    }

    /**
     * Get a config vat rate db object
     */
    protected function getAccountingConfigVatRateDB()
    {
        global $ilDB, $ilUser;
        if ($this->vat_rate_db === null) {
            $this->vat_rate_db = new Accounting\Config\VatRate\ilDB($ilDB, $ilUser);
        }
        return $this->vat_rate_db;
    }


    public function getParentTypes()
    {
        return array("crs", "fold");
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

        Accounting\UnboundGlobalProvider::createGlobalProvider();

        return true;
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
        $new_rbac_options = array(
            array("edit_entries", "Edit entries", "object", 8151),
            array("delete_entries", "Delete entries", "object", 8152),
            array("add_entries", "Add entries", "object", 8153),
            array("finalize_recording", "Finalize recording", "object", 8200),
            array("cancel_finalize", "Cancel finalize", "object", 8201)
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
            $db->quote("Plugin " . $this->getAccomodation(), "text") . "," .
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
     * HistorizedRepositoryPlugin implementations
     */
    public function getObjType() : string
    {
        return 'xacc';
    }
    public function getEmptyPayload() : array
    {
        return [
            'net_total_cost' => 0.0,
            'gross_total_cost' => 0.0,
            'costcenter_finalized' => false,
            'fee' => 0.0
        ];
    }
    public function getTree() : \ilTree
    {
        global $DIC;
        return $DIC['tree'];
    }
    public function extractPayloadByPluginObject(\ilObjectPlugin $obj) : array
    {
        assert('$obj instanceof ilObjAccounting');
        $actions = $obj->getObjectActions();
        return [
            'net_total_cost' => $actions->getNetSum(),
            'gross_total_cost' => $actions->getGrossSum(),
            'costcenter_finalized' => $obj->getSettings()->getFinalized(),
            'fee' => $actions->getFeeActions()->select()->getFee()
        ];
    }
    public function relevantHistCases() : array
    {
        return ['crs'];
    }

    protected function getDIC()
    {
        global $DIC;
        return $this->getPluginDIC($this, $DIC);
    }
}
