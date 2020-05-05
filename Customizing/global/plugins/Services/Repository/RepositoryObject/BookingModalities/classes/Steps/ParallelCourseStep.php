<?php

namespace CaT\Plugins\BookingModalities\Steps;

use \ILIAS\TMS\Booking;
use \CaT\Ente\Entity;
use CaT\Ente\ILIAS\ilHandlerObjectHelper;

use ILIAS\TMS\CourseInfo;
use ILIAS\TMS\CourseInfoHelper;
use \ILIAS\TMS\MyUsersHelper;
use ILIAS\UI;

abstract class ParallelCourseStep
{
    use ilHandlerObjectHelper;
    use CourseInfoHelper;
    use MyUsersHelper;

    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    protected function getEntityRefId()
    {
        return $this->entity()->object()->getRefId();
    }

    protected function getUIRenderer()
    {
        return $this->getDIC()->ui()->renderer();
    }

    protected function getActingUser()
    {
        return $this->acting_user;
    }

    public function withActingUser(int $usr_id)
    {
        $clone = clone $this;
        $clone->acting_user = new \ilObjUser($usr_id);
        return $clone;
    }

    protected function getTree()
    {
        return $this->getDIC()->repositoryTree();
    }

    protected function getObjDefinition()
    {
        $DIC = $this->getDIC();
        return $DIC["objDefinition"];
    }

    /**
     */
    protected function getUIFactory()
    {
        global $DIC;
        return $DIC->ui()->factory();
    }

    const PARALLEL_CONFIRMATION_CHECKBOX = "sup_conf_checkbox";

    /**
     * @var	Entity
     */
    protected $entity;

    /**
     * @var callable
     */
    protected $txt;

    /**
     * @var	string | null
     */
    protected $modalities_doc;

    /**
     * @var \ilObjUser
     */
    protected $acting_user;

    public function __construct(
        Entity $entity,
        callable $txt,
        \ilObjBookingModalities $owner,
        \ilObjUser $acting_user
    ) {
        $this->entity = $entity;
        $this->txt = $txt;
        $this->owner = $owner;
        $this->acting_user = $acting_user;
    }

    /**
     * i18n
     *
     * @param	string	$id
     * @return	string	$text
     */
    protected function txt($id)
    {
        assert('is_string($id)');
        return call_user_func($this->txt, $id);
    }

    /**
     * @inheritdocs
     */
    public function entity()
    {
        return $this->entity;
    }

    /**
     * @inheritdocs
     */
    public function getLabel()
    {
        return $this->txt("parallel_courses");
    }

    /**
     * Get a description for this step in the process.
     *
     * @return	string
     */
    public function getDescription()
    {
        return $this->txt("unknown");
    }

    /**
     * Get the priority of the step.
     *
     * Lesser priorities means the step should be performed earlier.
     *
     * @return	int
     */
    public function getPriority()
    {
        return 10;
    }

    /**
     * Find out if this step is applicable for the booking process of the
     * given user.
     *
     * @param	int	$usr_id
     * @return	bool
     */
    public function isApplicableFor($usr_id)
    {
        $course = $this->entity->object();
        $parallel_courses = $this->getParallelCoursesOfUser($course, $usr_id);
        $parallel_waiting = $this->getParallelWaitingListCoursesOfUser($course, $usr_id);

        require_once("Modules/Course/classes/class.ilCourseParticipants.php");
        require_once("Services/Membership/classes/class.ilWaitingList.php");
        return !\ilCourseParticipants::_isParticipant($course->getRefId(), $usr_id)
                && !\ilWaitingList::_isOnList($usr_id, $course->getId())
                && (count($parallel_courses) > 0 || count($parallel_waiting) > 0)
                && $this->owner->getBooking()->getModus() == "self_booking";
    }

