<?php
require_once("Services/Repository/classes/class.ilObjectPlugin.php");
require_once(__DIR__ . "/AgendaItem/class.ilAgendaItemsGUI.php");
require_once(__DIR__ . "/Settings/class.ilAIPSettingsGUI.php");

use CaT\Plugins\AgendaItemPool;
use CaT\Plugins\AgendaItemPool\Settings;
use CaT\Ente\ILIAS\ilProviderObjectHelper;

/**
 * Object of the plugin
 */
class ilObjAgendaItemPool extends ilObjectPlugin
{
    use ilProviderObjectHelper;

    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    /**
     * @var ilObjectActions
     */
    protected $object_actions;

    /**
     * @var AgendaItemPool
     */
    protected $settings;

    /**
     * Constructor of the class ilObjAgendaItemPool
     *
     * @return 	void
     */
    public function __construct()
    {
        parent::__construct();
        $this->object_actions = $this->getObjectActions();
        $this->g_tree = $this->getDIC()["tree"];
        $this->g_objDefinition = $this->getDIC()["objDefinition"];
    }
    /**
     * Init the type of the plugin. Same value as choosen in plugin.php
     */
    public function initType()
    {
        $this->setType("xaip");
    }

    /**
     * Create a Settings object.
     *
     * @return 	void
     */
    public function doCreate()
    {
        $id = (int) $this->getId();
        $this->object_actions->createSettings($id);
    }

    /**
     * Get called if the object get be updated
     * Update additoinal setting values
     */
    public function doUpdate()
    {
        $this->object_actions->updateSettings($this->settings);
    }

    /**
     * Get called after object creation to read further information
     */
    public function doRead()
    {
        $id = (int) $this->getId();
        $this->settings = $this->object_actions->getSettingsById($id);
    }

    public function areItemsUsed()
    {
        $used_agenda_items = $this->getUsedAiIds();
        $agenda_items = $this->getObjectActions()->getAllAgendaItemsByPoolId((int) $this->getId());
        $agenda_items = array_map(function ($ai) {
            return $ai->getObjId();
        }, $agenda_items);
        $diff = array_diff($agenda_items, $used_agenda_items);

        return count($diff) != count($agenda_items);
    }

    /**
     * Get called if the object should be deleted.
     * Delete additional settings
     */
    public function doDelete()
    {
        $id = (int) $this->getId();
        $this->object_actions->deleteSettingById($id);
        $this->object_actions->deleteAgendaItemsByPoolId($id);
    }

    /**
     * Get called if the object get be coppied.
     * Copy additional settings to new object
     */
    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $settings = $this->getSettings();
        $fnc = function ($s) use ($settings) {
            return $s->withIsOnline($settings->getIsOnline());
        };

        $new_obj->doRead();
        $new_obj->updateSetting($fnc);
        $new_obj->doUpdate();

        $items = $this->getObjectActions()->getAllAgendaItemsByPoolId((int) $this->getId());

        $n_actions = $new_obj->getObjectActions();
        $g_user = $this->getDIC()->user();
        $date = new DateTime();
        foreach ($items as $key => $item) {
            $n_actions->createAgendaItem(
                $item->getTitle(),
                $date,
                (int) $g_user->getId(),
                (int) $new_obj->getId(),
                $item->getDescription(),
                $item->getIsActive(),
                $item->getIddRelevant(),
                $item->getIsDeleted(),
                $item->getIsBlank(),
                $item->getTrainingTopics(),
                $item->getGoals(),
                $item->getGDVLearningContent(),
                $item->getIDDLearningContent(),
                $item->getAgendaItemContent()
            );
        }
    }

    /**
     * Update Settings.
     *
     * @param 	\Closure 	$c
     * @return 	void
     */
    public function updateSetting(\Closure $c)
    {
        $this->settings = $c($this->settings);
    }

    /**
     * Get settings.
     *
     * @return 	void
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Get an instance of ilObjectActions.
     *
     * @return 	ilActions
     */
    public function getObjectActions()
    {
        if ($this->object_actions === null) {
            global $DIC;
            $db = $DIC->database();

            $this->object_actions = new AgendaItemPool\ilObjectActions(
                $this,
                $this->getAgendaItemDB($db),
                $this->getSettingsDB($db)
                );
        }
        return $this->object_actions;
    }

    /**
     * Get agenda_item db.
     *
     * @param 	ilDatabase 	$db
     * @return 	AgendaItemPool\AgendaItem\ilDB
     */
    public function getAgendaItemDB($db)
    {
        return $this->plugin->getAgendaItemDB($db);
    }

    /**
     * Get settings db.
     *
     * @param 	ilDatabase 	$db
     * @return 	AgendaItemPool\Settings\ilDB
     */
    public function getSettingsDB($db)
    {
        if ($this->settings_db === null) {
            $this->settings_db = new AgendaItemPool\Settings\ilDB($db);
        }

        return $this->settings_db;
    }

    /**
     * Get the root plugin directory.
     *
     * @return 	string
     */
    public function getPluginDirectory()
    {
        return $this->plugin->getDirectory();
    }

    /**
     * Check if plugin CourseClassification is active.
     *
     * @return 	bool
     */
    public function isCourseClassificationActive()
    {
        require_once("Services/Component/classes/class.ilPluginAdmin.php");
        return ilPluginAdmin::isPluginActive("xccl");
    }

    /**
     * Get actions from the plugin CoursClassification.
     *
     * @return ilActions
     */
    public function getCourseClassificationActions()
    {
        require_once("Services/Component/classes/class.ilPluginAdmin.php");
        $obj = ilPluginAdmin::getPluginObjectById("xccl");
        return $obj->getActions();
    }

    /**
     * Get ids of all used ai
     *
     * @return int[]
     */
    protected function getUsedAiIds() : array
    {
        $ret = array();
        if (!\ilPluginAdmin::isPluginActive("xage")) {
            return $ret;
        }

        $agendas = $this->getAllChildrenOfByType((int) ROOT_FOLDER_ID, "xage");
        foreach ($agendas as $agenda) {
            $agenda_db = $agenda->getAgendaEntryDB();
            foreach ($agenda_db->selectFor((int) $agenda->getId()) as $entry) {
                $ret[] = $entry->getPoolItemId();
            }
        }

        return array_unique($ret);
    }

    /**
     * Get all children by type recursive
     *
     * @return Object 	of search type
     */
    protected function getAllChildrenOfByType(int $ref_id, string $search_type)
    {
        $childs = $this->g_tree->getSubTree(
            $this->g_tree->getNodeData($ref_id),
            false,
            $search_type
        );

        $ret = array();

        foreach ($childs as $child) {
            $ret[] = \ilObjectFactory::getInstanceByRefId($child);
        }

        return $ret;
    }
}
