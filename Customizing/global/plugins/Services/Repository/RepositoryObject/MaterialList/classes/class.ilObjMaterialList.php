<?php
include_once("Services/Repository/classes/class.ilObjectPlugin.php");

use \CaT\Plugins\MaterialList\ObjMaterialList;
use \CaT\Plugins\MaterialList\ilObjectActions;
use \CaT\Plugins\MaterialList\Settings\MaterialList;
use \CaT\Plugins\MaterialList\Mailing\MaterialListOccasion;
use CaT\Ente\ILIAS\ilProviderObjectHelper;

/**
 * Object of the plugin
 */
class ilObjMaterialList extends \ilObjectPlugin implements ObjMaterialList
{
    use ilProviderObjectHelper;

    public function __construct($ref_id = 0)
    {
        $this->initType();
        $this->plugin = $this->getPlugin();
        parent::__construct($ref_id);
    }

    /**
     * Init the type of the plugin. Same value as choosen in plugin.php
     */
    public function initType()
    {
        $this->setType("xmat");
    }

    /**
     * Get called if the object get be updated
     * Update additoinal setting values
     */
    public function doUpdate()
    {
        $this->getActions()->updateExtendedSettings($this->settings);
        $this->scheduleMailingEvents(); //make sure that settings are written before.
    }

    /**
     * Get called after object creation to read further information
     */
    public function doRead()
    {
        $this->settings = $this->getActions()->getExtendedSettingsForCurrentObject();
    }

    /**
     * @inheritdoc
     */
    public function doCreate()
    {
        $this->settings = $this->getActions()->createExtendedSettingsForCurrentObject();
        $this->createUnboundProvider("crs", CaT\Plugins\MaterialList\SharedUnboundProvider::class, __DIR__ . "/SharedUnboundProvider.php");
    }

    /**
     * Get called if the object should be deleted.
     * Delete additional settings
     */
    public function doDelete()
    {
        $this->getActions()->deleteExtendedSettingsForCurrentObject();
        $this->getActions()->deleteListEntriesForCurrentObj();
        $this->deleteExistingMailingEvents();
        $this->deleteUnboundProviders();
    }

    /**
     * Get called if the object get be coppied.
     * Copy additional settings to new object
     */
    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $new_actions = $new_obj->getActions();
        $new_obj_id = (int) $new_obj->getId();

        foreach ($this->getActions()->getListEntiesForCurrentObj() as $list_entry) {
            $new_actions->createListEntry(
                $new_obj_id,
                $list_entry->getNumberPerParticipant(),
                $list_entry->getNumberPerCourse(),
                $list_entry->getArticleNumber(),
                $list_entry->getTitle()
            );
        }

