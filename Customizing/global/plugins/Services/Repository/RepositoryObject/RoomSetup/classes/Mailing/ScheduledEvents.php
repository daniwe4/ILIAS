<?php
namespace CaT\Plugins\RoomSetup\Mailing;

use CaT\Plugins\RoomSetup;

/**
 * RoomSetup shall be mailed x days prior to training.
 * Note, that there are two mails: one for setup, and one for service/catering.
 * This is to schedule the events.
 *
 * @author Nils Haagen	<nils.haagen@concepts-and-training.de>
 */
trait ScheduledEvents
{

    /**
     * @var ILIAS\TMS\ScheduledEvents\Schedule | null
     */
    protected $schedule;


    /**
     * @inheritdoc
     */
    public function getVenueDB()
    {
        if ($this->venue_db === null) {
            $this->venue_db = new RoomSetup\Venue\ilDB((int) $this->getRefId());
        }
        return $this->venue_db;
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
     * Get the schedule for deferred events
     *
     * @return ILIAS\TMS\ScheduledEvents\Schedule
     */
    protected function getSchedule()
    {
        if ($this->schedule === null) {
            require_once('./Services/TMS/ScheduledEvents/classes/Schedule.php');
            global $DIC;
            $this->schedule = new \Schedule($DIC->database());
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
     * Delete ScheduledEvents for mails issued by this object.
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
     * Get the recipient for room-setup and -service,
     * taking sources (local, venue) into account.
     *
     * @param 	int 	$setting_type
     * @throws 	\LogicException 	if a wrong setting-type is provided
     * @return 	string | null
     */
    public function getEffectiveMailRecipient($setting_type)
    {
        assert('is_int($setting_type)');
        $settings = $this->getSettings();
        $setting = null;
        foreach ($settings as $obj_setting) {
            if ($obj_setting->getType() === $setting_type) {
                $setting = $obj_setting;
            }
        }

        if (is_null($setting)) {
            throw new \LogicException("No such setting-type", 1);
        }

        $use_venuesettings = $setting->getRecipientMode() === RoomSetup\ilObjectActions::M_COURSE_VENUE;
        if ($use_venuesettings) {
            //from course
            $ci = $this->getParentCourseInfo();
            $crs_obj_id = (int) $ci['obj_id'];
            $venue = $this->getRealVenueByCourseId($crs_obj_id);
            if (is_null($venue)) {
                return null;
            }

            if ($setting_type === RoomSetup\Settings\RoomSetup::TYPE_SERVICE) {
                return $venue->getService()->getMailServiceList();
            }
            if ($setting_type === RoomSetup\Settings\RoomSetup::TYPE_ROOMSETUP) {
                return $venue->getService()->getMailRoomSetup();
            }
        } else {
            //use local settings
            return $setting->getRecipient();
        }
    }


    /**
     * Get the number of days prior to the training's start when the mail
     * should be triggered, taking sources (local, venue) into account.
     *
     * Returns offset_days.
     *
     * @return array<int, int> 	key is setting-type
     */
    protected function getEffectiveMailOffsetDays()
    {
        $settings = $this->getSettings();
        $ret = array();
        foreach ($settings as $setting) {
            $use_venuesettings = $setting->getRecipientMode() === RoomSetup\ilObjectActions::M_COURSE_VENUE;

            if ($use_venuesettings) { //from course
                $ci = $this->getParentCourseInfo();
                $crs_obj_id = (int) $ci['obj_id'];
                $venue = $this->getRealVenueByCourseId($crs_obj_id);
                if (is_null($venue)) {
                    $offset_days = null;
                } else {
                    switch ($setting->getType()) {
                        case RoomSetup\Settings\RoomSetup::TYPE_SERVICE:
                            $offset_days = $venue->getService()->getDaysSendService();
                            break;
                        case RoomSetup\Settings\RoomSetup::TYPE_ROOMSETUP:
                            $offset_days = $venue->getService()->getDaysSendRoomSetup();
                            break;
                    }
                }
            } else { //use local settings
                $offset_days = $setting->getSendDaysBefore();
            }

            $ret[$setting->getType()] = $offset_days;
        }

        return $ret;
    }


    /**
     * Get due-dates for all setting-types.
     *
     * @return array <string, \DateTime | null>
     */
    public function getDueDates()
    {
        $ret = [];

        $parent_course = $this->getParentCourse();

        if (is_null($parent_course)) {
            return $ret;
        }

        $course_start = $parent_course->getCourseStart();
        $all_offset_days = $this->getEffectiveMailOffsetDays();

        foreach ($all_offset_days as $setting_type => $offset_days) {
            if (is_null($course_start) || is_null($offset_days)) {
                $ret[$setting_type] = null;
            } else {
                $crs_start = $course_start->get(IL_CAL_DATE);
                $crs_start = new \DateTime($crs_start);
                $offset = new \DateInterval('P' . $offset_days . 'D');
                $due = clone $crs_start;
                $due->sub($offset);
                $now = new \DateTime();

                if ($now < $due) {
                    $ret[$setting_type] = $due;
                }
            }
        }
        return $ret;
    }

    /**
     * Calculate point of time for sending of accomodation lists,
     * create deferred events.
     *
     * @return void
     */
    public function scheduleMailingEvents()
    {
        if (!is_null($this->getRefId())) {
            $parent_course = $this->getParentCourse();

            if (is_null($parent_course)) {
                return;
            }

            $schedule = $this->getSchedule();

            $this->deleteExistingMailingEvents();

            foreach ($this->getDueDates() as $setting_type => $due) {
                if (!is_null($due)) {
                    $event = $this->getEventForSettingType($setting_type);
                    $schedule->create(
                        (int) $this->getRefId(),
                        $due,
                        'Modules/Course',
                        $event,
                        [
                            'crs_ref_id' => $parent_course->getRefId(),
                            BaseOccasion::PARAM_OWNER_REF => $this->getRefId()
                        ]
                    );
                }
            }
        }
    }

    /**
     * Get the event-id according to setting-type
     *
     * @param 	int 	$setting_type
     * @return 	string|null
    */
    public function getEventForSettingType($setting_type)
    {
        assert('is_int($setting_type)');
        switch ($setting_type) {
            case RoomSetup\Settings\RoomSetup::TYPE_SERVICE:
                return BaseOccasion::EVENT_SEND_SERVICE;
            case RoomSetup\Settings\RoomSetup::TYPE_ROOMSETUP:
                return BaseOccasion::EVENT_SEND_ROOMSETUP;
        }
        return null;
    }


    /**
    * @param bool 	$throw
    * @return plugin|false
    */
    private function getVenuePlug($throw = true)
    {
        if (\ilPluginAdmin::isPluginActive('venues') !== true) {
            if ($throw) {
                throw new \Exception('Venue plugin is not available');
            }
            return false;
        }
        return \ilPluginAdmin::getPluginObjectById('venues');
    }


    /**
     * Get a Venue-Object by course-id.
     * The "real" venue is the venue as issued by the Venues-Plugin.
     *
     * @param 	int 	$course_id
     * @return 	Venue | null
     */
    public function getRealVenueByCourseId($course_id)
    {
        assert('is_int($course_id)');
        $vplug = $this->getVenuePlug();
        $vactions = $vplug->getActions();
        $vassignment = $vactions->getAssignment($course_id);

        if (!$vassignment) {
            return null;
        }

        if ($vassignment->isListAssignment()) {
            $venue_id = $vassignment->getVenueId();
            if ((int) $venue_id == 0) {
                return null;
            }
            return $vactions->getVenue($venue_id);
        }
        return null;
    }
}
