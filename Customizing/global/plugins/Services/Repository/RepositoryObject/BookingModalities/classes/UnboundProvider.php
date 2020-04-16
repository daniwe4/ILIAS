<?php

namespace CaT\Plugins\BookingModalities;

use \CaT\Ente\ILIAS\SeparatedUnboundProvider;
use \CaT\Ente\ILIAS\Entity;
use \ILIAS\TMS\Booking;
use \ILIAS\TMS\CourseInfo;
use \ILIAS\TMS\ActionBuilder;
use \ILIAS\TMS\CourseInfoImpl;
use \ILIAS\TMS\Mailing\MailContext;

class UnboundProvider extends SeparatedUnboundProvider
{
    /**
     * @var \ilObjUser
     */
    protected $g_user;

    /**
     * @inheritdocs
     */
    public function componentTypes()
    {
        return [
            Booking\SelfBookingStep::class,
            Booking\SuperiorBookingStep::class,
            Booking\SelfBookingWithApprovalsStep::class,
            Booking\SuperiorBookingWithApprovalsStep::class,
            CourseInfo::class,
            ActionBuilder::class,
            MailContext::class
        ];
    }

    /**
     * Build the component(s) of the given type for the given object.
     *
     * @param   string    $component_type
     * @param   Entity    $provider
     * @return  Component[]
     */
    public function buildComponentsOf($component_type, Entity $entity)
    {
        $owner = $this->owner();
        $booking_actions = $owner->getBookingActions();
        $this->txt = $owner->txtClosure();
        global $DIC;
        $this->g_user = $DIC->user();
        $this->g_ctrl = $DIC->ctrl();
        $modalities_doc = $owner->getActions()->getModalitiesDocForUser((int) $this->g_user->getId());

        if ($this->checkPermissionsOn((int) $owner->getRefId())) {
            if ($component_type === Booking\SelfBookingStep::class) {
                return $this->getSelfBookingSteps($entity, $booking_actions, $modalities_doc, $owner, $this->g_user);
            }

            if ($component_type === Booking\SuperiorBookingStep::class) {
                return $this->getSuperiorBookingSteps($entity, $booking_actions, $modalities_doc, $owner, $this->g_user);
            }

            if ($component_type === Booking\SelfBookingWithApprovalsStep::class) {
                return $this->getSelfBookingWithApprovalsSteps(
                    $entity,
                    $booking_actions,
                    $modalities_doc,
                    $owner,
                    $this->g_user
                );
            }

            if ($component_type === Booking\SuperiorBookingWithApprovalsStep::class) {
                return $this->getSuperiorBookingWithApprovalsSteps(
                    $entity,
                    $booking_actions,
                    $modalities_doc,
                    $owner,
                    $this->g_user
                );
            }

            if ($component_type === ActionBuilder::class) {
                return [
                    $this->getActionBuilder($entity, $owner)
                ];
            }

            if ($component_type === MailContext::class) {
                return [new Mailing\MailContextBookingModalities($entity, $this->owner())];
            }

            if ($component_type === CourseInfo::class) {
                $ret = array();
                $course = $entity->object();

                $crs_start = $course->getCourseStart();
                $crs_member = $course->getMembersObject()->getCountMembers();

                $max_member = $owner->getMember()->getMax();
                $with_waiting_list = $owner->getWaitinglist()->getModus() != "no_waitinglist";
                $max_waiting = $owner->getWaitinglist()->getMax();
                $booking_deadline = $owner->getBooking()->getDeadline();
                $booking_beginning = $owner->getBooking()->getBeginning();
                $booking_modus = $owner->getBooking()->getModus();
                $storno_deadline = $owner->getStorno()->getDeadline();
                $storno_hard_deadline = $owner->getStorno()->getHardDeadline();
                $storno_modus = $owner->getStorno()->getModus();

                require_once("Services/Membership/classes/class.ilWaitingList.php");
                $crs_waiting_member = \ilWaitingList::lookupListSize($course->getId());

                // Temporary fix for TMS-568
                if ($booking_modus !== null) {
                    $ret = $this->getCourseInfoForFreePlaces(
                        $ret,
                        $entity,
                        $max_member,
                        $crs_member,
                        $crs_waiting_member,
                        $max_waiting,
                        $with_waiting_list,
                        $crs_start,
                        $booking_deadline
                    );

                    $ret = $this->getCourseInfoForBookingInfos(
                        $ret,
                        $entity,
                        $crs_member,
                        $max_member,
                        $crs_waiting_member,
                        $max_waiting,
                        $with_waiting_list,
                        $crs_start,
                        $booking_beginning,
                        $booking_deadline,
                        $booking_modus
                    );
                    if ($crs_start !== null) {
                        $ret = $this->getCourseInfoForBookingDeadline($ret, $entity, $crs_start, $booking_deadline);
                    }
                }
                if ($crs_start !== null) {
                    $ret = $this->getCourseInfoForCancelDeadlines($ret, $entity, $crs_start, $storno_deadline, $storno_hard_deadline, $storno_modus);
                }

                $ret = $this->getCourseInfoGeneralBookingLink($ret, $entity, $this->g_ctrl, $owner);

                return $ret;
            }

            throw new \InvalidArgumentException("Unexpected component type '$component_type'");
        }
        return array();
    }

