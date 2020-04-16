<?php

namespace CaT\Plugins\BookingModalities\Steps;

use \ILIAS\TMS\Booking;
use \CaT\Ente\Entity;
use CaT\Ente\ILIAS\ilHandlerObjectHelper;

use ILIAS\TMS\CourseInfo;
use ILIAS\TMS\CourseInfoHelper;
use \ILIAS\TMS\MyUsersHelper;
use ILIAS\UI;

abstract class DuplicateCourseStep
{
    use ilHandlerObjectHelper;
    use CourseInfoHelper;
    use MyUsersHelper;

    const PAST_DAYS = 365;

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

    protected function getDatabase()
    {
        return $this->getDIC()->database();
    }

    /**
     */
    protected function getUIFactory()
    {
        global $DIC;
        return $DIC->ui()->factory();
    }

    const DUPLICATE_CONFIRMATION_CHECKBOX = "sup_conf_checkbox";

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
     * @var	\ilObjUser $acting_user
     */
    protected $acting_user;


    public function __construct(Entity $entity, callable $txt, $owner, \ilObjUser $acting_user)
    {
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
        return $this->txt("duplicate_courses");
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
        return 5;
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
        require_once("Modules/Course/classes/class.ilCourseParticipants.php");
        require_once("Services/Membership/classes/class.ilWaitingList.php");
        $is_booked = \ilCourseParticipants::_isParticipant($course->getRefId(), $usr_id)
                || \ilWaitingList::_isOnList($usr_id, $course->getId());

        if ($is_booked === true) {
            return false;
        }

        $modus = $this->owner->getBooking()->getModus();
        $skip_duplicate_check = $this->owner->getBooking()->getSkipDuplicateCheck();
        $booked_courses = $this->getDuplicateBookedCoursesOfUser($course, $usr_id);
        return count($booked_courses) > 0 && $modus == $this->requiredBookingMode() && !$skip_duplicate_check;
    }

    /**
     * Get the modus is required to be cancel enabled
     *
     * @return string
     */
    abstract protected function requiredBookingMode();

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
        $booked_courses = $this->getDuplicateBookedCoursesOfUser($course, $usr_id);

        $item = new \ilNonEditableValueGUI($this->txt("booked_courses"), "", true);
        $item->setValue($this->getDuplicateCourseMessage($booked_courses));
        $form->addItem($item);

