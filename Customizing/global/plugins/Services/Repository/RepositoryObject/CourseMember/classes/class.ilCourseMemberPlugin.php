<?php
include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");
include_once("Services/Tracking/classes/class.ilLPStatus.php");
require_once(__DIR__ . "/../vendor/autoload.php");

use CaT\Plugins\CourseMember;
use CaT\Plugins\CourseMember\SignatureList\ConfigurableList\DBConfigurableListConfigRepo;
use CaT\Plugins\CourseMember\SignatureList\ConfigurableList\TMSAttendanceList;

/**
 * Plugin base class. Keeps all information the plugin needs
 */
class ilCourseMemberPlugin extends ilRepositoryObjectPlugin
{
    use CourseMember\DI;

    const COPY_OPERATION_ID = 58;

    /**
     * @var CaT\Plugins\CourseMember\LPOptions\ilActions
     */
    protected $lp_option_actions;

    /**
     * @var CaT\Plugins\CourseMember\LPOptions\ilDB
     */
    protected $lp_option_db;

    /**
     * Get the name of the Plugin
     *
     * @return string
     */
    public function getPluginName()
    {
        return "CourseMember";
    }

    /**
     * @inheritdoc
     */
    protected function uninstallCustom()
    {
        $lp_option_db = $this->getLPOptionsDB();
        $lp_option_db->deleteAll();
        $lp_option_db->dropSequence();
        $lp_option_db->dropTable();
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
        $this->createPluginPermissions((int) $type_id, $db);
        //on activation, also install global provider
        CourseMember\UnboundGlobalProvider::createGlobalProvider();

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
        $new_rbac_options = array(array("edit_lp_mode", "Edit settings of learning progress", "object", 2720),
            array("edit_lp", "Edit user learning progress", "object", 2710),
            array("view_lp", "View user learning progress", "object", 2700)
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
     * @param \ilDBInterface 	$db
     *
     * @return int
     */
    protected function createTypeId($type, \ilDBInterface $db)
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
     * @param \ilDBInterface 	$db
     *
     * @return int
     */
    protected function assignCopyPermissionToPlugin($type_id, \ilDBInterface $db)
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
     * @param \ilDBInterface $db
     *
     * @return bool
     */
    protected function permissionIsAssigned($type_id, $op_id, \ilDBInterface $db)
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
     * Get actions for lp options
     *
     * @return LPOptions\ilActions
     */
    public function getLPOptionActions()
    {
        if ($this->lp_option_actions === null) {
            $this->lp_option_actions = new CaT\Plugins\CourseMember\LPOptions\ilActions($this, $this->getLPOptionsDB());
        }

        return $this->lp_option_actions;
    }

    /**
     * Get db for lp options
     *
     * @return LPOptions\DB
     */
    public function getLPOptionsDB()
    {
        if ($this->lp_option_db === null) {
            global $DIC;
            $this->lp_option_db = new CaT\Plugins\CourseMember\LPOptions\ilDB($DIC->database());
        }

        return $this->lp_option_db;
    }

    /**
     * Get actions for signature list
     *
     * @return SignatureList\ilActions
     */
    public function getSiglistActions()
    {
        if ($this->siglist_actions === null) {
            $file_storage = new CaT\Plugins\CourseMember\SignatureList\ilFileStorage(0);
            $this->siglist_actions = new CaT\Plugins\CourseMember\SignatureList\ilActions($this, $file_storage);
        }
        return $this->siglist_actions;
    }

    /**
     * Get tree
     *
     * @return ilLog
     */
    protected function getTree()
    {
        global $DIC;
        return $DIC->repositoryTree();
    }

    public function getReminderSettings() : CourseMember\Reminder\NotFinalized
    {
        global $DIC;
        return $this->getPluginDIC($this, $DIC)["config.notfinalized.db"]->select();
    }

    public function getMemberListTemplates() : array
    {
        global $DIC;
        $templates = $this->getPluginDIC($this, $DIC)["SignatureList.ConfigurableListConfigRepo"]
            ->tableData();

        $ret = [];
        foreach ($templates as $tpl) {
            $tpl_id = $tpl[DBConfigurableListConfigRepo::FIELD_ID];
            $ret[$tpl_id] = $tpl[DBConfigurableListConfigRepo::FIELD_NAME];
        }

        return $ret;
    }

    public function getMemberListDefaultTemplateId()
    {
        global $DIC;
        return $this->getPluginDIC($this, $DIC)["SignatureList.ConfigurableListConfigRepo"]
            ->getDefaultTeplateId();
    }

    /**
     * @return int | null
     */
    public function getSelectedCourseTemplate(int $crs_id)
    {
        global $DIC;
        return $this->getPluginDIC($this, $DIC)["SignatureList.ConfigurableListConfigRepo"]
            ->getSelectedCourseTemplate($crs_id);
    }

    public function setSelectedCourseTemplate(int $crs_id, int $tpl_id)
    {
        global $DIC;
        return $this->getPluginDIC($this, $DIC)["SignatureList.ConfigurableListConfigRepo"]
            ->setSelectedCourseTemplate($crs_id, $tpl_id);
    }

    public function initAttendanceListFor(ilObject $crs, int $template_id = null) : TMSAttendanceList
    {
        global $DIC;
        $repo = $this->getPluginDIC($this, $DIC)["SignatureList.ConfigurableListConfigRepo"];

        if (is_null($template_id)) {
            throw new LogicException("No signature list template defined in course member plugin");
        }

        $logo_path = $this->getPluginDIC($this, $DIC)["SignatureList.ilActions"]->getPath();
        $tpl_data = $repo->load($template_id);
        $list = new TMSAttendanceList(
            $crs,
            $this->getDirectory(),
            function ($code) {
                return $this->txt($code);
            },
            $logo_path
        );
        $list->configurateByTemplate($tpl_data);

        return $list;
    }

    public function isDefaultTemplateDefined() : bool
    {
        return !is_null($this->getMemberListDefaultTemplateId());
    }

    public function getAvailablePlaceholders() : array
    {
        global $DIC;
        return $this->getPluginDIC($this, $DIC)["SignatureList.ConfigurableListConfigRepo"]
            ->getAvailablePlaceholders();
    }

    public function getTemplateIdByMailPlaceholder(string $mail_id) : int
    {
        global $DIC;
        return $this->getPluginDIC($this, $DIC)["SignatureList.ConfigurableListConfigRepo"]
            ->getTemplateByMailId($mail_id);
    }
}
