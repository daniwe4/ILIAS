<?php declare(strict_types = 1);

include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");
require_once 'Services/TMS/RepositoryPluginUtilities/HistorizedRepositoryPlugin.php';

use CaT\Plugins\CourseClassification;

/**
 * Plugin base class. Keeps all information the plugin needs
 */
class ilCourseClassificationPlugin extends ilRepositoryObjectPlugin implements HistorizedRepositoryPlugin
{
    const COPY_OPERATION_ID = 58;

    /**
     * @var ilLogger
     */
    protected $logger;

    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC['ilLog'];

        parent::__construct();
    }
    /**
     * Get the name of the Plugin
     *
     * @return string
     */
    public function getPluginName()
    {
        return "CourseClassification";
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

        $type_id = $this->getTypeId($type, $db);
        if (!$type_id) {
            $type_id = $this->createTypeId($type, $db);
        }

        $this->assignCopyPermissionToPlugin($type_id, $db);

        //on activation, also install global provider
        CourseClassification\UnboundGlobalProvider::createGlobalProvider();

        return true;
    }

    protected function afterDeactivation()
    {
        //on deactivation, also de-install global provider
        CourseClassification\UnboundGlobalProvider::deleteGlobalProvider();
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
     * Get actions for optin by type
     *
     * @param string 	$type
     *
     * @return ilActions
     */
    public function getActionsByType($type)
    {
        if ($this->actions[$type] === null) {
            global $DIC;

            $actions_class = "CaT\\Plugins\\CourseClassification\\Options\\$type\\ilActions";
            $db_class = "CaT\\Plugins\\CourseClassification\\Options\\$type\\ilDB";
            $this->actions[$type] = new $actions_class($this, new $db_class($DIC->database()), $DIC["ilAppEventHandler"]);
        }

        return $this->actions[$type];
    }

    /**
     * Get actions for repo object
     *
     * @return CourseClassification\ilActions
     */
    public function getActions()
    {
        if ($this->actions === null) {
            $this->actions = new CourseClassification\ilPluginActions($this, $this->getSettingsDB());
        }

        return $this->actions;
    }

    /**
     * Get db for settings
     *
     * @return CourseClassification\Settings\DB
     */
    protected function getSettingsDB()
    {
        if ($this->settings_db === null) {
            global $DIC;
            $this->settings_db = new CourseClassification\Settings\ilDB($DIC->database());
        }

        return $this->settings_db;
    }

    /**
     * HistorizedRepositoryPlugin implementations
     */
    public function getObjType() : string
    {
        return 'xccl';
    }
    public function getEmptyPayload() : array
    {
        return [
            'topics' => []
            ,'crs_type' => ''
            ,'edu_programme' => ''
            ,'categories' => []
        ];
    }
    public function getTree() : \ilTree
    {
        global $DIC;
        return $DIC['tree'];
    }
    public function extractPayloadByPluginObject(\ilObjectPlugin $obj) : array
    {
        assert('$obj instanceof ilObjCourseClassification');
        list($type_id, $type, $target_group_ids, $target_group, $goals, $topic_ids, $topics) = $obj->getCourseClassificationValues();
        $return = [];
        $return['topics'] = is_null($topics) ? [] : $topics ;
        $return['crs_type'] = $type;
        $actions = $obj->getActions();
        $classifications = $obj->getCourseClassification();
        $return['edu_programme'] = array_shift($actions->getEduProgramName($classifications->getEduProgram()));
        $return['categories'] = $actions->getCategoryNames($actions->getCategoriesByTopicIds($topic_ids));
        return $return;
    }
    public function relevantHistCases() : array
    {
        return ['crs'];
    }

    /**
     * @return CourseClassification\Options\Topic\Topic
     */
    public function getCourseTopics() : array
    {
        return $this->getActionsByType("Topic")->getTableData();
    }

    /**
     * Handle an (update) event.
     *
     * @param string	$a_component
     * @param string	$a_event
     * @param mixed		$a_parameter
     *
     * @return void
     */
    public function handleEvent($a_component, $a_event, $a_parameter)
    {
        if (
            $a_component !== 'Plugin/CourseClassification' ||
            $a_event !== 'update'
        ) {
            return;
        }

        $cc_ref = $a_parameter['ref_id'];
        $cc_data = $a_parameter['data']; //course_classification

        $parent_crs = $this->getParentCourse((int)$cc_ref);

        if ($parent_crs === null) {
            return;
        }

        //check, if this is the only or earliest instance of CC within the course
        $cc = $this->getFirstClassificationBelow((int)$parent_crs->getRefid());
        if ($cc->getRefId() !== $cc_ref) {
            $msg = 'CourseClassification Event: do not update course by ref_id '
                . $cc_ref
                . ' (not the first xccl below course)';
            $this->logger->write($msg);
            return;
        }

        //update course object (syllabus, contact)
        $parent_crs->setSyllabus($cc_data->getContent());

        $parent_crs->setContactName($cc_data->getContact()->getName());
        $parent_crs->setContactResponsibility($cc_data->getContact()->getResponsibility());
        $parent_crs->setContactPhone($cc_data->getContact()->getPhone());
        $parent_crs->setContactEmail($cc_data->getContact()->getMail());

        $parent_crs->update();
    }

    protected function getFirstClassificationBelow(int $ref_id)
    {
        $childs = $this->getTree()->getChilds((int) $ref_id);
        foreach ($childs as $child) {
            $type = $child["type"];
            if ($type == "xccl") {
                return \ilObjectFactory::getInstanceByRefId($child["child"]);
            }
            if ($this->obj_def->isContainer($type)) {
                $ret = $this->getFirstClassificationBelow((int)$child["child"]);
                if (!is_null($ret)) {
                    return $ret;
                }
            }
        }

        return null;
    }

    /**
     * Get parent course of cc object
     * @param int 	$cc_ref
     * @return bool|ilObject
     */
    public function getParentCourse(int $cc_ref)
    {
        $parents = $this->getTree()->getPathFull($cc_ref);
        $parent = array_filter($parents, function ($p) {
            if ($p["type"] == "crs") {
                return $p;
            }
        });
        if (count($parent) > 0) {
            $parent_crs = array_shift($parent);
            require_once("Services/Object/classes/class.ilObjectFactory.php");
            return ilObjectFactory::getInstanceByRefId($parent_crs["ref_id"]);
        }
        return null;
    }
}