    protected function getActionBuilder($entity, $owner) : ActionBuilder
    {
        return new CourseActions\ActionBuilder($entity, $owner, $this->g_user);
    }

    /**
     * Returns all booking steps for my self
     *
     * @return Step[]
     */
    protected function getSelfBookingSteps($entity, $booking_actions, $modalities_doc, $owner, $acting_user)
    {
        return [
                new Steps\SelfParallelCourseStep($entity, $this->txt, $owner, $acting_user),
                new Steps\SelfBookingStep($entity, $this->txt, $booking_actions, $modalities_doc, $owner, $acting_user),
                new Steps\SelfBookWaitingStep($entity, $this->txt, $booking_actions, $modalities_doc, $owner, $acting_user),
                new Steps\SelfCancelStep($entity, $this->txt, $booking_actions, $owner, $acting_user, $modalities_doc),
                new Steps\SelfCancelWaitingStep($entity, $this->txt, $booking_actions, $owner, $acting_user, $modalities_doc),
                new Steps\SelfDuplicateCourseStep($entity, $this->txt, $owner, $acting_user),
                new Steps\SelfHardCancelStep($entity, $this->txt, $booking_actions, $owner, $acting_user, $modalities_doc)
            ];
    }

    /**
     * Returns all booking steps for my self
     *
     * @return Step[]
     */
    protected function getSelfBookingWithApprovalsSteps(
        $entity,
        $booking_actions,
        $modalities_doc,
        $owner,
        $acting_user
    ) {
        return [
                new Steps\SelfParallelCourseStep($entity, $this->txt, $owner, $acting_user),
                new Steps\SelfDuplicateCourseStep($entity, $this->txt, $owner, $acting_user),
                new Steps\SelfBookingWithApprovalsStep($entity, $this->txt, $booking_actions, $owner, $acting_user, $this->g_user),
                new Steps\SelfBookWaitingWithApprovalsStep($entity, $this->txt, $booking_actions, $owner, $acting_user, $this->g_user)
            ];
    }

    /**
     * Returns all booking steps for superiors
     *
     * @return Step[]
     */
    protected function getSuperiorBookingSteps($entity, $booking_actions, $modalities_doc, $owner, $acting_user)
    {
        return [
                new Steps\SuperiorParallelCourseStep($entity, $this->txt, $owner, $acting_user),
                new Steps\SuperiorBookingStep($entity, $this->txt, $booking_actions, $modalities_doc, $owner, $acting_user),
                new Steps\SuperiorBookWaitingStep($entity, $this->txt, $booking_actions, $modalities_doc, $owner, $acting_user),
                new Steps\SuperiorCancelStep($entity, $this->txt, $booking_actions, $owner, $acting_user, $modalities_doc),
                new Steps\SuperiorCancelWaitingStep($entity, $this->txt, $booking_actions, $owner, $acting_user, $modalities_doc),
                new Steps\SuperiorDuplicateCourseStep($entity, $this->txt, $owner, $acting_user),
                new Steps\SuperiorHardCancelStep($entity, $this->txt, $booking_actions, $owner, $acting_user, $modalities_doc)
            ];
    }

