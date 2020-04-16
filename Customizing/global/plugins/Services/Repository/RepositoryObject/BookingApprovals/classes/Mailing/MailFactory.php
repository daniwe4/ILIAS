<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingApprovals\Mailing;

use \ILIAS\TMS\Mailing\MailContext;
use \ILIAS\TMS\Mailing\TMSMail;

require_once('./Services/TMS/Mailing/classes/class.ilTMSMailRecipient.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailAttachments.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextUser.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextTargetUser.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextILIAS.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextCourse.php');

/**
 * Build TMS-ready mails, with context and all.
 */
class MailFactory
{
    /**
     * @param string	$template_ident
     * @param int[] 	$recipients 	usr_ids of recipients
     * @param int	$crs_ref_id
     * @param int|null	$target_usr_id
     *
     * @return TMSMail[]
     */
    public function getMails(
        string $template_ident,
        array $recipients,
        int $crs_ref_id,
        int $target_usr_id = null
    ) : array {
        $contexts = array(
            $this->getCourseContext($crs_ref_id),
            $this->getIliasContext()
        );
        if (!is_null($target_usr_id)) {
            $contexts[] = $this->getTargetUserContext($target_usr_id);
        }

        $attachments = $this->getAttachments();

        $mails = [];
        foreach ($recipients as $recipient_id) {
            $recipient = $this->getRecipient($recipient_id);
            $user_context = $this->getUserContext($recipient_id);

            $mails[] = new TMSMail(
                $recipient,
                $template_ident,
                array_merge($contexts, [$user_context]),
                $attachments
            );
        }
        return $mails;
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

    protected function getTargetUserContext(int $usr_id) : \ilTMSMailContextTargetUser
    {
        return new \ilTMSMailContextTargetUser($usr_id);
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
