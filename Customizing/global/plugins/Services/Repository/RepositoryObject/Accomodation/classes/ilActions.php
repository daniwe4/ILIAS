<?php

namespace CaT\Plugins\Accomodation;

use CaT\Plugins\Accomodation\Reservation;
use CaT\Plugins\Accomodation\Reservation\Note\Note;
use CaT\Plugins\Accomodation\Reservation\UserReservations;

/**
 * Communication class between front- and backend.
 * E.g. GUI only use this class to get information from ILIAS DB.
 */
class ilActions
{
    /**
     * Constructor of the class ilActions
     *
     * @param ObjAccomodation 		$obj
     * @param ObjSettings\DB 		$settings_db
     * @param Reservation\DB		$reservations_db
     * @param Reservation\Note\DB 	$note_db
     * @param Venue\DB				$venue_db
     * @param \ilAppEventHandler 	$app_event_handler
     */
    public function __construct(
        ObjAccomodation $obj,
        ObjSettings\DB 	$settings_db,
        Reservation\DB	$reservations_db,
        Reservation\Note\DB $note_db,
        Venue\DB	$venue_db,
        \ilAppEventHandler $app_event_handler,
        ObjSettings\Overnights $overnights
    ) {
        $this->object = $obj;
        $this->settings_db = $settings_db;
        $this->reservations_db = $reservations_db;
        $this->note_db = $note_db;
        $this->venue_db = $venue_db;
        $this->app_event_handler = $app_event_handler;
        $this->overnights = $overnights;
    }

    /**
     * Get the plugin's directory
     *
     * @return string
     */
    public function getPluginDirectory()
    {
        return $this->object->getPluginDirectory();
    }

    /**
     * Update the object, i.e. write to DB.
     * @return void
     */
    public function updateObject()
    {
        $this->object->update();
    }

    /**
     * Get the object of the plugin.
     *
     * @return \ilObjAccomodation
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * get the object-id for this object
     * @return string
     */
    public function getObjId()
    {
        return (int) $this->object->getId();
    }

    /**
     * get the object's ref_id for this object
     * @return string
     */
    public function getRefId()
    {
        return (int) $this->object->getRefId();
    }

    /**
     * get the title for this object
     * @return string
     */
    public function getTitle()
    {
        return $this->object->getTitle();
    }

    /**
     * get the description for this object
     * @return string
     */
    public function getDescription()
    {
        return $this->object->getDescription();
    }

    /**
     * @param string $title
     * @param string $description
     * @return void
     */
    public function updateObjBasicSettings($title, $description = '')
    {
        $this->object->setTitle($title);
        $this->object->setDescription($description);
    }

    /**
     * @return ObjSettings
     */
    public function getObjSettings()
    {
        return $this->object->getObjSettings();
    }

    /**
     * @param \Closure $update_function
     * @return void
     */
    public function updateObjSettings(\Closure $update_function)
    {
        $this->object->updateObjSettings($update_function);
    }

    /**
     * @return array<int,string>
     */
    public function getVenueListFromPlugin()
    {
        return $this->venue_db->getVenueListFromPlugin();
    }

    /**
     * Get venue by id
     *
     * @param int 	$venue_id
     */
    public function getVenueById($venue_id)
    {
        return $this->venue_db->getVenueFromPlugin($venue_id);
    }


    /**
     * @return array<string, mixed>
     */
    public function getCourseInformation()
    {
        return $this->object->getParentCourseInfo();
    }

    /**
     * @return Reservation[]
     */
    public function getReservationsForUser($usr_id)
    {
        $obj_id = (int) $this->object->getId();
        return $this->reservations_db->selectForUserInObject($usr_id, $obj_id);
    }

    /**
     * Return a UserReservations-object for a user.
     *
     * @param 	int 	$usr_id
     * @return 	UserReservations
     */
    public function getUserReservationsAtObj($usr_id) : Reservation\UserReservations
    {
        $obj_id = (int) $this->object->getId();
        $note = $this->note_db->selectNoteFor($obj_id, $usr_id);
        $reservations = $this->reservations_db->getUserReservations($obj_id, $usr_id);

        return new UserReservations($usr_id, $reservations, $note);
    }

    /**
     * Get a UserReservation at this object for every course member.
     *
     * @return 	UserReservation[]
     */
    public function getAllUserReservationsAtObj($include_waiting = false)
    {
        assert('is_bool($include_waiting)');
        $ret = array();
        $usr_ids = $this->getParentCourseMembers();
        if ($include_waiting) {
            $usr_ids = array_merge($usr_ids, $this->object->getUsersFromWaitingList());
        }
        foreach ($usr_ids as $usr_id) {
            $ret[] = $this->getUserReservationsAtObj((int) $usr_id);
        }
        return $ret;
    }

