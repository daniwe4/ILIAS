<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */
/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

use CaT\Plugins\Agenda;
use CaT\Ente\ILIAS\ilProviderObjectHelper;
use CaT\Plugins\AgendaItemPool\AgendaItem\AgendaItem;

class ilObjAgenda extends ilObjectPlugin
{
    use ilProviderObjectHelper;
    use Agenda\DI;

    /**
     * @var \Pimple\Container
     */
    protected $dic;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    /**
     * @var Agenda\AgendaEntry\ilDB
     */
    protected $agenda_entry_db;

    /**
     * @var Agenda\Settings\Settings
     */
    protected $settings;

    public function __construct(int $a_ref_id = 0)
    {
        $dic = $this->getDI();
        $this->tree = $dic["tree"];
        $this->objDefinition = $dic["objDefinition"];

        parent::__construct($a_ref_id);
    }

    /**
     * Needed for trait ilProviderObjectHelper
     *
     * @return mixed
     */
    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    protected function getDI()
    {
        if (is_null($this->dic)) {
            global $DIC;
            $this->dic = $this->getObjectDIC($this, $DIC);
        }
        return $this->dic;
    }

    public function initType()
    {
        $this->setType("xage");
    }

    public function doCreate()
    {
        $this->createUnboundProvider("crs", CaT\Plugins\Agenda\UnboundProvider::class, __DIR__ . "/UnboundProvider.php");
        $this->createUnboundProvider("crs", CaT\Plugins\Agenda\SharedUnboundProvider::class, __DIR__ . "/SharedUnboundProvider.php");
        $this->settings = $this->getSettingsDB()->create((int) $this->getId());
    }

    public function doRead()
    {
        $this->settings = $this->getSettingsDB()->selectFor((int) $this->getId());
    }

    public function doUpdate()
    {
        $this->getSettingsDB()->update($this->getSettings());
    }

    public function doDelete()
    {
        $this->deleteUnboundProviders();
        $this->getAgendaEntryDB()->deleteFor($this->getId());
    }

    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $settings = $this->getSettings();
        $fnc = function ($s) use ($settings) {
            return $s->withStartTime($settings->getStartTime());
        };

        $new_obj->doRead();
        $new_obj->updateSettings($fnc);
        $new_obj->doUpdate();