        $online = new \ilCheckboxInputGUI("", self::DUPLICATE_CONFIRMATION_CHECKBOX);
        $online->setInfo($this->getInfoMessage(self::PAST_DAYS, $usr_id));
        $form->addItem($online);
    }

    /**
     * Get the message for checkbox info
     *
     * @param int 	$days
     * @param int 	$usr_id
     *
     * @return string
     */
    protected function getInfoMessage($days, $usr_id)
    {
        return sprintf($this->txt("duplicate_courses_confirmation"), $days);
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
        if (!$form->getInput(self::DUPLICATE_CONFIRMATION_CHECKBOX)) {
            $ok = false;
            $item = $form->getItemByPostVar(self::DUPLICATE_CONFIRMATION_CHECKBOX);
            $item->setAlert(sprintf($this->txt("duplicate_courses_confirmation_alert"), self::PAST_DAYS));
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
        $values[self::DUPLICATE_CONFIRMATION_CHECKBOX] = true;
        $form->setValuesByArray($values);
    }

    /**
     * @inheritdoc
     */
    public function appendToOverviewForm($data, \ilPropertyFormGUI $form, $usr_id)
    {
        require_once("Services/Form/classes/class.ilFormSectionHeaderGUI.php");

        $course = $this->entity->object();
        $booked_courses = $this->getDuplicateBookedCoursesOfUser($course, $usr_id);

        $item = new \ilNonEditableValueGUI($this->txt("booked_courses"), "", true);
        $item->setValue($this->getDuplicateCourseMessage($booked_courses));
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
     * @param \ilObjCourse 	$course
     * @param int 	$usr_id
     * @return \ilObjCourse[]
     */
    protected function getDuplicateBookedCoursesOfUser(\ilObjCourse $course, $usr_id)
    {
        assert('is_int($usr_id)');
        $start_date = $course->getCourseStart();
        if ($start_date === null) {
            return array();
        }

        $template_id = $this->getTemplateIdOf((int) $course->getId());
        $booked = $this->getUserBookedCourses($usr_id);

        return array_filter($booked, function ($booked_course) use ($start_date, $template_id, $usr_id) {
            $end_date = $booked_course->getCourseEnd();
            if ($end_date === null) {
                return false;
            }

            $cal_end_date = clone $end_date;
            $cal_end_date->increment(\ilDateTime::DAY, self::PAST_DAYS);
            $booked_template_id = $this->getTemplateIdOf((int) $booked_course->getId());
            if (is_null($template_id) || is_null($booked_template_id)) {
                return false;
            }

            if ($booked_template_id == $template_id
                && $this->isTemplateCourse($template_id)
                && $cal_end_date->get(IL_CAL_DATE) > $start_date->get(IL_CAL_DATE)
                && $this->courseSuccessfulCompleted($booked_course->getId(), $usr_id)
            ) {
                return true;
            }

            return false;
        });
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
     * Get template id of course
     *
     * @param int 	$crs_id
     *
     * @return int 	$template_id
     */
    protected function getTemplateIdOf($crs_id)
    {
        $g_db = $this->getDatabase();

        $query = "SELECT source_id FROM copy_mappings WHERE obj_id = " . $g_db->quote($crs_id, "integer");
        $res = $g_db->query($query);
        $row = $g_db->fetchAssoc($res);

        return $row["source_id"];
    }

    /**
     * Check the template course id is course with copy settings below
     *
     * @param int 	$crs_id
     *
     * @return bool
     */
    protected function isTemplateCourse($crs_id)
    {
        if (!\ilPluginAdmin::isPluginActive("xcps")) {
            return false;
        }

        $g_db = $this->getDatabase();
        $query = "SELECT COUNT('obj_id') AS cnt FROM xcps_tpl_crs WHERE crs_id = " . $g_db->quote($crs_id, "integer");
        $res = $g_db->query($query);
        $row = $g_db->fetchAssoc($res);

        return (int) $row["cnt"] > 0;
    }

    /**
     * Checks user has successful completed the course
     *
     * @param int 	$crs_id
     * @param int 	$usr_id
     *
     * @return bool
     */
    protected function courseSuccessfulCompleted($crs_id, $usr_id)
    {
        require_once("Services/Tracking/classes/class.ilLPStatus.php");
        return \ilLPStatus::_lookupStatus($crs_id, $usr_id) == \ilLPStatus::LP_STATUS_COMPLETED_NUM;
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
     * @param \ilObjCourse[] 	$booked_courses
     *
     * @return string
     */
    protected function getDuplicateCourseMessage(array $booked_courses)
    {
        $tpl = new \ilTemplate("tpl.duplicate_courses.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities");

        $tpl->setVariable("TH_CRS_TITLE", "Kurs");
        $tpl->setVariable("TH_CRS_DATE", "Datum");

        if (count($booked_courses) > 0) {
            $tpl->setCurrentBlock("header");
            $tpl->setVariable("DUPLICATE_TRAININGS", $this->txt("duplicate_trainings"));
            $tpl->parseCurrentBlock();

            $this->fillTemplateWithCourseInfos($tpl, $booked_courses);
        }

        return $tpl->get();
    }

    protected function fillTemplateWithCourseInfos(\ilTemplate $tpl, $courses)
    {
        foreach ($courses as $course) {
            $tpl->setCurrentBlock("crs");
            $tpl->setVariable("CRS_TITLE", $course->getTitle());

            $start_date = $this->formatDate($course->getCourseStart());
            $end_date = $this->formatDate($course->getCourseEnd());

            if ($start_date == $end_date) {
                $dates[] = $start_date;
            } else {
                $dates[] = $start_date . " - " . $end_date;
            }
            $tpl->setVariable("CRS_PERIOD_DATE", join("<br />", $dates));

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