    /**
     * Check, if there are any user reservations.
     *
     * @return 	bool
     */
    public function getUserReservationsExist()
    {
        $obj_id = (int) $this->object->getId();
        return count($this->reservations_db->selectAllForObj($obj_id)) > 0;
    }

    /**
     * @param int 	$usr_id
     * @return void
     */
    public function deleteAllUserReservations($usr_id)
    {
        assert('is_int($usr_id)');
        $obj_id = (int) $this->object->getId();
        $this->reservations_db->deleteAllUserReservations($obj_id, $usr_id);
    }

    /**
     * @return Venue | null
     */
    public function getLocation()
    {
        return $this->object->getVenue();
    }

    /**
     * Get the date until which boking is allowed
     * @return \ilDateTime | null
     */
    public function getBookingDeadline()
    {
        $deadline = $this->overnights->getBookingDeadline();
        if (is_null($deadline)) {
            return null;
        }
        return new \ilDateTime($deadline->format('Y-m-d'), IL_CAL_DATE);
    }

    /**
     * Returns true, if booking is still possible according to the
     * given deadline.
     * @return bool
     */
    public function isInBookingDeadline()
    {
        $deadline_date = $this->getBookingDeadline();
        if (is_null($deadline_date)) {
            return false;
        }
        $now = time();
        return $now < $deadline_date->get(IL_CAL_UNIX);
    }

    /**
     * Get reservation-options for user.
     *
     * @return string[]
     */
    public function getReservationOptionsForUser()
    {
        return $this->overnights->getOvernightsForUser();
    }
    /**
     * Get all reservation-options (including prior/post night).
     *
     * @return string[]
     */
    public function getFullReservationOptions()
    {
        return $this->overnights->getOvernightsExtended();
    }

    /**
     * Get date of prior night
     *
     * @return string
     */
    public function getPriorNightDate()
    {
        return $this->overnights->getPriorNight();
    }

    /**
     * Get date of post night
     *
     * @return string
     */
    public function getPostNightDate()
    {
        return $this->overnights->getPostNight();
    }

    /**
     * @param \Closure 	$update_function
     * @return void
     */
    public function updateUserReservations(\Closure $update_function, $usr_id)
    {
        assert('is_int($usr_id)');
        $obj_id = (int) $this->object->getId();

        $user_reservation_actions = $update_function(
            $this->reservations_db->selectForUserInObject($usr_id, $obj_id),
            array(
                'delete' => array(),
                'create' => array(),
                'update' => array(),
            )
        );

        foreach ($user_reservation_actions['delete'] as $id) {
            $this->reservations_db->deleteForId($id);
        }

        foreach ($user_reservation_actions['create'] as $reservation_data) {
            $this->reservations_db->createReservation(
                $obj_id,
                $usr_id,
                $reservation_data['date'],
                (bool) $reservation_data['selfpay']
            );
        }

        foreach ($user_reservation_actions['update'] as $reservation) {
            $this->reservations_db->update($reservation);
        }
    }

    public function getNoteFor(int $obj_id, int $usr_id)
    {
        return $this->note_db->selectNoteFor($obj_id, $usr_id);
    }

    public function updateNote(string $note, int $usr_id)
    {
        $obj_id = (int) $this->object->getId();

        if ($this->note_db->nodeExists($obj_id, $usr_id)) {
            $this->note_db->update($obj_id, $usr_id, $note);
        } else {
            $this->note_db->createNote($obj_id, $usr_id, $note);
        }
    }

    /**
     * Insert a new reservation.
     *
     * @param int 	$obj_id
     * @param int 	$usr_id
     * @param int 	$session_id
     * @param string 	$date
     * @param bool 	$selfpay
     * @return Reservation
     */
    //2do: remove session_id from params
    public function insertReservation($obj_id, $usr_id, $session_id, $date, $selfpay)
    {
        $existing = $this->reservations_db->selectForUserInObject($usr_id, $obj_id);
        foreach ($existing as $er) {
            if ($er->getDate()->get(IL_CAL_DATE) === $date
                //&& $er->getSessionObjId() === $session_id
            ) {
                return $er;
            }
        }
        return $this->reservations_db->createReservation(
            $obj_id,
            $usr_id,
            $date,
            $selfpay
        );
    }

    /**
     * format date according to user settings.
     * @param string|ilDateTime 	$dat
     * @param bool 	$fromstring
     * @return string
     */
    public function formatDate($dat, $fromstring = false)
    {
        require_once('Services/Calendar/classes/class.ilCalendarUtil.php');
        global $ilUser;
        $use_time = false;
        if ($fromstring) {
            $dat = new \ilDateTime($dat, IL_CAL_DATE);
        }
        $out_format = \ilCalendarUtil::getUserDateFormat($use_time, true);
        return $dat->get(IL_CAL_FKT_DATE, $out_format, $ilUser->getTimeZone());
    }