        $agenda_entry_db = $this->getAgendaEntryDB();
        $n_agenda_entry_db = $new_obj->getAgendaEntryDB();
        $n_obj_id = (int) $new_obj->getId();
        foreach ($agenda_entry_db->selectFor($this->getId()) as $value) {
            $n_agenda_entry_db->create(
                $n_obj_id,
                $value->getPoolItemId(),
                $value->getDuration(),
                $value->getPosition(),
                $value->getIsBlank(),
                $value->getAgendaItemContent(),
                $value->getGoals()
            );
        }
    }

    public function getSettingsDB() : Agenda\Settings\ilDB
    {
        return $this->getDI()["settings.db"];
    }

    public function getSettings() : Agenda\Settings\Settings
    {
        return $this->settings;
    }

    public function updateSettings(\Closure $c)
    {
        $this->settings = $c($this->settings);
    }

    public function getAgendaEntryDB() : Agenda\AgendaEntry\ilDB
    {
        return $this->getDI()["agenda_entry.db"];
    }

    public function getAgendaEntryTable() : ilTMSTableGUI
    {
        $gui = $this->getDI()["agenda_entry.gui"];
        $table = $gui->getTable();
        return $gui->configureTable($table);
    }

    protected function getCourseCreationDB() : Agenda\CourseCreation\ilDB
    {
        return $this->getDI()["course_creation.db"];
    }

    /**
     * @return ilObjCourse | bool
     */
    public function getParentCourse()
    {
        $ref_id = $this->getRefId();
        if (is_null($ref_id)) {
            $ref_ids = ilObject::_getAllReferences($this->getId());
            $ref_id = array_shift($ref_ids);
        }

        $crs_obj = null;
        foreach ($this->tree->getPathFull($ref_id) as $hop) {
            if ($hop['type'] === 'crs') {
                $crs_obj = ilObjectFactory::getInstanceByRefId($hop["ref_id"]);
                break;
            }
        }
        return $crs_obj;
    }

    /**
     * @return ilObjSession | null
     */
    public function getParentSession()
    {
        $parent_crs = $this->getParentCourse();

        if (is_null($parent_crs)) {
            return null;
        }

        $ret = null;
        foreach ($this->getCourseSessions((int) $parent_crs->getRefId()) as $key => $session) {
            foreach (ilEventItems::_getItemsOfEvent((int) $session->getId()) as $item_id) {
                if ((int) $item_id === (int) $this->getRefId()) {
                    $ret = $session;
                    break;
                }
            }

            if (!is_null($ret)) {
                break;
            }
        }

        return $ret;
    }

    /**
     * Get all sessions below parent course
     *
     * @param int 	$crs_ref_id
     * @return ilObjSession[]
     */
    public function getCourseSessions(int $crs_ref_id) : array
    {
        $childs = $this->tree->getChilds($crs_ref_id);
        $ret = array();

        foreach ($childs as $child) {
            $type = $child["type"];
            if ($type == "sess") {
                $ret[] = \ilObjectFactory::getInstanceByRefId($child["child"]);
            }

            if ($this->objDefinition->isContainer($type)) {
                $rec_ret = $this->getCourseSessions((int) $child["child"]);
                if (!is_null($rec_ret)) {
                    $ret = array_merge($ret, $rec_ret);
                }
            }
        }

        return $ret;
    }

    /**
     * Closure to get txt from plugin
     */
    public function txtClosure()
    {
        return function (string $code) {
            return $this->txt($code);
        };
    }

    /**
     * Get the plugin directory
     *
     * @return string
     */
    public function getDirectory() : string
    {
        return $this->plugin->getDirectory();
    }

    public function pluginTxt(string $code)
    {
        return parent::txt($code);
    }

    public function isEduTrackingActive() : bool
    {
        return ilPluginAdmin::isPluginActive("xetr");
    }

    // for course creation
    /**
     * Will be called after course creation with configuration options.
     *
     * @param	mixed	$config
     * @return	void
     */
    public function afterCourseCreation($config)
    {
        $agenda_db = $this->getAgendaEntryDB();

        foreach ($config as $key => $value) {
            switch ($key) {
                case "entries":
                    $agenda_db->deleteFor((int) $this->getId());
                    foreach ($value as $entry) {
                        $agenda_db->create(
                            (int) $this->getId(),
                            (int) $entry["pool_item"],
                            (int) $entry["duration"],
                            (int) $entry["position"],
                            (bool) $entry["is_blank"],
                            $entry["content"],
                            $entry["goals"]
                        );
                    }
                    break;
                case "start_time":
                    $h = str_pad(floor($value / 60), 2, "0", STR_PAD_LEFT);
                    $m = str_pad($value % 60, 2, "0", STR_PAD_LEFT);
                    $value = DateTime::createFromFormat("H:i", $h . ":" . $m, new DateTimeZone("Europe/Berlin"));
                    $fnc = function ($s) use ($value) {
                        return $s->withStartTime($value);
                    };
                    $this->updateSettings($fnc);
                    $this->update();
                    break;
            }
        }
    }

    /**
     * Get actions from the plugin CourseClassification.
     *
     * @return ilActions
     */
    public function getCourseClassificationActions()
    {
        if (ilPluginAdmin::isPluginActive('xccl') === true) {
            $obj = ilPluginAdmin::getPluginObjectById("xccl");
            return $obj->getActions();
        }
        return null;
    }

    /**
     * Get the start date of assigned session
     *
     * @return string | null
     */
    public function getSessionStart()
    {
        $course_creation_db = $this->getCourseCreationDB();
        $sessions = $course_creation_db->getAssignedSessions($this->getRefId());

        if (count($sessions) == 0) {
            return null;
        }

        usort($sessions, function (ilObjSession $a, ilObjSession $b) {
            $appointment = $a->getFirstAppointment();
            $start_a = $appointment->getStart()->get(IL_CAL_DATETIME);

            $appointment = $b->getFirstAppointment();
            $start_b = $appointment->getStart()->get(IL_CAL_DATETIME);

            if ($start_a > $start_b) {
                return 1;
            }

            if ($start_b > $start_a) {
                return -1;
            }

            return 0;
        });

        $first_session = array_shift($sessions);
        return $first_session->getFirstAppointment()->getStart()->get(IL_CAL_DATETIME);
    }

    public function editFixedBlocks() : bool
    {
        return $this->getPlugin()->editFixedBlocks();
    }

    public function getAipById(int $id) : CaT\Plugins\AgendaItemPool\AgendaItem\AgendaItem
    {
        return $this->getAipDb()->selectFor($id);
    }

    public function getAipDb() : CaT\Plugins\AgendaItemPool\AgendaItem\DB
    {
        if (is_null($this->aip_db)) {
            $this->aip_db = $this->getDI()["aip.agenda.item.db"];
        }

        return $this->aip_db;
    }

    /**
     * Updates the session where this agenda is assigned to
     */
    public function updateSession()
    {
        $assigned_session = $this->getParentSession();
        if (is_null($assigned_session)) {
            return;
        }

        $assigned_session->updateFromAgenda();
    }
}
