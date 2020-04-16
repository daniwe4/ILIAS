<?php
include_once("Services/Repository/classes/class.ilObjectPlugin.php");
include_once("Services/Tracking/interfaces/interface.ilLPStatusPlugin.php");
include_once("Services/Tracking/classes/class.ilLPStatus.php");

use CaT\Plugins\CourseMember;
use CaT\Ente\ILIAS\ilProviderObjectHelper;

/**
 * Object of the plugin
 */
class ilObjCourseMember extends ilObjectPlugin implements ilLPStatusPluginInterface
{
    use ilProviderObjectHelper;

    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    /**
     * @var CourseMember\ilObjActions
     */
    protected $obj_actions;

    /**
     * Init the type of the plugin. Same value as choosen in plugin.php
     */
    public function initType()
    {
        $this->setType("xcmb");
    }

    /**
     * @inheritdoc
     */
    public function doCreate()
    {
        $this->settings = $this->getSettingsDB()->create((int) $this->getId(), null);
        $this->createUnboundProvider("crs", CaT\Plugins\CourseMember\UnboundProvider::class, __DIR__ . "/UnboundProvider.php");
        $this->createUnboundProvider("crs", CaT\Plugins\CourseMember\SharedUnboundProvider::class, __DIR__ . "/SharedUnboundProvider.php");
    }

    /**
     * Get called if the object get be updated
     * Update additoinal setting values
     */
    public function doUpdate()
    {
        $this->getSettingsDB()->update($this->getSettings());
    }

    /**
     * Get called after object creation to read further information
     */
    public function doRead()
    {
        $this->settings = $this->getSettingsDB()->selectFor((int) $this->getId());
    }

    /**
     * Get called if the object should be deleted.
     * Delete additional settings
     */
    public function doDelete()
    {
        $this->getSettingsDB()->deleteFor((int) $this->getId());
        $this->deleteUnboundProviders();
    }

    /**
     * Get called if the object get be coppied.
     * Copy additional settings to new object
     */
    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $fnc = function ($s) {
            return $s->withCredits($this->getSettings()->getCredits())
                ->withLPMode($this->getSettings()->getLPMode())
                ->withListRequired($this->getSettings()->getListRequired())
                ->withListOptionOrgu($this->getSettings()->getListOptionOrgu())
                ->withListOptionText($this->getSettings()->getListOptionText())
            ;
        };