    /**
     * Returns all booking steps for superiors
     *
     * @return Step[]
     */
    protected function getSuperiorBookingWithApprovalsSteps(
        $entity,
        $booking_actions,
        $modalities_doc,
        $owner,
        $acting_user
    ) {
        return [
                new Steps\SuperiorParallelCourseStep($entity, $this->txt, $owner, $acting_user),
                new Steps\SuperiorDuplicateCourseStep($entity, $this->txt, $owner, $acting_user),
                new Steps\SuperiorBookingWithApprovalsStep($entity, $this->txt, $booking_actions, $owner, $acting_user, $this->g_user),
                new Steps\SuperiorBookWaitingWithApprovalsStep($entity, $this->txt, $booking_actions, $owner, $acting_user, $this->g_user)
            ];
    }

    /**
     * Get course infos for free places
     *
     * @param CourseInfo[] 	$ret
     * @param Entity 	$entity
     * @param int 	$max_member
     * @param int 	$crs_member
     *
     * @return CourseInfo[]
     */
    protected function getCourseInfoForFreePlaces(
        array $ret,
        Entity $entity,
        $max_member,
        $crs_member,
        $crs_waiting_member,
        $max_waiting,
        $with_waiting_list,
        $crs_start,
        $booking_deadline
    ) {
        $free = $max_member - $crs_member;
        if ($max_member === null) {
            $free = $this->txt("infinity_places");
        } elseif ($max_member !== null && $free <= 0) {
            $free = $this->txt(
                $this->getStatusOfBooking($crs_member, $max_member, $crs_waiting_member, $max_waiting, $with_waiting_list, $booking_deadline, $crs_start)
            );
        }

        if (is_int($free)) {
            $ret[] = $this->buildDetailInfo(
                $entity,
                "",
                $this->txt("free") . " " . $free,
                550,
                [CourseInfo::CONTEXT_SEARCH_SHORT_INFO]
            );

            $ret[] = $this->buildDetailInfo(
                $entity,
                $this->txt("free") . ":",
                $free,
                400,
                [CourseInfo::CONTEXT_SEARCH_FURTHER_INFO]
            );
        } else {
            if ($this->bookingPeriodPassed($booking_deadline, $crs_start)
                && $crs_member < $max_member
            ) {
                $ret[] = $this->buildDetailInfo(
                    $entity,
                    "",
                    ucfirst($this->txt("request_book")),
                    550,
                    [CourseInfo::CONTEXT_SEARCH_SHORT_INFO]
                );
            } else {
                $ret[] = $this->buildDetailInfo(
                    $entity,
                    "",
                    ucfirst($free),
                    550,
                    [CourseInfo::CONTEXT_SEARCH_SHORT_INFO]
                );
            }
        }

        $ret[] = $this->buildDetailInfo(
            $entity,
            $this->txt("free"),
            $free,
            1800,
            [CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO]
        );

        return $ret;
    }

    /**
     * Get course info for booking options and booking button
     *
     * @param CourseInfo[] 	$ret
     * @param Entity 	$entity
     * @param int 	$crs_member
     * @param int 	$max_member
     * @param int 	$crs_waiting_member
     * @param int 	$max_waiting
     * @param bool 	$with_waiting_list
     * @param int 	$crs_start
     * @param int 	$booking_beginning,
     * @param int 	$storno_deadline
     * @param string 	$booking_modus
     *
     * @return CourseInfo[]
     */
    protected function getCourseInfoForBookingInfos(
        array $ret,
        Entity $entity,
        $crs_member,
        $max_member,
        $crs_waiting_member,
        $max_waiting,
        $with_waiting_list,
        $crs_start,
        $booking_beginning,
        $booking_deadline,
        $booking_modus
    ) {
        $status = $this->getStatusOfBooking($crs_member, $max_member, $crs_waiting_member, $max_waiting, $with_waiting_list, $booking_deadline, $crs_start);

        if ($status == "bookable") {
            $ret[] = $this->buildDetailInfo(
                $entity,
                "",
                1,
                100,
                [CourseInfo::CONTEXT_IS_BOOKABLE]
            );
        }

        $ret[] = $this->buildDetailInfo(
            $entity,
            $this->txt("book_status") . ":",
            $this->txt($status),
            700,
            [CourseInfo::CONTEXT_SEARCH_FURTHER_INFO]
        );

        return $ret;
    }