        $s = $this->getSettings();
        $nu_actions = $new_obj->getActions();
        $nu_settings = $nu_actions->getExtendedSettingsForCurrentObject()
            ->withRecipientMode($s->getRecipientMode())
            ->withRecipient($s->getRecipient())
            ->withSendDaysBefore($s->getSendDaysBefore())
            ;
        $nu_actions->updateExtendedSettings($nu_settings);
    }

    /**
     * @inheritdoc
     */
    public function updateSettings(\Closure $update)
    {
        $this->settings = $update($this->getSettings());
    }

    /**
     * Get actions of this object
     *
     * @return ilObjectActions
     */
    public function getActions()
    {
        if ($this->actions === null) {
            global $DIC;
            $db = $DIC->database();
            $user = $DIC->user();
            $app_event_handler = $DIC["ilAppEventHandler"];
            $tree = $DIC->repositoryTree();
            $access = $DIC->access();
            $this->actions = new ilObjectActions(
                $this,
                $this->plugin->getSettingsDB($db),
                $this->plugin->getListsDB($db),
                $user,
                $app_event_handler,
                $tree,
                $access
            );
        }

        return $this->actions;
    }

    /**
     * @inheritdoc
     */
    public function getSettings()
    {
        return $this->settings;
    }

    public function setSettings(MaterialList $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Get object of parent course
     *
     * @return \ilObjCourse | null
     */
    public function getParentCourse()
    {
        global $DIC;
        $tree = $DIC->repositoryTree();
        $parents = $tree->getPathFull($this->getRefId());
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

    /**
     * Calculate point of time for sending material list.
     *
     * @return \DateTime | null
     */
    public function getDueDate()
    {
        $parent_course = $this->getParentCourse();
        if (is_null($parent_course)) {
            return null;
        }

        $course_start = $parent_course->getCourseStart();
        if ($course_start === null) {
            return null;
        }

        $settings = $this->getSettings();
        $mode = $settings->getRecipientMode();
        if ($mode === ilObjectActions::M_SELECTION) { //configured at object
            $offset_days = $settings->getSendDaysBefore();
        }
        if ($mode === ilObjectActions::M_COURSE_VENUE) { //venue from course
            $course_venue = $this->getAssignedVenueFromParentCourse();
            if (is_null($course_venue)) {
                return null;
            }
            $offset_days = (int) $course_venue->getService()->getDaysSendMaterial();
        }

        $offset = new \DateInterval('P' . $offset_days . 'D');
        $course_start = $course_start->get(IL_CAL_DATE);
        $course_start = new DateTime($course_start);
        $due = clone $course_start;
        $due->sub($offset);
        $now = new \DateTime();

        if ($now > $due) {
            return null;
        }
        return $due;
    }

    /**
     * Create deferred events to send the list.
     *
     * @return void
     */
    public function scheduleMailingEvents()
    {
        if (!is_null($this->getRefId())) {
            $parent_course = $this->getParentCourse();
            $this->deleteExistingMailingEvents();
            $due = $this->getDueDate();
            if (!is_null($due)) {
                $schedule = $this->getSchedule();
                $schedule->create(
                    (int) $this->getRefId(),
                    $due,
                    'Modules/Course',
                    MaterialListOccasion::EVENT_SEND_LIST,
                    [
                        'crs_ref_id' => $parent_course->getRefId(),
                        'xmat_ref_id' => $this->getRefId()
                    ]
                );
            }
        }
    }

    /**
     * Get the assigned venue from parent course.
     * The course has more than one mode, though, and the
     * venue might be a free text. In this case, the information is not
     * processable and this function will uniformly return null.
     *
     * @return Venue | null
     */
    public function getAssignedVenueFromParentCourse()
    {
        $parent_course = $this->getParentCourse();
        $vplug = ilPluginAdmin::getPluginObjectById('venues');
        $vactions = $vplug->getActions();
        $vassignment = $vactions->getAssignment((int) $parent_course->getId());

        if (!$vassignment || $vassignment->isCustomAssignment()) {
            return null;
        }

        if ($vassignment->isListAssignment()) {
            $venue_id = $vassignment->getVenueId();
            $venue = $vactions->getVenue($venue_id);
            return $venue;
        }
    }

    /**
     * Get the schedule for deferred events
     *
     * @return ILIAS\TMS\ScheduledEvents\Schedule
     */
    protected function getSchedule()
    {
        if ($this->schedule === null) {
            require_once('./Services/TMS/ScheduledEvents/classes/Schedule.php');
            global $DIC;
            $this->schedule = new Schedule($DIC->database());
        }
        return $this->schedule;
    }

    /**
     * Get ScheduledEvents for mails issued by this object.
     *
     * @return ILIAS\TMS\ScheduledEvents\Event[]
     */
    protected function getExistingMailingEvents()
    {
        $schedule = $this->getSchedule();
        $existing_events = $schedule->getAllFromIssuer(
            (int) $this->getRefId(),
            'Modules/Course',
            MaterialListOccasion::EVENT_SEND_LIST
        );
        return $existing_events;
    }

    /**
     * Delete ScheduledEvents for invitation-mails issued by this object.
     *
     * @return void
     */
    protected function deleteExistingMailingEvents()
    {
        $existing_events = $this->getExistingMailingEvents();
        if (count($existing_events) > 0) {
            $this->getSchedule()->delete($existing_events);
        }
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
     * @inheritdoc
     */
    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    /**
     * Get all children by type recursive
     *
     * @param int 	$ref_id
     * @param string 	$search_type
     *
     * @return Object 	of search type
     */
    public function getAllChildrenOfByType($ref_id, $search_type)
    {
        assert('is_int($ref_id)');
        assert('is_string($search_type)');
        $DIC = $this->getDIC();
        $g_tree = $DIC->repositoryTree();
        $g_objDefinition = $DIC["objDefinition"];

        $childs = $g_tree->getChilds($ref_id);
        $ret = array();

        foreach ($childs as $child) {
            $type = $child["type"];
            if ($type == $search_type) {
                $ret[] = \ilObjectFactory::getInstanceByRefId($child["child"]);
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