    /**
     * @inheritdoc
     */
    public function appendToStepForm(\ilPropertyFormGUI $form, $usr_id)
    {
        require_once("Services/Form/classes/class.ilCheckboxInputGUI.php");
        require_once("Services/Form/classes/class.ilFormSectionHeaderGUI.php");

        $sec = new \ilFormSectionHeaderGUI();
        $sec->setTitle($this->getLabel());
        $form->addItem($sec);

        $course = $this->entity->object();
        $parallel_courses = $this->getParallelCoursesOfUser($course, $usr_id);
        $parallel_waiting = $this->getParallelWaitingListCoursesOfUser($course, $usr_id);
        $item = new \ilNonEditableValueGUI($this->txt("parallel_courses"), "", true);
        $item->setValue($this->getParallelCourseMessage($parallel_courses, $parallel_waiting));
        $form->addItem($item);

        $online = new \ilCheckboxInputGUI("", self::PARALLEL_CONFIRMATION_CHECKBOX);
        $online->setInfo($this->getParallelCoursesConfirmationMessage());
        $form->addItem($online);
    }

    /**
     * Get the parallel course confirmation message
     *
     * @return string
     */
    protected function getParallelCoursesConfirmationMessage()
    {
        return $this->txt("parallel_courses_confirmation");
    }

    /**
     * Get the parallel course confirmation alert message
     *
     * @return string
     */
    protected function getParallelCoursesConfirmationAlertMessage()
    {
        return $this->txt("parallel_courses_confirmation_alert");
    }

