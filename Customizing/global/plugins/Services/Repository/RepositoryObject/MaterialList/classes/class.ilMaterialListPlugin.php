<?php
include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");
require_once(__DIR__ . "/../vendor/autoload.php");

use \CaT\Plugins\MaterialList;

/**
 * Plugin base class. Keeps all information the plugin needs
 */
class ilMaterialListPlugin extends ilRepositoryObjectPlugin
{
    const COPY_OPERATION_ID = 58;

    /**
     * @var MaterialList\ilPluginActions
     */
    protected $plugin_actions;

    /**
     * @var MaterialList\Settings\ilDB
     */
    protected $settings_db;

    /**
     * @var MaterialList\HeaderConfiguration\ilDB
     */
    protected $header_configuration_db;

    /**
     * @var MaterialList\Materials\ilDB
     */
    protected $materials_db;

    /**
     * @var MaterialList\Lists\ilDB
     */
    protected $lists_db;

    /**
     * @var MaterialList\RPC\FolderReader
     */
    protected $folder_reader;

    /**
     * @var MaterialList\HeaderConfiguration\XLSHeaderOptions
     */
    protected $xls_header_options;

    /**
     * @var MaterialList\HeaderConfiguration\XLSHeaderExport
     */
    protected $xls_header_export;

    /**
     * Get the name of the Plugin
     *
     * @return string
     */
    public function getPluginName()
    {
        return "MaterialList";
    }

    /**
     * Defines custom uninstall action like delete table or something else
     */
    protected function uninstallCustom()
    {
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
     * Get an intance of ilPluginActions
     *
     * @return MaterialList\ilPluginActions
     */
    public function getActions()
    {
        if ($this->plugin_actions === null) {
            global $DIC;
            $db = $DIC->database();
            $settings = $DIC["ilSetting"];

            $this->plugin_actions = new MaterialList\ilPluginActions(
                $this,
                $this->getMaterialsDB($db),
                $settings
            );
        }

        return $this->plugin_actions;
    }

    /**
     * Get settings db
     *
     * @return MaterialList\Settings\ilDB
     */
    public function getSettingsDB($db)
    {
        if ($this->settings_db === null) {
            $this->settings_db = new MaterialList\Settings\ilDB($db);
        }

        return $this->settings_db;
    }

    /**
     * Get header configuration db
     *
     * @return MaterialList\HeaderConfiguration\ilDB
     */
    public function getHeaderConfigurationDB($db)
    {
        if ($this->header_configuration_db === null) {
            $this->header_configuration_db = new MaterialList\HeaderConfiguration\ilDB($db);
        }

        return $this->header_configuration_db;
    }

    /**
     * Get materials db
     *
     * @return MaterialList\Materials\ilDB
     */
    protected function getMaterialsDB($db)
    {
        if ($this->materials_db === null) {
            $this->materials_db = new MaterialList\Materials\ilDB($db);
        }

        return $this->materials_db;
    }

    /**
     * Get lists db
     *
     * @return MaterialList\Lists\ilDB
     */
    public function getListsDB($db)
    {
        if ($this->lists_db === null) {
            $this->lists_db = new MaterialList\Lists\ilDB($db);
        }

        return $this->lists_db;
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
     * Get the folder loader
     *
     * @return MaterialList\RPC\FolderReader
     */
    public function getFolderReader()
    {
        if ($this->folder_reader === null) {
            $this->folder_reader = new MaterialList\RPC\FolderReader($this->txtClosure());
        }

        return $this->folder_reader;
    }

    /**
     * Get the procedure loader
     *
     * @return MaterialList\RPC\ProcedureLoader
     */
    public function getProcedureLoader()
    {
        if ($this->procedure_loader === null) {
            $this->procedure_loader = new MaterialList\RPC\ProcedureLoader(__DIR__ . "/RPC/Procedures", $this->txtClosure());
        }

        return $this->procedure_loader;
    }

    /**
     * Get the xls header options
     *
     * @return MaterialList\HeaderConfiguration\XLSHeaderOptions
     */
    public function getXLSHeaderOptions()
    {
        if ($this->xls_header_options === null) {
            $this->xls_header_options = new MaterialList\HeaderConfiguration\XLSHeaderOptions($this->getFolderReader());
        }
        return $this->xls_header_options;
    }

    /**
     * Get the xls header export
     *
     * @return MaterialList\HeaderConfiguration\XLSHeaderExport
     */
    public function getXLSHeaderExport()
    {
        if ($this->xls_header_export === null) {
            $this->xls_header_export = new MaterialList\HeaderConfiguration\XLSHeaderExport($this->getProcedureLoader());
        }
        return $this->xls_header_export;
    }

    /**
     * Get exporter for lists.
     *
     * @param ilObjMaterialList[] 	$xmat_objs
     * @return Lists\Export\Exporter
     */
    public function getListXLSExporter(array $xmat_objs)
    {
        global $DIC;
        $db = $DIC->database();
        $header_db = $this->getHeaderConfigurationDB($db);

        return new MaterialList\Lists\Export\Exporter(
            $this->getXLSHeaderExport(),
            $header_db->selectAll(),
            $this->txtClosure(),
            $xmat_objs
        );
    }

    /**
     * Handle events.
     *
     * @param 	string	$a_component
     * @param 	string	$a_event
     * @param 	array <string, mixed>		$a_parameter
     *
     * @return 	void
     */
    public function handleEvent($a_component, $a_event, array $a_parameter)
    {
        assert('is_string($a_component)');
        assert('is_string($a_event)');

        global $DIC;
        $logger = $DIC['ilLog'];

        //deal with course-update: re-schedule mailing-events
        if ($a_component === 'Modules/Course' && $a_event === 'update') {
            $crs_ref = $a_parameter['object']->getRefId();
            if (!$crs_ref) {
                $refs = \ilObject::_getAllReferences($a_parameter['object']->getId());
                if (!$refs) {
                    return;
                } else {
                    $crs_ref = array_shift($refs);
                }
            }

            $objs = $this->getAllChildrenOfByType((int) $crs_ref, 'xmat');
            foreach ($objs as $xmat_obj) {
                $logger->write('update mailing events (MaterialList) on course-change for ref ' . $crs_ref);
                $xmat_obj->scheduleMailingEvents();
            }
        }
    }

    /**
     * Get all children by type recursive
     *
     * @param 	int 	$ref_id
     * @param 	string 	$search_type
     *
     * @return 	Object 	of search type
     */
    protected function getAllChildrenOfByType($ref_id, $search_type)
    {
        assert('is_int($ref_id)');
        assert('is_string($search_type)');

        global $DIC;
        $g_tree = $DIC->repositoryTree();
        $g_objDefinition = $DIC["objDefinition"];

        $childs = $g_tree->getChilds($ref_id);
        $ret = array();

        foreach ($childs as $child) {
            $type = $child["type"];
            if ($type == $search_type) {
                $ret[] = \ilObjectFactory::getInstanceByRefId((int) $child["child"]);
            }

            if ($g_objDefinition->isContainer($type)) {
                $rec_ret = $this->getAllChildrenOfByType((int) $child["child"], $search_type);
                if (!is_null($rec_ret)) {
                    $ret = array_merge($ret, $rec_ret);
                }
            }
        }
        return $ret;
    }
}
