<?php

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/class.ilObjCancellationFeeReportAccess.php';
require_once __DIR__ . '/class.ilCancellationFeeReportGUI.php';
require_once __DIR__ . '/Settings/class.ilCancellationFeeReportSettingsGUI.php';

use CaT\Plugins\CancellationFeeReport as CFR;
use ILIAS\TMS\Filter;
use ILIAS\TMS\TableRelations;
use ILIAS\TMS\ReportUtilities\TreeObjectDiscovery;
use Pimple\Container;

/**
 * Plugin base class. Keeps all information the plugin needs.
 *
 * @author
 * @copyright Extended GPL, see LICENSE
 */
class ilCancellationFeeReportPlugin extends ilRepositoryObjectPlugin
{
    const COPY_OPERATION_ID = 58;

    protected static $dic;

    /**
     * @var ilPluginActions
     */
    protected $plugin_actions;

    /**
     * Decides if this repository plugin can be copied.
     *
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
        return "CancellationFeeReport";
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

    public function init()
    {
        self::$dic = self::maybeInitDIC();
    }

    protected static function maybeInitDIC()
    {
        if (!self::$dic) {
            global $DIC;
            self::$dic = new Pimple\Container();
            self::$dic['Plugin'] = function ($dic) {
                return new self();
            };
            self::$dic['Report'] = function ($dic) use ($DIC) {
                $gf = new TableRelations\GraphFactory();
                $pf = new Filter\PredicateFactory();
                $tf = new TableRelations\TableFactory($pf, $gf);
                $tyf = new Filter\TypeFactory();
                $ff = new Filter\FilterFactory($pf, $tyf);
                return new CFR\Report(
                    $dic['UserOrguLocator'],
                    new ilTreeObjectDiscovery($DIC['tree']),
                    $dic['Plugin'],
                    $pf,
                    $tf,
                    $tyf,
                    $ff,
                    new TableRelations\SqlQueryInterpreter(
                        new Filter\SqlPredicateInterpreter($DIC['ilDB']),
                        $pf,
                        $DIC['ilDB']
                    ),
                    $DIC['ilUser'],
                    $DIC['ilDB']
                );
            };
            self::$dic['Settings.ilCancellationFeeReportSettingsGUI'] = function ($dic) use ($DIC) {
                return new ilCancellationFeeReportSettingsGUI(
                    $dic['Settings.SettingsRepository'],
                    $dic['Plugin'],
                    $DIC['ilCtrl'],
                    $DIC['ilAccess'],
                    $DIC['tpl']
                );
            };
            self::$dic['Settings.SettingsRepository'] = function ($dic) use ($DIC) {
                return new CFR\Settings\DBSettingsRepository($DIC['ilDB']);
            };
            self::$dic['ilCancellationFeeReportGUI'] = function ($dic) use ($DIC) {
                return new ilCancellationFeeReportGUI(
                    $dic['Report'],
                    $dic['Plugin'],
                    $DIC['ilCtrl'],
                    $DIC['ilAccess'],
                    $DIC['tpl'],
                    $DIC['ilUser']
                );
            };
            self::$dic['UserOrguLocator'] = function ($dic) use ($DIC) {
                return new CFR\UserOrguLocator(
                    \ilObjOrgUnitTree::_getInstance(),
                    $DIC['ilAccess'],
                    new \TMSPositionHelper(\ilOrgUnitUserAssignmentQueries::getInstance())
                );
            };
        }
        return self::$dic;
    }

    public static function dic()
    {
        return self::maybeInitDIC();
    }
}