    public function raiseDeleteEvent()
    {
        $parent = $this->object->getParentCourseInfo();
        if ($parent !== false) {
            $e["xoac_parent_crs_info"] = $parent;
            $e["xoac_objects"] = array($this->getObjId());
            $e['accomodation'] = '';
            $e['venue_from_course'] = null;
            $this->app_event_handler->raise("Plugin/Accomodation", "deleteAccomodation", $e);
        }
    }

    /**
     * Raises update event
     *
     * @return void
     */
    public function raiseUpdateEvent()
    {
        $e["xoac_parent_crs_info"] = $this->object->getParentCourseInfo();
        $e["xoac_objects"] = array($this->getObjId());

        $e['xoac_date_start'] = $this->overnights->getEffectiveStartDate();
        $e['xoac_date_end'] = $this->overnights->getEffectiveEndDate();

        $e['xoac_venue_from_course'] = $this->object->getObjSettings()->getLocationFromCourse();

        /** @var \CaT\Plugins\Accomodation\ObjSettings\ObjSettings $settings */
        $settings = $this->getObjSettings();
        $e['xoac_user_allowed_prior'] = $settings->isPriorDayAllowed();
        $e['xoac_user_allowed_following'] = $settings->isFollowingDayAllowed();

        $e['xoac_venue'] = null;
        $location = $this->getLocation();
        if (!is_null($location)) {
            $e['xoac_venue'] = $location->getName();
        }
        $this->app_event_handler->raise("Plugin/Accomodation", "updateAccomodation", $e);
    }

    /**
     * Raises reservation update event
     *
     * @param int 	$user_id
     *
     * @return void
     */
    public function raiseReservationUpdateEventFor($user_id)
    {
        $user_reservations = $this->getReservationsForUser($user_id);

        $e["xoac_parent_crs_info"] = $this->object->getParentCourseInfo();
        $e["xoac_objects"] = array($this->getObjId());
        $e["xoac_reservations"] = $user_reservations;
        $e["xoac_usr_id"] = $user_id;
        $e["xoac_priorday"] = 0;
        $e["xoac_followingday"] = 0;

        $dates = array();
        foreach ($user_reservations as $reservation) {
            $dates[] = $reservation->getDate()->get(IL_CAL_DATE);
        }

        if (in_array($this->overnights->getPriorNight(), $dates)) {
            $e["xoac_priorday"] = 1;
        }
        if (in_array($this->overnights->getPostNight(), $dates)) {
            $e["xoac_followingday"] = 1;
        }

        $this->app_event_handler->raise("Plugin/Accomodation", "updateReservations", $e);
    }

    /**
     * Get ids of users that are members of the parent course.
     *
     * @return int[]
     */
    public function getParentCourseMembers()
    {
        return $this->object->getCourseParticipantsIds();
    }

    /**
     * Get information about the parent course to put into xls-header.
     * Return null, if there is no parent course.
     *
     * @return array<string, string> | null
     */
    public function getParentCourseHeaderInfo()
    {
        return $this->object->getParentCourseInfoForExportHeader();
    }

    /**
     * Get tutors from parent course to put into xls-header.
     * Return null, if there is no parent course.
     *
     * @return array<string, string> | null
     */
    public function getParentCourseTutorInfo()
    {
        return $this->object->getTutorsForExportHeader();
    }

    /**
     * Get admins from parent course to put into xls-header.
     * Return null, if there is no parent course.
     *
     * @return array<string, string> | null
     */
    public function getParentCourseAdminInfo()
    {
        return $this->object->getAdminsForExportHeader();
    }

    /**
     * Export the list and return path to file.
     *
     * @return string
     */
    public function exportAccomodationList()
    {
        $exporter = $this->getObject()->getPDFExporter();
        $exporter->writeOutput();
        return $exporter->getFilePath();
    }

    /**
     * Get all course-roles for a single user
     *
     * @param 	int 	$usr_id
     * @return 	string[]
     */
    public function getCourseRolesOfUser($usr_id)
    {
        assert('is_int($usr_id)');
        return $this->object->getRolesForUser($usr_id);
    }

    /**
     * @return string[]
     */
    public function getSessionsTimeTable()
    {
        return $this->object->getCourseTimetable();
    }

    /**
     * Get all user-ids that have a reservation and this object.
     * This is needed by the UserCourseHistorizing.
     *
     * @return 	int[]
     */
    public function getAllUserIdsWithReservationsAtObj()
    {
        $obj_id = (int) $this->object->getId();
        $all_reservations = $this->reservations_db->selectAllForObj($obj_id);
        $ret = array_map('intval', array_keys($all_reservations));
        return $ret;
    }

    /**
     * @param string $dat
     */
    public function getNextDayLabel($dat)
    {
        assert('is_string($dat)');
        $ildat = new \ilDateTime($dat, IL_CAL_DATE);
        $ildat_next = clone $ildat;
        $ildat_next->increment(\ilDateTime::DAY, 1);
        $label_next = $this->formatDate($ildat_next, false);
        return $label_next;
    }
}
