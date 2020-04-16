<?php

declare(strict_types=1);

namespace CaT\Plugins\BookingApprovals\Approvals;

use CaT\Plugins\BookingApprovals\Utils\CourseInformation;

/**
 * Facade for encapsulate CourseInfo, Approval, BookingRequest and User objects.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-trining.de>
 */
class ApprovalsFacade
{
    const APPROVAL_OVERVIEW_TITLE = "title";
    const APPROVAL_OVERVIEW_DATE = "date";
    const APPROVAL_OVERVIEW_VENUE = "venue";
    const APPROVAL_OVERVIEW_TYPE = "type";
    const APPROVAL_OVERVIEW_CONTENT = "content";
    const APPROVAL_OVERVIEW_PROVIDER = "provider";
    const APPROVAL_OVERVIEW_GOALS = "goals";
    const APPROVAL_OVERVIEW_FEE = "fee";
    const APPROVAL_OVERVIEW_IDD = "idd";

    /**
     * @var CourseInfo
     */
    protected $course_info;

    /**
     * @var Approval
     */
    protected $approval;

    /**
     * @var BookingRequest
     */
    protected $booking_request;

    /**
     * @var \ilObjUser
     */
    protected $user;

    /**
     * @var bool
     */
    protected $may_act;

    /**
     * @var int | null
     */
    protected $next_id;

    /**
     * @var arrray
     */
    protected $course_values;

    public function __construct(
        CourseInformation $course_info,
        array $approvals,
        BookingRequest $booking_request,
        \ilObjUser $user,
        bool $may_act,
        $next_id
    ) {
        $this->course_info = $course_info;
        $this->approvals = $approvals;
        $this->booking_request = $booking_request;
        $this->user = $user;
        $this->may_act = $may_act;
        $this->next_id = $next_id;

        $this->course_values = array();
    }

    public function loadCourseValues()
    {
        if (empty($this->course_values)) {
            foreach ($this->course_info->get() as $value) {
                $this->course_values[$value->getLabel()] = $value->getValue();
            }
        }
    }

    public function getValueForLabel(string $label) : string
    {
        $this->loadCourseValues();

        if (array_key_exists($label, $this->course_values)) {
            return $this->course_values[$label];
        }
        return "-";
    }

    public function mayAct()
    {
        return $this->may_act;
    }

    public function getApprovalIdForAction()
    {
        return $this->next_id;
    }

    public function getCourseRefId()
    {
        return $this->booking_request->getCourseRefId();
    }

    public function getBookingRequestId()
    {
        return $this->booking_request->getId();
    }

    public function getApprovalPosition() : int
    {
        return $this->approval->getApprovalPosition();
    }

    public function getApprovals() : array
    {
        return $this->approvals;
    }

    public function getLastname()
    {
        return $this->user->getLastname();
    }

    public function getFirstname()
    {
        return $this->user->getFirstname();
    }

    public function getEmail()
    {
        return $this->user->getEmail();
    }

    public function getCourseTitle()
    {
        return $this->getValueForLabel(self::APPROVAL_OVERVIEW_TITLE);
    }

    public function getCourseDate()
    {
        return $this->getValueForLabel(self::APPROVAL_OVERVIEW_DATE);
    }

    public function getCourseType()
    {
        return $this->getValueForLabel(self::APPROVAL_OVERVIEW_TYPE);
    }

    public function getCourseProvider()
    {
        return $this->getValueForLabel(self::APPROVAL_OVERVIEW_PROVIDER);
    }

    public function getCourseVenue()
    {
        return $this->getValueForLabel(self::APPROVAL_OVERVIEW_VENUE);
    }

    public function getCourseContent()
    {
        return $this->getValueForLabel(self::APPROVAL_OVERVIEW_CONTENT);
    }

    public function getCourseGoals()
    {
        return $this->getValueForLabel(self::APPROVAL_OVERVIEW_GOALS);
    }

    public function getCourseParticipantFee()
    {
        return $this->getValueForLabel(self::APPROVAL_OVERVIEW_FEE);
    }

    public function getCourseIddTimeUnits()
    {
        return $this->getValueForLabel(self::APPROVAL_OVERVIEW_IDD);
    }
}
