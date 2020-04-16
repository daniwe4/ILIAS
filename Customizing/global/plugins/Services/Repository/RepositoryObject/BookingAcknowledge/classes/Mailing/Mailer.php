<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingAcknowledge\Mailing;

use \ILIAS\TMS\Mailing\MailContext;
use \ILIAS\TMS\Mailing\TMSMail;
use \ILIAS\TMS\Mailing\TMSMailClerk;

/**
 * Build TMS-ready mails, with context and all, and send them
 */
class Mailer
{
    const MAILING_TEMPLATE_DISAPPROVED_BOOKING = 'BR06';
    const LOGGING_EVENT_STRING = 'booking_disapproval';

    /**
     * @var TMSMailClerk
     */
    protected $mailer;

    public function __construct(TMSMailClerk $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param int[] 	$recipients 	usr_ids of recipients
     * @param int $crs_ref_id
     * @param int $usr_id
     *
     * @return TMSMail[]
     */
    public function getMails(
        array $recipients,
        int $crs_ref_id,
        int $usr_id
    ) : array {
        $contexts = array(
            $this->getIliasContext(),
            $this->getCourseContext($crs_ref_id),
            $this->getUserContext($usr_id)
        );
        $attachments = $this->getAttachments();

        $mails = [];
        foreach ($recipients as $recipient_id) {
            $recipient = $this->getRecipient($recipient_id);

            $mails[] = new TMSMail(
                $recipient,
                self::MAILING_TEMPLATE_DISAPPROVED_BOOKING,
                $contexts,
                $attachments
            );
        }
        return $mails;
    }

    public function sendMails(array $mails)
    {
        $event = self::LOGGING_EVENT_STRING;
        $this->mailer->process($mails, $event);
    }

    protected function getRecipient(int $usr_id) : \ilTMSMailRecipient
    {
        return new \ilTMSMailRecipient($usr_id);
    }

    protected function getAttachments() : \ilTMSMailAttachments
    {
        return new \ilTMSMailAttachments();
    }

    protected function getUserContext(int $usr_id) : \ilTMSMailContextUser
    {
        return new \ilTMSMailContextUser($usr_id);
    }

    protected function getIliasContext() : \ilTMSMailContextILIAS
    {
        return new \ilTMSMailContextILIAS();
    }

    protected function getCourseContext(int $crs_ref_id) : \ilTMSMailContextCourse
    {
        return new \ilTMSMailContextCourse($crs_ref_id);
    }
}
