<?php
include_once("Services/Repository/classes/class.ilObjectPlugin.php");

use CaT\Plugins\Accomodation;
use CaT\Plugins\Accomodation\ObjSettings;
use CaT\Plugins\Accomodation\Mailing\AccomodationListOccasion;
use CaT\Plugins\Accomodation\Reservation\Export;
use CaT\Ente\ILIAS\ilProviderObjectHelper;
use ILIAS\TMS\Booking;

/**
 * Object of the plugin
 *
 * @author Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class ilObjAccomodation extends ilObjectPlugin implements Accomodation\ObjAccomodation
{
    use ilProviderObjectHelper;

    /**
     * @var CaT\Plugins\Accomodation\ilActions
     */
    protected $actions = null;

    /**
     * @var ObjSettings\DB
     */
    protected $settings_db;

    /**
     * @var Accomodation\Reservation\DB
     */
    protected $reservations_db;

    /**
     * @var Accomodation\Reservation\Note\DB
     */
    protected $note_db;

    /**
     * @var Venue\DB
     */
    protected $venue_db;

    /**
     * @var ObjSettings\ObjSettings
     */
    protected $obj_settings;

    /**
     * @var ObjSettings\Overnights
     */
    protected $overnights;

    /**
     * Constructor of the class ilObjAccomodation
     */
    public function __construct(int $ref_id = 0)
    {
        global $DIC;
        $this->DIC = $DIC;
        $this->g_lang = $this->DIC->language();
        $this->g_lang->loadLanguageModule("tms");

        parent::__construct($ref_id);
    }

    /**
     * Init the type of the plugin. Same value as choosen in plugin.php
     */
    public function initType()
    {
        $this->setType("xoac");
    }

    /**
     * @inheritdoc
     */
    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    /**
     * @return Booking\Actions
     */
    public function getBookingActions()
    {
        //Is this deprecated?!
        require_once("Services/TMS/Booking/classes/class.ilTMSBookingActions.php");
        return new ilTMSBookingActions();
    }

    /**
     * @inheritdoc
     */
    public function getActions()
    {
        if ($this->actions === null) {
            global $DIC;

            $settings_db = $this->getObjSettingsDB();
            $reservations_db = $this->getReservationDB();
            $note_db = $this->getNodeDB();
            $venue_db = $this->getVenueDB();
            $overnights = $this->getObjOvernights();

            $this->actions = new Accomodation\ilActions(
                $this,
                $settings_db,
                $reservations_db,
                $note_db,
                $venue_db,
                $DIC["ilAppEventHandler"],
                $overnights
            );
        }
        return $this->actions;
    }

    public function reInitActions()
    {
        $this->overnights = null; //re-init overnights
        $this->actions = null;
        $this->getActions();
    }


    /**
     * @inheritdoc
     */
    public function getObjSettingsDB()
    {
        if ($this->settings_db === null) {
            global $ilDB;
            $this->settings_db = new Accomodation\ObjSettings\ilDB($ilDB);
        }
        return $this->settings_db;
    }

    /**
     * @inheritdoc
     */
    public function getReservationDB()
    {
        if ($this->reservations_db === null) {
            global $ilDB;
            $this->reservations_db = new Accomodation\Reservation\ilDB($ilDB);
        }
        return $this->reservations_db;
    }

    public function getNodeDB()
    {
        if ($this->note_db === null) {
            global $ilDB;
            $this->note_db = new Accomodation\Reservation\Note\ilDB($ilDB);
        }
        return $this->note_db;
    }

    /**
     * @inheritdoc
     */
    public function getVenueDB()
    {
        if ($this->venue_db === null) {
            $this->venue_db = new Accomodation\Venue\ilDB((int) $this->getRefId());
        }
        return $this->venue_db;
    }

    /**
     * Overnights provide single days for a time-range
     * @return ObjSettings\Overnights
     */
    public function getObjOvernights()
    {
        if (!$this->overnights) {
            list($crs_start, $crs_end) = $this->getParentCourseDates();
            $this->overnights = new ObjSettings\Overnights(
                $this->getObjSettings(),
                $crs_start,
                $crs_end
            );
        }
        return $this->overnights;
    }

    /**
     * @inheritdoc
     */
    public function getObjSettings()
    {
        if (!$this->obj_settings) {
            $this->obj_settings = $this->getObjSettingsDB()->selectFor((int) $this->getId());
        }
        return $this->obj_settings;
    }

    /**
     * Set updated settings for object after clone in mechanism
     *
     * @param ObjSettings\ObjSettings 	$obj_settings
     *
     * @return void
     */
    protected function setObjSettings($obj_settings)
    {
        $this->obj_settings = $obj_settings;
        $this->reInitActions();
    }

    /**
     * @inheritdoc
     */
    public function updateObjSettings(\Closure $update_function)
    {
        $settings = $update_function($this->getObjSettings());
        $this->setObjSettings($settings);
        $this->scheduleMailingEvents();
    }

    /**
     * @inheritdoc
     */
    public function getVenue()
    {
        $settings = $this->getObjSettings();

        if ($settings->getLocationFromCourse()) {
            $course_info = $this->getParentCourseInfo();
            $venue = $this->getVenueDB()->getVenueFromCourse((int) $course_info['obj_id']);
        } else { //get Venue from plugin-list
            $venue_id = $settings->getLocationObjId();
            $venue = $this->getVenueDB()->getVenueFromPlugin($venue_id);
        }

        return $venue;
    }

    public function getTxtClosure()
    {
        return $this->getPlugin()->txtClosure();
    }

    public function doCreate()
    {
        $this->createUnboundProvider("crs", CaT\Plugins\Accomodation\UnboundProvider::class, __DIR__ . "/UnboundProvider.php");
        $this->createUnboundProvider("crs", CaT\Plugins\Accomodation\SharedUnboundProvider::class, __DIR__ . "/SharedUnboundProvider.php");
        $db = $this->getObjSettingsDB();
        $db->create((int) $this->getId());
    }

    /**
     * Gets called if the object is to be be updated
     * Update additional settings as well
     */
    public function doUpdate()
    {
        $this->getObjSettingsDB()->update($this->obj_settings);
        $this->getActions()->raiseUpdateEvent();
    }

    /**
     * Gets called after object creation to read further information
     */
    public function doRead()
    {
        $this->getObjSettings();
    }

    /**
     * Gets called when the object should be deleted.
     * Delete additional settings
     */
    public function doDelete()
    {
        $id = (int) $this->getId();
        $this->getObjSettingsDB()->deleteFor($id);
        $this->getReservationDB()->deleteAllForObj($id);
        $this->deleteExistingMailingEvents();
        $this->deleteUnboundProviders();
        $this->getActions()->raiseDeleteEvent();
    }

    /**
     * Get called if the object get be coppied.
     * Copy additional settings to new object
     */
    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $db = $this->getObjSettingsDB();
        $s = $db->selectFor((int) $this->getId());
        $nu_settings = $new_obj->getObjSettings()
            ->withLocationObjId($s->getLocationObjId())
            ->withDatesByCourse($s->getDatesByCourse())
            ->withStartDate($s->getStartDate())
            ->withEndDate($s->getEndDate())
            ->withLocationFromCourse($s->getLocationFromCourse())
            ->withPriorDayAllowed($s->isPriorDayAllowed())
            ->withFollowingDayAllowed($s->isFollowingDayAllowed())
            ->withBookingEnd($s->getBookingEnd())
            ->withMailsettingsFromVenue($s->getMailsettingsFromVenue())
            ->withMailRecipient($s->getMailRecipient())
            ->withSendDaysBefore($s->getSendDaysBefore())
            ->withSendReminderDaysBefore($s->getSendReminderDaysBefore())
            ->withEditNotes($s->getEditNotes())
        ;
        $new_obj->setObjSettings($nu_settings);
        $new_obj->update();
    }

    /**
     * Get the directory of this plugin.
     *
     * @return string
     */
    public function getPluginDirectory()
    {
        return $this->getPlugin()->getDirectory();
    }

    /**
     * Get the ref-id of the course this object resides in.
     *
     * @return int | null
     */
    public function getParentCourseRefId()
    {
        $info = $this->getParentCourseInfo();
        if ($info) {
            return (int) $info['ref_id'];
        }
        return null;
    }

    /**
     * Get some information about the parent course.
     *
     * @return array<string,mixed> | false
     */
    public function getParentCourseInfo()
    {
        global $tree;
        foreach ($tree->getPathFull($this->getRefId()) as $hop) {
            if ($hop['type'] === 'crs') {
                return $hop;
            }
        }
        return false;
    }

    /**
     * Get ids of members in (parent) course
     *
     * @return int[]
     */
    public function getCourseParticipantsIds()
    {
        require_once("Modules/Course/classes/class.ilCourseParticipants.php");
        $ci = $this->getParentCourseInfo();
        $crs_obj_id = $ci['obj_id'];
        $part = \ilParticipants::getInstanceByObjId($crs_obj_id);
        $parts = $part->getParticipants();
        array_walk($parts, function (&$i) {
            $i = (int) $i;
        });
        return $part->getParticipants();
    }

    /**
     * Get all xoac-object within the course
     *
     * @return ilObjAccomodation[]
     */
    public function getAllAccomodationObjectsInCourse()
    {
        return $this->getPlugin()->getAllChildrenOfByType(
            $this->getParentCourseRefId(),
            'xoac'
        );
    }

    /**
     * Get parent course object.
     *
     * @return \ilObjCourse | null
     */
    public function getParentCourse()
    {
        $crs_ref = $this->getParentCourseRefId();
        if (!is_null($crs_ref)) {
            require_once("Services/Object/classes/class.ilObjectFactory.php");
            return \ilObjectFactory::getInstanceByRefId($crs_ref);
        }
        return null;
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
            (int) $this->getRefId()
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
     * Get the recipient for accomodation list,
     * taking sources (local, venue) into account.
     *
     * @return string | null
     */
    public function getEffectiveMailRecipient()
    {
        $settings = $this->getObjSettings();
        $use_venuesettings = $settings->getMailsettingsFromVenue();
        if ($use_venuesettings) {
            //get recipient from venue
            if ($settings->getLocationFromCourse()) {
                //from course
                $ci = $this->getParentCourseInfo();
                $crs_obj_id = (int) $ci['obj_id'];
                $venue = $this->getVenueDB()->getRealVenueByCourseId($crs_obj_id);
                if (is_null($venue)) {
                    return null;
                }
            } else {
                if ((int) $settings->getLocationObjId() == 0) {
                    return null;
                }
                $venue = $this->getVenueDB()->getRealVenueByVenueId($settings->getLocationObjId());
            }
            return $venue->getService()->getMailAccomodationList();
        } else {
            //use local settings
            return $settings->getMailRecipient();
        }
    }


    /**
     * Get the number of days prior to the training's start when the mail
     * should be triggered, taking sources (local, venue) into account.
     *
     * Returns [offset_days, offset_reminder].
     *
     * @return int[] | mixed[]
     */
    public function getEffectiveMailOffsetDays()
    {
        $settings = $this->getObjSettings();
        $use_venuesettings = $settings->getMailsettingsFromVenue();
        if ($use_venuesettings) {
            //get offset from venue
            if ($settings->getLocationFromCourse()) {
                //from course
                $ci = $this->getParentCourseInfo();
                $crs_obj_id = (int) $ci['obj_id'];
                $venue = $this->getVenueDB()->getRealVenueByCourseId($crs_obj_id);
                if (is_null($venue)) {
                    return null;
                }
            } else {
                if ((int) $settings->getLocationObjId() == 0) {
                    return null;
                }
                $venue = $this->getVenueDB()->getRealVenueByVenueId($settings->getLocationObjId());
            }
            return array(
                $venue->getService()->getDaysSendAccomodation(),
                $venue->getService()->getDaysRemindAccomodation()
            );
        } else {
            //use local settings
            return array(
                $settings->getSendDaysBefore(),
                $settings->getSendReminderDaysBefore()
            );
        }
    }

    /**
     * Calculate point of time for sending of accomodation lists.
     *
     * @return array <\DateTime | null>
     */
    public function getDueDates()
    {
        $parent_course = $this->getParentCourse();
        $course_start = $parent_course->getCourseStart();
        list($offset_days, $offset_reminder) = $this->getEffectiveMailOffsetDays();
        if (is_null($course_start) || is_null($offset_days)) {
            return [null, null];
        }

        $course_start = $course_start->get(IL_CAL_DATE);
        $course_start = new \DateTime($course_start);

        $offset = new \DateInterval('P' . $offset_days . 'D');
        $due = clone $course_start;
        $due->sub($offset);

        $due_remind = null;
        if (!is_null($offset_reminder)) {
            $offset_reminder = new \DateInterval('P' . $offset_reminder . 'D');
            $due_remind = clone $course_start;
            $due_remind->sub($offset_reminder);
        }

        $now = new \DateTime();
        $due_dat = null;
        if ($now < $due) {
            $due_dat = $due;
        }
        $due_remind_dat = null;
        if ($now < $due_remind) {
            $due_remind_dat = $due_remind;
        }
        return [$due_dat, $due_remind_dat];
    }


    /**
     * Create deferred events for sending the list.
     *
     * @return void
     */
    public function scheduleMailingEvents()
    {
        $this->deleteExistingMailingEvents();
        $schedule = $this->getSchedule();
        $parent_course = $this->getParentCourse();

        list($due, $remind) = $this->getDueDates();

        if (!is_null($due)) {
            $schedule->create(
                (int) $this->getRefId(),
                $due,
                'Modules/Course',
                AccomodationListOccasion::EVENT_SEND_LIST,
                array(
                    'crs_ref_id' => $parent_course->getRefId(),
                    'xoac_ref_id' => $this->getRefId()
                )
            );
        }

        if (!is_null($remind)) {
            $schedule->create(
                (int) $this->getRefId(),
                $remind,
                'Modules/Course',
                AccomodationListOccasion::EVENT_REMIND_LIST,
                array(
                    'crs_ref_id' => $parent_course->getRefId(),
                    'xoac_ref_id' => $this->getRefId()
                )
            );
        }
    }

    /**
     * Get start/end-date from course
     *
     * @return DateTime|null []
     */
    public function getParentCourseDates()
    {
        $crs = $this->getParentCourse();
        if (is_null($crs)) {
            return [null, null];
        }
        $crs_start = $crs->getCourseStart();
        if (!is_null($crs_start)) {
            $crs_start = new \DateTime($crs_start->get(IL_CAL_DATE));
        }
        $crs_end = $crs->getCourseEnd();
        if (!is_null($crs_end)) {
            $crs_end = new \DateTime($crs_end->get(IL_CAL_DATE));
        }

        return [$crs_start, $crs_end];
    }


    /**
     * Get information about the parent course to put into xls-header.
     * Return null, if there is no parent course.
     *
     * @return array<string, string> | null
     */
    public function getParentCourseInfoForExportHeader()
    {
        $crs = $this->getParentCourse();
        if (is_null($crs)) {
            return null;
        }
        $buffer = array(
            $crs->getTitle()
        );
        $crs_start = $crs->getCourseStart();
        if (!is_null($crs_start)) {
            $start = $crs_start->get(IL_CAL_DATE);
            $end = $crs->getCourseEnd()->get(IL_CAL_DATE);
            $buffer[] = $start . ' - ' . $end;
        }
        return $buffer;
    }

    /**
     * Get tutors from parent course to put into xls-header.
     * Return null, if there is no parent course.
     *
     * @return array<string, string> | null
     */
    public function getTutorsForExportHeader()
    {
        $crs = $this->getParentCourse();
        if (is_null($crs)) {
            return $crs;
        }
        $buffer = array();

        foreach ($crs->getMembersObject()->getTutors() as $key => $trainer_id) {
            $buffer[] = sprintf(
                "%s (%s)",
                \ilObjUser::_lookupFullname($trainer_id),
                \ilObjUser::_lookupEmail($trainer_id)
            );
        }
        return $buffer;
    }

    /**
     * Get admins from parent course to put into xls-header.
     * Return null, if there is no parent course.
     *
     * @return array<string, string> | null
     */
    public function getAdminsForExportHeader()
    {
        $crs = $this->getParentCourse();
        if (is_null($crs)) {
            return $crs;
        }
        $buffer = array();
        foreach ($crs->getMembersObject()->getAdmins() as $key => $admin_id) {
            $buffer[] = sprintf(
                "%s (%s)",
                \ilObjUser::_lookupFullname($admin_id),
                \ilObjUser::_lookupEmail($admin_id)
            );
        }

        return $buffer;
    }

    /**
     * Get the static link to enter the settings of webinar
     *
     * @return string | null
     */
    public function getLinkToSettings()
    {
        require_once("Services/Link/classes/class.ilLink.php");
        global $DIC;
        $access = $DIC->access();

        $link = null;
        if ($access->checkAccess("visible", "", $this->getRefId())
            && $access->checkAccess("read", "", $this->getRefId())
            && $access->checkAccess("write", "", $this->getRefId())
        ) {
            $link = ilLink::_getLink($this->getRefId(), $this->getType(), array("cmd" => "editProperties"));
        }

        return $link;
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
        $settings = $this->getObjSettings();

        $location_from_course = false;
        if (array_key_exists('venue_source', $config)) {
            $location_from_course = $config['venue_source'] === "venue_from_course";
        }
        $settings = $settings->withLocationFromCourse($location_from_course);

        if ($location_from_course === false) {
            if (array_key_exists('venue', $config)) {
                if (!is_null($config['venue']) && $config['venue'] !== '') {
                    $settings = $settings->withLocationObjId((int) $config['venue']);
                }
            }
        }

        $dates_from_course = false;
        if (array_key_exists('date_source', $config)) {
            $dates_from_course = $config['date_source'] === "date_source_course";
        }
        $settings = $settings->withDatesByCourse($dates_from_course);

        if ($dates_from_course === false) {
            if (array_key_exists('startdate', $config) &&
                array_key_exists('enddate', $config)
            ) {
                if (!is_null($config['startdate']) &&
                    !is_null($config['enddate'])
                ) {
                    $start_date = new \DateTime($config['startdate']);
                    $end_date = new \DateTime($config['enddate']);
                    $settings = $settings
                        ->withStartDate($start_date)
                        ->withEndDate($end_date);
                }
            }
        }

        $prior_day = false;
        if (array_key_exists('prior_day', $config)) {
            $prior_day = $config['prior_day'] === "1";
        }

        $post_day = false;
        if (array_key_exists('following_day', $config)) {
            $post_day = $config['following_day'] === "1";
        }

        $edit_notes = false;
        if (array_key_exists('edit_notes', $config)) {
            $edit_notes = $config['edit_notes'] === "1";
        }

        $settings = $settings
            ->withPriorDayAllowed($prior_day)
            ->withFollowingDayAllowed($post_day)
            ->withEditNotes($edit_notes)
        ;

        if (array_key_exists('deadline', $config)) {
            $settings = $settings->withBookingEnd((int) $config['deadline']);
        }

        $this->setObjSettings($settings);
        $this->doUpdate();
    }


    /**
     * Get the exporter
     *
     * @return 	Export\PDFExport
     */
    public function getPDFExporter()
    {
        return new Export\PDFExport(
            $this,
            $this->getActions()
        );
    }


    /**
     * Get all course-roles for a single user
     *
     * @param 	int 	$usr_id
     * @return 	string[]
     */
    public function getRolesForUser($usr_id)
    {
        assert('is_int($usr_id)');
        global $rbacreview;

        $crs_ref_id = $this->getParentCourseRefId();
        $usr_roles = $rbacreview->assignedRoles($usr_id);
        $local_roles = $rbacreview->getLocalRoles($crs_ref_id);
        $waiting_list_users = $this->getUsersFromWaitingList();


        $txt = $this->getTxtClosure();

        if (in_array($usr_id, $waiting_list_users)) {
            return [$txt('crs_waiting')];
        }

        $buf = [];
        foreach ($usr_roles as $role_id) {
            if (in_array($role_id, $local_roles)) {
                $role_title = \ilObject::_lookupTitle($role_id);
                if (substr($role_title, 0, 3) === 'il_') {
                    $role_title = substr($role_title, 3, strrpos($role_title, (string) $crs_ref_id) - 4);
                    $role_title = $txt($role_title);
                }
                $buf[] = $role_title;
            }
        }
        return $buf;
    }

    /**
     * Get user-ids from waiting list
     *
     * @return 	int[]
     */
    public function getUsersFromWaitingList()
    {
        $ci = $this->getParentCourseInfo();
        $crs_obj_id = (int) $ci['obj_id'];

        include_once('./Modules/Course/classes/class.ilCourseWaitingList.php');
        $waiting_list = new ilCourseWaitingList($crs_obj_id);
        return $waiting_list->getUserIds();
    }


    /**
     *
     */
    public function getCourseTimetable()
    {
        $vals = array();
        $sessions = $this->getPlugin()->getAllChildrenOfByType($this->getParentCourseRefId(), "sess");

        if (count($sessions) > 0) {
            foreach ($sessions as $session) {
                $appointment = $session->getFirstAppointment();
                $sort_date = $appointment->getStart()->get(IL_CAL_FKT_DATE, "YmdHi");
                $start_date = $appointment->getStart()->get(IL_CAL_FKT_DATE, "d.m.Y");
                $start_time = $appointment->getStart()->get(IL_CAL_FKT_DATE, "H:i");
                $end_time = $appointment->getEnd()->get(IL_CAL_FKT_DATE, "H:i");
                $vals[$sort_date] = array($start_date, $start_time, $end_time);
            }
        }
        ksort($vals, SORT_NUMERIC);

        $timetable = array();
        foreach ($vals as $sortdat => $times) {
            list($date, $start, $end) = $times;
            $timetable[] = sprintf(
                "%s, %s - %s %s",
                $date,
                $start,
                $end,
                $this->g_lang->txt('oclock')
            );
        }
        return $timetable;
    }
}
