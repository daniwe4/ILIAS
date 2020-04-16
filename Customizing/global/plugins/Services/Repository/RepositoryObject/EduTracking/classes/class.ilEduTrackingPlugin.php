<?php

declare(strict_types = 1);

require_once 'Services/Repository/classes/class.ilRepositoryObjectPlugin.php';
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/Purposes/WBD/IliasWBDUserDataProvider.php";
require_once __DIR__ . "/Purposes/WBD/IliasWBDObjectProvider.php";
require_once 'Services/TMS/RepositoryPluginUtilities/HistorizedRepositoryPlugin.php';

use CaT\Plugins\EduTracking\UnboundGlobalProvider;
use CaT\Plugins\EduTracking as ET;

class ilEduTrackingPlugin extends ilRepositoryObjectPlugin implements HistorizedRepositoryPlugin
{
    const COPY_OPERATION_ID = 58;

    public function getPluginName()
    {
        return 'EduTracking';
    }

    public function uninstallCustom()
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
        $new_rbac_options = array(array("edit_purposes", "Edit settings of purposes", "object", 7000));

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
     * Get config actions for type
     *
     * @var string 	$purpose_name
     * @throws Exception if purpose is not found
     *
     * @return ilActions
     */
    public function getConfigActionsFor($purpose_name)
    {
        $purpose_name = strtoupper($purpose_name);
        switch ($purpose_name) {
            case "WBD":
                return new \CaT\Plugins\EduTracking\Purposes\WBD\Configuration\ilActions($this, $this->getConfigDBFor($purpose_name));
            case "IDD":
                return new \CaT\Plugins\EduTracking\Purposes\IDD\Configuration\ilActions($this, $this->getConfigDBFor($purpose_name));
            case "GTI":
                return new \CaT\Plugins\EduTracking\Purposes\GTI\Configuration\ilActions($this, $this->getConfigDBFor($purpose_name));
            default:
                throw new Exception("unknown purpose type");
        }
    }

    /**
     * Get config actions for type
     *
     * @var string 	$purpose_name
     *
     * @return ilActions
     */
    public function getConfigDBFor($purpose_name)
    {
        $purpose_name = strtoupper($purpose_name);
        global $DIC;
        switch ($purpose_name) {
            case "WBD":
                return new \CaT\Plugins\EduTracking\Purposes\WBD\Configuration\ilDB($DIC->database());
            case "IDD":
                return new \CaT\Plugins\EduTracking\Purposes\IDD\Configuration\ilDB($DIC->database());
            case "GTI":
                return new \CaT\Plugins\EduTracking\Purposes\GTI\Configuration\ilDB($DIC->database());
            default:
                throw new Exception("unknown purpose type");
        }
    }


    /**
     * HistorizedRepositoryPlugin implementations
     */
    public function getObjType() : string
    {
        return 'xetr';
    }
    public function getEmptyPayload() : array
    {
        return [
            'idd_learning_time' => 0
            ,'gti_learning_time' => 0
            ,'wbd_learning_type' => ''
            ,'wbd_learning_content' => ''
            ,'internal_id' => ''
            ,'gti_category' => ''
        ];
    }
    public function getTree() : \ilTree
    {
        global $DIC;
        return $DIC['tree'];
    }
    protected function loadGTICategoryMap() : array
    {
        $return = [];
        foreach ($this->getConfigActionsFor("GTI")->selectCategories() as $obj) {
            $return[(int) $obj->getId()] = $obj->getName();
        }
        return $return;
    }
    protected static $gti_category_map;
    protected function gtiTitleById(int $id) : string
    {
        if (!self::$gti_category_map) {
            self::$gti_category_map = $this->loadGTICategoryMap();
        }
        if ($id > 0 && array_key_exists($id, self::$gti_category_map)) {
            return self::$gti_category_map[$id];
        }
        return '';
    }
    public function extractPayloadByPluginObject(\ilObjectPlugin $obj) : array
    {
        assert('$obj instanceof ilObjEduTracking');
        $idd_minutes = (int) $obj->getDBFor('IDD')->selectFor($obj)->getMinutes();
        $gti_data = $obj->getDBFor('GTI')->selectFor($obj);
        $gti_minutes = (int) $gti_data->getMinutes();

        $config = $obj->getConfigWBD();
        if (is_null($config)) {
            $payload = $this->getEmptyPayload();
            $payload['idd_learning_time'] = $idd_minutes;
            $payload['gti_learning_time'] = $gti_minutes;
            $payload['gti_category'] = $this->gtiTitleById((int) $gti_data->getCategoryId());
            return $payload;
        }

        $wbd_data_interface = new ET\Purposes\WBD\WBDDataInterface(
            $obj->getDBFor('WBD')->selectFor($obj),
            $config,
            $obj->getWBDUserDataProvider(),
            $obj->getWBDObjectProvider()
        );
        $parent_crs_ref = $obj->getParentCourse()->getRefId();
        return [
            'idd_learning_time' => $idd_minutes
            ,'gti_learning_time' => $gti_minutes
            ,'wbd_learning_type' => $wbd_data_interface->getEducationType()
            ,'wbd_learning_content' => $wbd_data_interface->getEducationContent()
            ,'internal_id' => str_replace(
                '{REF_ID}',
                $parent_crs_ref,
                $wbd_data_interface->getInternalId()
            )
            ,'gti_category' => $this->gtiTitleById((int) $gti_data->getCategoryId())
        ];
    }