    /**
     * Get course info for booking deadline
     *
     * @param CourseInfo[] 	$ret
     * @param Entity 	$entity
     * @param ilDate 	$crs_start
     * @param int 	$booking_deadline
     *
     * @return CourseInfo[]
     */
    protected function getCourseInfoForBookingDeadline(array $ret, Entity $entity, \ilDateTime $crs_start, $booking_deadline)
    {
        $booking_end_date = clone $crs_start;

        if ($booking_deadline !== null && $booking_deadline > 0) {
            require_once("Services/Calendar/classes/class.ilDateTime.php");
            $booking_end_date->increment(\ilDateTime::DAY, -1 * $booking_deadline);
        }

        $ret[] = $this->buildDetailInfo(
            $entity,
            $this->txt("booking_until") . ":",
            $this->formatDate($booking_end_date),
            600,
            [CourseInfo::CONTEXT_SEARCH_FURTHER_INFO]
        );

        $ret[] = $this->buildDetailInfo(
            $entity,
            $this->txt("booking_until"),
            $this->formatDate($booking_end_date),
            1600,
            [CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO]
        );

        return $ret;
    }

    /**
     * Get course info for cancel deadline
     *
     * @param CourseInfo[] 	$ret
     * @param Entity 	$entity
     * @param ilDate 	$crs_start
     * @param int 	$storno_deadline
     * @param string 	$storno_modus
     *
     * @return CourseInfo[]
     */
    protected function getCourseInfoForCancelDeadlines(
        array $ret,
        Entity $entity,
        \ilDateTime $crs_start,
        $storno_deadline,
        $storno_hard_deadline,
        $storno_modus
    ) {
        $storno_end_date = clone $crs_start;
        if ($storno_deadline !== null && $storno_deadline > 0) {
            require_once("Services/Calendar/classes/class.ilDateTime.php");
            $storno_end_date->increment(\ilDateTime::DAY, -1 * $storno_deadline);
        }

        $hard_storno_end_date = clone $crs_start;
        if ($storno_hard_deadline !== null && $storno_hard_deadline > 0) {
            require_once("Services/Calendar/classes/class.ilDateTime.php");
            $hard_storno_end_date->increment(\ilDateTime::DAY, -1 * $storno_hard_deadline);
        }

        require_once(__DIR__ . "/Settings/class.ilBookingModalitiesGUI.php");
        if ($storno_modus !== null && $storno_modus !== \ilBookingModalitiesGUI::NO_CANCEL) {
            $ret[] = $this->buildDetailInfo(
                $entity,
                $this->txt("storno_until") . ":",
                $this->formatDate($storno_end_date),
                500,
                [
                    CourseInfo::CONTEXT_USER_BOOKING_FURTHER_INFO,
                    CourseInfo::CONTEXT_USER_BOOKING_SUPERIOR_FURTHER_INFO,
                    CourseInfo::CONTEXT_ASSIGNED_TRAINING_FURTHER_INFO,
                    CourseInfo::CONTEXT_ADMIN_OVERVIEW_FURTHER_INFO
                ]
            );
            $ret[] = $this->buildDetailInfo(
                $entity,
                $this->txt("storno_until"),
                $this->formatDate($storno_end_date),
                1700,
                [CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO]
            );
        }

        return $ret;
    }

    /**
     * Get course info for booking link
     *
     * @param CourseInfo[] 	$ret
     * @param Entity 	$entity
     * @param \ilCtrl 	$ctrl
     *
     * @return CourseInfo[]
     */
    protected function getCourseInfoGeneralBookingLink(array $ret, Entity $entity, \ilCtrl $ctrl, $owner)
    {
        $course = $entity->object();
        require_once("Services/Link/classes/class.ilLink.php");
        $link = \ilLink::_getStaticLink($owner->getRefId(), 'xbkm', true, "_crs" . $course->getRefId());

        $ret[] = $this->buildDetailInfo(
            $entity,
            $this->txt("link_to_user_booking"),
            $link,
            20000,
            [
                CourseInfo::CONTEXT_ADMIN_OVERVIEW_DETAIL_INFO,
                CourseInfo::CONTEXT_ASSIGNED_TRAINING_DETAIL_INFO,
                CourseInfo::CONTEXT_GENERAL_BOOKING_LINK
            ]
        );

        return $ret;
    }