    /**
     * Get the data the step needs to store until the end of the process, based
     * on the form.
     *
     * The data needs to be plain PHP data that can be serialized/unserialized
     * via json.
     *
     * If null is returned, the form was not displayed correctly and needs to
     *
     * @param	\ilPropertyFormGUI	$form
     * @return	bool|null
     */
    public function getData(\ilPropertyFormGUI $form)
    {
        $ok = true;
        if (!$form->getInput(self::PARALLEL_CONFIRMATION_CHECKBOX)) {
            $ok = false;
            $item = $form->getItemByPostVar(self::PARALLEL_CONFIRMATION_CHECKBOX);
            $item->setAlert($this->getParallelCoursesConfirmationAlertMessage());
        }

        if ($ok) {
            return true;
        } else {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function addDataToForm(\ilPropertyFormGUI $form, $data)
    {
        $values = array();
        $values[self::PARALLEL_CONFIRMATION_CHECKBOX] = true;
        $form->setValuesByArray($values);
    }

    /**
     * @inheritdoc
     */
    public function appendToOverviewForm($data, \ilPropertyFormGUI $form, $usr_id)
    {
        require_once("Services/Form/classes/class.ilFormSectionHeaderGUI.php");

        $course = $this->entity->object();
        $parallel_courses = $this->getParallelCoursesOfUser($course, $usr_id);
        $parallel_waiting = $this->getParallelWaitingListCoursesOfUser($course, $usr_id);

        $item = new \ilNonEditableValueGUI($this->txt("parallel_courses"), "", true);
        $item->setValue($this->getParallelCourseMessage($parallel_courses, $parallel_waiting));
        $form->addItem($item);
    }

    /**
     * Process the data to perform the actions in the system that are required
     * for the step.
     *
     * The data must be the same as the component return via getData.
     *
     * @param	int     $crs_ref_id
     * @param	int     $usr_id
     * @param	mixed $data
     * @return	void
     */
    public function processStep($crs_ref_id, $usr_id, $data)
    {
    }

    /**
     * Checks the user a parelle course to this he wants to book
     *
     * @param	int		$crs_ref_id
     * @param	int		$usr_id
     * @return	\ilObjCourse[]
     */
    protected function getParallelCoursesOfUser($crs, $usr_id)
    {
        assert('is_int($usr_id)');

        $booked_courses = $this->getUserBookedCourses($usr_id);
        $parallel_courses = $this->getParallelCourses($crs, $booked_courses);

        return $parallel_courses;
    }

    /**
     * Checks the user a parelle course to this he wants to book
     *
     * @param	int		$crs_ref_id
     * @param	int		$usr_id
     * @return	\ilObjCourse[]
     */
    protected function getParallelWaitingListCoursesOfUser($crs, $usr_id)
    {
        assert('is_int($usr_id)');

        $waitinglist_courses = $this->getUserWaitinglistCourses($usr_id);
        $parallel_waiting = $this->getParallelCourses($crs, $waitinglist_courses);

        return $parallel_waiting;
    }

    /**
     * Get courses where user is booked
     *
     * @param int	$usr_id
     *
     * @return \ilObjCourse[]
     */
    protected function getUserBookedCourses($usr_id)
    {
        $ret = array();
        require_once("Services/Membership/classes/class.ilParticipants.php");
        foreach (\ilParticipants::_getMembershipByType($usr_id, "crs", true) as $crs_id) {
            $ref_id = array_shift(\ilObject::_getAllReferences($crs_id));
            $ret[] = \ilObjectFactory::getInstanceByRefId($ref_id);
        }

        return $ret;
    }

    /**
     * Get courses where user is on waiting list
     *
     * @param int	$usr_id
     *
     * @return \ilObjCourse[]
     */
    protected function getUserWaitinglistCourses($usr_id)
    {
        $ret = array();
        require_once("Services/Membership/classes/class.ilWaitingList.php");
        foreach (\ilWaitingList::getIdsWhereUserIsOnList($usr_id) as $crs_id) {
            $ref_id = array_shift(\ilObject::_getAllReferences($crs_id));
            $ret[] = \ilObjectFactory::getInstanceByRefId($ref_id);
        }

        return $ret;
    }

    /**
     * Get courses running parallel
     *
     * @param \ilObjCourse 	$try_to_book_course
     * @param \ilObjCourse[] 	$check_courses
     *
     * @return \ilObjCourse[]
     */
    protected function getParallelCourses(\ilObjCourse $try_to_book_course, array $check_courses)
    {
        if ($try_to_book_course->getCourseStart() === null) {
            return array();
        }

        $try_sessions = $this->getSessionAppointments($try_to_book_course);
        $parallel_courses = array_map(function ($course) use ($try_sessions) {
            if ($course->getCourseStart() === null) {
                return null;
            }

            $ret = array("title" => $course->getTitle());

            $ret["venue"] = $this->getVenueOfCourse($course);
            $sessions = $this->getSessionAppointments($course);
            $block_sessions = array();
            foreach ($try_sessions as $try_date) {
                $try_start = $try_date["start"]->get(IL_CAL_DATE);
                $try_end = $try_date["end"]->get(IL_CAL_DATE);

                foreach ($sessions as $date) {
                    $start = $date["start"]->get(IL_CAL_DATE);
                    $end = $date["end"]->get(IL_CAL_DATE);

                    if (($try_start <= $start && $try_end >= $start)
                        || ($try_start >= $start && $try_start <= $end)
                    ) {
                        $block_sessions[] = $date;
                    }
                }
            }

            if (count($block_sessions) > 0) {
                $ret["sessions"] = $block_sessions;
                return $ret;
            }

            return null;
        }, $check_courses);

        return array_filter($parallel_courses, function ($course) {
            return $course !== null;
        });
    }

    /**
     * Get the venue of the course
     *
     * @param \ilObjCourse 	$course
     *
     * @return string
     */
    protected function getVenueOfCourse(\ilObjCourse $course)
    {
        if (\ilPluginAdmin::isPluginActive('venues')) {
            $vplug = \ilPluginAdmin::getPluginObjectById('venues');
            $txt = $vplug->txtClosure();
            list($venue_id, $city, $address, $name, $postcode) = $vplug->getVenueInfos($course->getId());

            return $city;
        }

        return "";
    }

    /**
     * Get all session appointments of course
     *
     * @param ilObjCourse 	$course
     *
     * @return string
     */
    protected function getSessionAppointments($course)
    {
        $vals = array();

        $sessions = $this->getAllChildrenOfByType($course->getRefId(), "sess");
        if (count($sessions) > 0) {
            foreach ($sessions as $session) {
                $appointment = $session->getFirstAppointment();
                $start = $appointment->getStart();
                $end = $appointment->getEnd();
                $offset = $appointment->getDaysOffset();

                $vals[$offset] = array("start" => $start, "end" => $end);
            }
        }

        asort($vals);
        return $vals;
    }

    /**
     * Get first child by type recursive
     *
     * @param int 	$ref_id
     * @param string 	$search_type
     *
     * @return Object 	of search type
     */
    protected function getAllChildrenOfByType($ref_id, $search_type)
    {
        $childs = $this->getTree()->getChilds($ref_id);
        $ret = array();

        foreach ($childs as $child) {
            $type = $child["type"];
            if ($type == $search_type) {
                $ret[] = \ilObjectFactory::getInstanceByRefId($child["child"]);
            }

            if ($this->getObjDefinition()->isContainer($type)) {
                $rec_ret = $this->getAllChildrenOfByType($child["child"], $search_type);
                if (!is_null($rec_ret)) {
                    $ret = array_merge($ret, $rec_ret);
                }
            }
        }

        return $ret;
    }

    /**
     * Get message to display
     *
     * @param \ilObjCourse[] 	$parallel_courses
     * @param \ilObjCourse[] 	$parallel_waiting
     *
     * @return string
     */
    protected function getParallelCourseMessage(array $parallel_courses, array $parallel_waiting)
    {
        $tpl = new \ilTemplate("tpl.parallel_courses.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities");

        $tpl->setVariable("TH_CRS_TITLE", "Kurs");
        $tpl->setVariable("TH_CRS_DATE", "Datum");
        $tpl->setVariable("TH_CRS_TIMES", "Uhrzeit");
        $tpl->setVariable("TH_CRS_VENUE", "Ort");

        if (count($parallel_courses) > 0) {
            $tpl->setCurrentBlock("header");
            $tpl->setVariable("CUTTING_TRAININGS", $this->txt("cutting_trainings"));
            $tpl->parseCurrentBlock();

            $this->fillTemplateWithCourseInfos($tpl, $parallel_courses);
        }

        if (count($parallel_waiting) > 0) {
            $tpl->setCurrentBlock("header");
            $tpl->setVariable("CUTTING_TRAININGS", $this->txt("cutting_waitings"));
            $tpl->parseCurrentBlock();

            $this->fillTemplateWithCourseInfos($tpl, $parallel_waiting);
        }

        $tpl->setVariable("INFO_CAN_STORNO", $this->getInfoForStornoMessage());

        return $tpl->get();
    }

    /**
     * Get the info message for storno
     *
     * @return string
     */
    protected function getInfoForStornoMessage()
    {
        return $this->txt("info_can_storno");
    }

    protected function fillTemplateWithCourseInfos(\ilGlobalTemplateInterface $tpl, $courses)
    {
        foreach ($courses as $key => $course) {
            $tpl->setCurrentBlock("crs");
            $tpl->setVariable("CRS_TITLE", $course["title"]);
            $dates = array();
            $times = array();
            foreach ($course["sessions"] as $session) {
                $start_date = $this->formatDate($session["start"]);
                $end_date = $this->formatDate($session["end"]);

                $start_time = $session["start"]->get(IL_CAL_FKT_DATE, "H:i");
                $end_time = $session["end"]->get(IL_CAL_FKT_DATE, "H:i");

                if ($start_date == $end_date) {
                    $dates[] = $start_date;
                } else {
                    $dates[] = $start_date . " - " . $end_date;
                }
                $times[] = $start_time . " - " . $end_time . " " . $this->txt("hour");
            }

            $tpl->setVariable("CRS_PERIOD_DATE", join("<br />", $dates));
            $tpl->setVariable("CRS_PERIOD_TIME", join("<br />", $times));
            $tpl->setVariable("CRS_VENUE", $course["venue"]);

            $tpl->parseCurrentBlock();
        }
    }

    /**
     * Form date.
     *
     * @param ilDateTime 	$dat
     * @param bool 	$use_time
     *
     * @return string
     */
    protected function formatDate(\ilDateTime $date, $use_time = false)
    {
        global $DIC;
        $g_user = $DIC->user();
        require_once("Services/Calendar/classes/class.ilCalendarUtil.php");
        $out_format = \ilCalendarUtil::getUserDateFormat($use_time, true);
        $ret = $date->get(IL_CAL_FKT_DATE, $out_format, $g_user->getTimeZone());
        if (substr($ret, -5) === ':0000') {
            $ret = substr($ret, 0, -5);
        }

        return $ret;
    }

    protected function getAccess()
    {
        if (is_null($this->access)) {
            $this->access = $this->getDIC()["ilAccess"];
        }

        return $this->access;
    }
}