        $new_obj->updateSettings($fnc);
        $new_obj->update();
    }

    /**
     * Get actions of the repo object
     *
     *
     * @return CourseMember\ilObjActions
     */
    public function getActions()
    {
        if ($this->obj_actions === null) {
            global $DIC;
            $this->obj_actions = new CourseMember\ilObjActions($this, $this->getMembersDB(), $this->getLPManager(), $DIC["ilAppEventHandler"]);
        }

        return $this->obj_actions;
    }

    /**
     * Get instance of settings db
     *
     * @return CourseMember\Settings\DB
     */
    protected function getSettingsDB()
    {
        if ($this->settings_db === null) {
            global $DIC;
            $this->settings_db = new CourseMember\Settings\ilDB($DIC->database());
        }

        return $this->settings_db;
    }

    /**
     * Get instance of members db
     *
     * @return CourseMember\Members\DB
     */
    protected function getMembersDB()
    {
        if ($this->members_db === null) {
            global $DIC;
            $this->members_db = new CourseMember\Members\ilDB($DIC->database(), $DIC->user());
        }

        return $this->members_db;
    }

    /**
     * Get the parent course
     *
     * @return ilObjCourse | null
     */
    public function getParentCourse()
    {
        $ref_id = $this->getRefId();
        if (is_null($ref_id)) {
            $ref_ids = ilObject::_getAllReferences($this->getId());
            $ref_id = array_shift($ref_ids);
        }

        global $DIC;
        $tree = $DIC->repositoryTree();
        $crs_obj = null;
        foreach ($tree->getPathFull($ref_id) as $hop) {
            if ($hop['type'] === 'crs') {
                require_once("Services/Object/classes/class.ilObjectFactory.php");
                $crs_obj = ilObjectFactory::getInstanceByRefId($hop["ref_id"]);
                break;
            }
        }
        return $crs_obj;
    }

    /**
     * get object id of parent course
     *
     * @return int
     */
    protected function getParentObjId()
    {
        if ($this->parent_obj_id === null) {
            $parent = $this->getParentCourse();
            $this->parent_obj_id = $parent->getId();
        }

        return $this->parent_obj_id;
    }

    /**
     * Get settings of this object
     *
     * @return CourseMember\Settings\CourseMemberSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    public function setSettings(CourseMember\Settings\CourseMemberSettings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Update settings of this object
     *
     * @param \Closure $update_function
     *
     * @return void
     */
    public function updateSettings(\Closure $update_function)
    {
        $this->settings = $update_function($this->settings);
    }

    /**
     * Tranlsate lang code
     *
     * @param string 	$code
     *
     * @return string
     */
    public function pluginTxt($code)
    {
        return $this->txt($code);
    }

    /**
     * Get directory of the plugin
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->getPlugin()->getDirectory();
    }

    /**
     * @inheritdoc
     */
    public function getLPCompleted()
    {
        if (!$this->getSettings()->getClosed()) {
            return array();
        }

        return $this->getMembersDB()->getUserIdsWithLPStatus((int) $this->getParentObjId(), \ilLPStatus::LP_STATUS_COMPLETED_NUM);
    }

    /**
     * @inheritdoc
     */
    public function getLPNotAttempted()
    {
        if (!$this->getSettings()->getClosed()) {
            return array();
        }

        return $this->getMembersDB()->getUserIdsWithLPStatus((int) $this->getParentObjId(), \ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM);
    }

    /**
     * @inheritdoc
     */
    public function getLPFailed()
    {
        if (!$this->getSettings()->getClosed()) {
            return array();
        }

        return $this->getMembersDB()->getUserIdsWithLPStatus((int) $this->getParentObjId(), \ilLPStatus::LP_STATUS_FAILED_NUM);
    }

    /**
     * @inheritdoc
     */
    public function getLPInProgress()
    {
        return array();
    }

    /**
     * @inheritdoc
     */
    public function getLPStatusForUser($a_user_id)
    {
        if (!$this->getSettings()->getClosed()) {
            return \ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
        }

        $status = $this->getMembersDB()->getLPDataFor((int) $this->getParentObjId(), (int) $a_user_id);
        if (is_null($status)) {
            return \ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
        }
        return $status;
    }

    /**
     * Get instance of lp manager
     *
     * @return CourseMember\LPSettings\LPManager
     */
    protected function getLPManager()
    {
        if ($this->lp_manager === null) {
            $this->lp_manager = new CourseMember\LPSettings\LPManagerImpl();
        }

        return $this->lp_manager;
    }


    /**
     * Get the file storage for participation list
     *
     * @return ilFileStorage
     */
    public function getFileStorage()
    {
        if ($this->file_storage === null) {
            $this->file_storage = new CourseMember\FileStorage\ilFileStorage($this->getId());
        }

        return $this->file_storage;
    }

    public function getLinkToMemberView()
    {
        require_once("Services/Link/classes/class.ilLink.php");
        global $DIC;
        $access = $DIC->access();

        $link = null;
        if ($access->checkAccess("read", "", $this->getRefId())
            && $access->checkAccess("view_lp", "", $this->getRefId())
        ) {
            $link = ilLink::_getLink($this->getRefId(), $this->getType(), array("cmd" => "showMembers"));
        }

        return $link;
    }

    /**
     * Get the satic link for download the signature list
     *
     * @return string | null
     */
    public function getLinkForSignatureDownload()
    {
        require_once("Services/Link/classes/class.ilLink.php");
        global $DIC;
        $access = $DIC->access();

        $link = null;
        if ($access->checkAccess("read", "", $this->getRefId())
            && $access->checkAccess("view_lp", "", $this->getRefId())
        ) {
            $link = ilLink::_getLink($this->getRefId(), $this->getType(), array("cmd" => "exportSignatureList"));
        }

        return $link;
    }

    /**
     * Get the satic link for download the member list
     *
     * @return string | null
     */
    public function getLinkForMemberlistDownload()
    {
        require_once("Services/Link/classes/class.ilLink.php");
        global $DIC;
        $access = $DIC->access();

        $link = null;
        if ($access->checkAccess("read", "", $this->getRefId())
            && $access->checkAccess("view_lp", "", $this->getRefId())
        ) {
            $link = ilLink::_getLink($this->getRefId(), $this->getType(), array("cmd" => "downloadFile"));
        }

        return $link;
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
     * Get the signature-list exporter.
     *
     * @return CourseMember\SignatureList\PDFExport
     */
    public function getSinatureListExporter()
    {
        return new CourseMember\SignatureList\PDFExport(
            $this,
            $this->getSignatureListActions()
        );
    }

    public function getSignatureListActions()
    {
        return $this->getPlugin()->getSiglistActions();
    }

    public function getLPOptionActions()
    {
        return $this->getPlugin()->getLPOptionActions();
    }

    /**
     * Get the idd leraning from tab if it's configured
     *
     * @return int | null
     */
    public function getConfiguredIDDLearningTime()
    {
        $parent = $this->getParentCourse();
        if (is_null($parent)) {
            return null;
        }

        if (ilPluginAdmin::isPluginActive('xetr') === false) {
            return null;
        }

        $xetr = $this->getFirstChildOfByType($parent->getRefId(), "xetr");
        if (is_null($xetr)) {
            return null;
        }

        $actions = $xetr->getActionsFor("IDD");
        $settings = $actions->select();
        return $settings->getMinutes();
    }

    /**
     * Get first child by type recursive
     *
     * @param int 	$ref_id
     * @param string 	$search_type
     *
     * @return Object 	of search type
     */
    protected function getFirstChildOfByType($ref_id, $search_type)
    {
        global $DIC;
        /**
         * @var ilTree $tree
         */
        $tree = $DIC["tree"];
        $children = $tree->getSubtree($tree->getNodeData($ref_id), false, $search_type);

        if (count($children) == 0) {
            return null;
        }

        $xetr = array_shift($children);
        return \ilObjectFactory::getInstanceByRefId($xetr);
    }

    public function getPluginObject()
    {
        return $this->getPlugin();
    }
}