    public function relevantHistCases() : array
    {
        return ['crs','wbd_crs'];
    }

    public function getCourseTrainingtimeInMinutes(int $ref_id)
    {
        $sessions = $this->getAllChildrenOfByType($ref_id, "sess");

        if (count($sessions) > 0) {
            $sum = 0;

            foreach ($sessions as $session) {
                $appointments = $session->getAppointments();
                foreach ($appointments as $appointment) {
                    $start_time = $appointment->getStartingTime();
                    $end_time = $appointment->getEndingTime();
                    $sum += ($end_time - $start_time);
                }
            }

            return $sum / 60;
        }

        return 0;
    }

    /**
     * Get all children by type recursive
     *
     * @param int 	$ref_id
     * @param string 	$search_type
     *
     * @return ilObject[] 	of search type
     */
    public function getAllChildrenOfByType($ref_id, $search_type)
    {
        global $DIC;
        $g_tree = $DIC->repositoryTree();
        $ret = array();
        foreach ($g_tree->getSubTree($g_tree->getNodeData($ref_id), false, $search_type) as $child) {
            $ret[] = \ilObjectFactory::getInstanceByRefId($child);
        }

        return $ret;
    }

    public function handleEvent($component, $event, $parameter)
    {
        $gti_config = $this->getConfigActionsFor("GTI")->select();

        if (!is_null($gti_config) && $gti_config->getAvailable()) {
            if (($component == "Modules/Session" && $event == "update_appointment")
            ) {
                $parent_crs_ref_id = $this->getParentCourseRefId((int) $parameter['ref_id']);
                if ($parent_crs_ref_id) {
                    $this->updateEduTrackings($parent_crs_ref_id);
                }
            }

            if (($component == "Modules/Session" && $event == "update")) {
                if ($obj_ref_id = (int) $parameter['object']->getRefId()) {
                    $parent_crs_ref_id = $this->getParentCourseRefId($obj_ref_id);
                    if ($parent_crs_ref_id) {
                        $this->updateEduTrackings($parent_crs_ref_id);
                    }
                }
            }

            if ($component == "Services/Tree" && $event === 'moveTree' && ilObjectFactory::getTypeByRefId($parameter['source_id']) === 'sess') {
                $this->updateEduTrackings((int) $parameter['old_parent_id']);
                $this->updateEduTrackings((int) $parameter['target_id']);
            }

            if ($component == "Services/Object" && $parameter['type'] === 'sess' && $event === 'delete') {
                $this->updateEduTrackings((int) $parameter['old_parent_ref_id']);
            }
        }
    }

    private function getParentCourseRefId(int $ref_id) : int
    {
        global $DIC;
        foreach ($DIC['tree']->getPathFull($ref_id) as $node) {
            if ($node['type'] === 'crs') {
                return (int) $node['ref_id'];
            }
        }
        return 0;
    }

    public function updateEduTrackings(int $parent_crs_ref_id)
    {
        $minutes = $this->getCourseTrainingtimeInMinutes($parent_crs_ref_id);
        $edu_trackings = $this->getAllChildrenOfByType($parent_crs_ref_id, "xetr");
        if (count($edu_trackings) > 0) {
            foreach ($edu_trackings as $edu_tracking) {
                $settings = $edu_tracking->getDBFor('GTI')->selectFor($edu_tracking);
                if (!$settings->getSetTrainingTimeManually()) {
                    $settings = $settings->withMinutes($minutes);
                    $settings->update();
                }
            }
        }
    }
}
