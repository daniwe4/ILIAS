<?php

declare(strict_types=1);

namespace CaT\Plugins\StatusMails\Mailing;

use ILIAS\TMS\Mailing\MailContext;
use ILIAS\TMS\Mailing\TMSMail;
use CaT\Plugins\StatusMails\History\UserActivity;
use CaT\Plugins\StatusMails\Course\CourseFlags;

/**
 * Provide TMSMails.
 * The factory shall build all required contexts,
 * including the armed DynamicContext.
 * @author Nils Haagen    <nils.haagen@concepts-and-training.de>
 */
class MailFactory
{
    /**
     * @var string
     */
    protected $template_ident;

    /**
     * @var DynamicContext
     */
    protected $dynamic_context;

    public function __construct(string $template_ident, MailContext $dynamic_context)
    {
        $this->template_ident = $template_ident;
        $this->dynamic_context = $dynamic_context;
    }

    /**
     * Get clerk-ready TMSMails.
     * @param UserActivity[] $data
     * @param CourseFlags[]  $flags
     */
    public function getMail(int $recipient_id, array $data, array $flags) : TMSMail
    {
        $recipient = $this->getRecipient($recipient_id);
        $contexts = array(
            $this->getUserContext($recipient_id),
            $this->getIliasContext(),
            $this->dynamic_context
                ->withData($data)
                ->withFlags($flags)
        );
        return new TMSMail(
            $recipient,
            $this->template_ident,
            $contexts,
            $this->getAttachments()
        );
    }

    protected function getRecipient(int $usr_id) : \ilTMSMailRecipient
    {
        return new \ilTMSMailRecipient($usr_id);
    }

    protected function getUserContext(int $usr_id) : \ilTMSMailContextUser
    {
        return new \ilTMSMailContextUser($usr_id);
    }

    protected function getIliasContext() : \ilTMSMailContextILIAS
    {
        return new \ilTMSMailContextILIAS();
    }

    protected function getAttachments() : \ilTMSMailAttachments
    {
        return new \ilTMSMailAttachments();
    }
}
