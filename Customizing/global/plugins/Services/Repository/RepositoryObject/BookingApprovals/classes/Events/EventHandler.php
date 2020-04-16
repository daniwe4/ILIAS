<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingApprovals\Events;

use CaT\Plugins\BookingApprovals\Approvals;
use CaT\Plugins\BookingApprovals\Player;
use CaT\Plugins\BookingApprovals\Mailing;
use CaT\Plugins\BookingApprovals\Mailing\MailTemplateConstants as MailTemplates;
use CaT\Plugins\BookingApprovals\Utils;
use ILIAS\TMS\Mailing\TMSMailClerk;
use ILIAS\TMS\Booking;

/**
 * Digest and handle events.
 */
class EventHandler
{
    /**
     * @var Approvals\ApprovalDB
     */
    protected $db;

    /**
     * @var Utils\OrguUtils
     */
    protected $orgu_utils;

    /**
     * @var Player\StepsProcessorFactory
     */
    protected $processor_factory;

    /**
     * @var Mailing\MailFactory
     */
    protected $mail_factory;

    /**
     * @var TMSMailClerk
     */
    protected $mailer;

    /**
     * @var Utils\CourseUtils
     */
    protected $course_utils;

    /**
     * @var Events
     */
    protected $events;

    public function __construct(
        Approvals\ApprovalDB $db,
        Utils\OrguUtils $orgu_utils,
        Player\StepsProcessorFactory $processor_factory,
        Mailing\MailFactory $mail_factory,
        TMSMailClerk $mailer,
        Utils\CourseUtils $course_utils,
        Events $events
    ) {
        $this->db = $db;
        $this->orgu_utils = $orgu_utils;
        $this->processor_factory = $processor_factory;
        $this->mail_factory = $mail_factory;
        $this->mailer = $mailer;
        $this->course_utils = $course_utils;
        $this->events = $events;
    }

    public function handle(string $event, array $parameter)
    {
        //validate and get objects
        switch ($event) {
            case Events::USER_REQUESTED_COURSEBOOKING:
            case Events::REQUEST_APPROVED:
            case Events::REQUEST_DECLINED:
                $param = 'booking_request_id';
                if (!array_key_exists($param, $parameter)) {
                    throw new \InvalidArgumentException("The event $event must give the request's id.", 1);
                }
                $booking_request_id = $parameter[$param];
                $booking_request = array_pop($this->db->getBookingRequests([$booking_request_id]));
                break;
            case Events::APPROVAL_APPROVED:
            case Events::APPROVAL_DECLINED:
                $param = 'approval_id';
                if (!array_key_exists($param, $parameter)) {
                    throw new \InvalidArgumentException("The event $event must give the approvals's id.", 1);
                }
                $approval_id = $parameter[$param];
                $approval = array_pop($this->db->getApprovals([$approval_id]));
                $booking_request_id = $approval->getBookingRequestId();
                $booking_request = array_pop($this->db->getBookingRequests([$booking_request_id]));
                break;
            case Events::REQUEST_OUTDATED: //2do
                break;

        }

        //handle
        switch ($event) {
            case Events::USER_REQUESTED_COURSEBOOKING:
                $this->sendMailToNextApprovingUsers($event, $booking_request);
                break;
            case Events::REQUEST_APPROVED:
                $this->bookUserToCourse($booking_request);
                $this->sendMailToUserRequestApproved($event, $booking_request);
                break;
            case Events::REQUEST_DECLINED:
                $this->sendMailToUserRequestDeclined($event, $booking_request);
                break;
            case Events::APPROVAL_APPROVED:
                $this->sendMailToNextApprovingUsers($event, $booking_request);
                break;
            case Events::APPROVAL_DECLINED:
                break;
            case Events::REQUEST_OUTDATED:
                $this->sendMailToUserRequestObsolete($event, $booking_request);
                //2do: update history: user/crs to approval_outdated
                break;
        }
    }

    /**
     * Try to process booking-steps with stored data.
     */
    protected function bookUserToCourse(Approvals\BookingRequest $booking_request)
    {
        $processor = $this->processor_factory->processor($booking_request);
        $result = $processor->process();
        switch ($result) {
            case Player\StepsProcessor::ERROR_DIFFERENT_AMOUNT_OF_STEPS:
            case Player\StepsProcessor::ERROR_PROCESSING_STEPS:
                $this->sendErrorMailToCourseAdmin($booking_request);
                break;
            case Player\StepsProcessor::ERROR_ALL_OK:
                $this->events->fireEventBookingSuccess($booking_request);
                break;
            case Player\StepsProcessor::ERROR_USER_ALREADY_BOOKED:
                //do nothing.
                break;
        }
    }