    /**
     * Is booking period passed
     *
     * @param int 	$booking_end
     * @param ilDateTime | null 	$crs_start
     *
     * @return bool
     */
    protected function bookingPeriodPassed($booking_end, \ilDateTime $crs_start = null)
    {
        if ($crs_start == null) {
            return false;
        }

        $today_string = date("Y-m-d");

        $booking_end_date = clone $crs_start;
        $booking_end_date->increment(\ilDateTime::DAY, -1 * $booking_end);
        $end_string = $booking_end_date->get(IL_CAL_DATE);

        if ($end_string >= $today_string) {
            return false;
        }

        return true;
    }

    /**
     * Createa a courseInfoImpl object for detailed infos
     *
     * @param Entity 	$entity
     * @param string 	$lable
     * @param string 	$value
     * @param int 	$step
     * @param string 	$context
     *
     * @return CourseInfoImpl
     */
    protected function buildDetailInfo(Entity $entity, $label, $value, $step, array $context)
    {
        return new CourseInfoImpl(
            $entity,
            $label,
            $value,
            "",
            $step,
            $context
        );
    }

    /**
     * Form date.
     *
     * @param ilDateTime 	$dat
     * @param bool 	$use_time
     *
     * @return string
     */
    protected function formatDate(\ilDateTime $date)
    {
        require_once("Services/Calendar/classes/class.ilCalendarUtil.php");
        $out_format = \ilCalendarUtil::getUserDateFormat(false, true);
        $ret = $date->get(IL_CAL_FKT_DATE, $out_format, $this->g_user->getTimeZone());
        if (substr($ret, -5) === ':0000') {
            $ret = substr($ret, 0, -5);
        }

        return $ret;
    }

    /**
     * Get status of booking for this course
     *
     * @param int 	$crs_member
     * @param int 	$max_member
     * @param int 	$crs_waiting_member
     * @param int 	$max_waiting
     * @param bool 	$with_waiting_list
     * @param int 	$booking_deadline
     * @param ilDateTime | null 	$crs_start
     *
     * @return string
     */
    protected function getStatusOfBooking(
        $crs_member,
        $max_member,
        $crs_waiting_member,
        $max_waiting,
        $with_waiting_list,
        $booking_deadline,
        \ilDateTime $crs_start = null
    ) {
        if ($this->bookingPeriodPassed($booking_deadline, $crs_start)
            && $crs_member < $max_member
        ) {
            return "request_book";
        }

        if ($this->bookingPeriodPassed($booking_deadline, $crs_start)) {
            return "period_passed";
        }

        if ($max_member === null) {
            return "bookable";
        }

        if ($crs_member < $max_member) {
            return "bookable";
        }

        if ($with_waiting_list && ($crs_member + $crs_waiting_member) < ($max_member + $max_waiting)) {
            return "bookable_waiting";
        }

        return "overbooked";
    }

    /**
     * Check user has permissions to view and book_by_this on the owner
     *
     * @param int 	$owner_ref_id
     *
     * @return bool
     */
    protected function checkPermissionsOn($owner_ref_id)
    {
        assert('is_int($owner_ref_id)');
        global $DIC;
        $g_access = $DIC->access();

        return $g_access->checkAccess("book_by_this", "", $owner_ref_id);
    }

    /**
     * Is today in booking period of course
     *
     * @param ilDateTime 	$crs_start
     * @param int 	$booking_start
     * @param int 	$booking_end
     *
     * @return bool
     */
    public function isInBookingPeriod(\ilDateTime $crs_start, $booking_start, $booking_end)
    {
        if ($crs_start == null) {
            return true;
        }

        $today_string = date("Y-m-d");

        $booking_start_date = clone $crs_start;
        $booking_start_date->increment(\ilDateTime::DAY, -1 * $booking_start);
        $start_string = $booking_start_date->get(IL_CAL_DATE);

        $booking_end_date = clone $crs_start;
        $booking_end_date->increment(\ilDateTime::DAY, -1 * $booking_end);
        $end_string = $booking_end_date->get(IL_CAL_DATE);

        if ($today_string >= $start_string && $today_string <= $end_string) {
            return true;
        }

        return false;
    }

    /**
     * Parse lang code to text
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt($code)
    {
        $txt = $this->txt;
        return $txt($code);
    }
}