    /**
     * Inform possibly approvers about open requests.
     * This is due after the creation of a BookingRequest
     * and every time an Approval (other than the last) is approved.
     */
    protected function sendMailToNextApprovingUsers($event, Approvals\BookingRequest $booking_request)
    {
        $template = MailTemplates::MAILING_TEMPLATE_INFORM_APPROVERS;
        $crs_ref_id = $booking_request->getCourseRefId();
        $usr_id = $booking_request->getUserId();
        $recipients = [];
        //get Approvals for BookingRequest
        $approvals = $this->db->getApprovalsForRequest($booking_request->getId());
        //only open ones are interesting
        $open_approvals = array_filter(
            $approvals,
            function ($approval) {
                return $approval->isOpen();
            }
        );
        //nothing to do, if they are all closed:
        if (count($open_approvals) === 0) {
            return;
        }
        //otherwise, get next open one:
        $approval = array_shift($open_approvals);
        $position = $approval->getApprovalPosition();
        $recipients = array_unique(
            $this->orgu_utils->getNextHigherUsersWithPositionForUser(
                $position,
                $usr_id
            )
        );
        //get mails and send them
        if (count($recipients) > 0) {
            $mails = $this->mail_factory->getMails($template, $recipients, $crs_ref_id, $usr_id);
            $this->mailer->process($mails, $event);
        }
    }

    /**
     * Inform the user, that her request was approved.
     */
    protected function sendMailToUserRequestApproved(string $event, Approvals\BookingRequest $booking_request)
    {
        $template = MailTemplates::MAILING_TEMPLATE_REQUEST_APPROVED;
        $crs_ref_id = $booking_request->getCourseRefId();
        $usr_id = $booking_request->getUserId();

        $recipients = $this->getRecipients($booking_request);
        $mails = $this->mail_factory->getMails($template, $recipients, $crs_ref_id, $usr_id);
        $this->mailer->process($mails, $event);
    }

    /**
     * Inform the user, that her request was declined.
     */
    protected function sendMailToUserRequestDeclined(string $event, Approvals\BookingRequest $booking_request)
    {
        $template = MailTemplates::MAILING_TEMPLATE_REQUEST_DECLINED;
        $crs_ref_id = $booking_request->getCourseRefId();
        $usr_id = $booking_request->getUserId();

        $recipients = $this->getRecipients($booking_request);
        $mails = $this->mail_factory->getMails($template, $recipients, $crs_ref_id, $usr_id);
        $this->mailer->process($mails, $event);
    }

    /**
     * Inform the user, that the course concerning her request
     * is no longer bookable.
     */
    protected function sendMailToUserRequestObsolete(string $event, Approvals\BookingRequest $booking_request)
    {
        $template = MailTemplates::MAILING_TEMPLATE_REQUEST_OBSOLETE;
        $crs_ref_id = $booking_request->getCourseRefId();
        $usr_id = $booking_request->getUserId();

        $recipients = $this->getRecipients($booking_request);
        $mails = $this->mail_factory->getMails($template, $recipients, $crs_ref_id, $usr_id);
        //2do: send mail
    }

    /**
     * Inform the user, that the course concerning her request
     * is no longer bookable.
     */
    protected function sendErrorMailToCourseAdmin(Approvals\BookingRequest $booking_request)
    {
        $event = Events::REQUEST_PROCESSING_FAILED;
        $template = MailTemplates::MAILING_TEMPLATE_REQUEST_PROCESSING_ERROR;
        $crs_ref_id = $booking_request->getCourseRefId();
        $usr_id = $booking_request->getUserId();
        $recipients = array_map(
            function ($admin_id) {
                return (int) $admin_id;
            },
            $this->course_utils->getCourseAdmins($crs_ref_id)
        );
        $mails = $this->mail_factory->getMails($template, $recipients, $crs_ref_id, $usr_id);
        $this->mailer->process($mails, $event);
    }

    protected function getRecipients(Approvals\BookingRequest $booking_request) : array
    {
        return [$booking_request->getActingUserId()];
    }
}
