<?php
namespace CaT\Plugins\CourseMember\Mailing;

use \ILIAS\TMS\Mailing\MailingOccasion;
use \ILIAS\TMS\Mailing\TMSMail;
use \ILIAS\TMS\Mailing\MailContext;
use \ILIAS\TMS\Mailing\CourseSendMailHelper;
use \CaT\Ente\Entity;
use CaT\Ente\ILIAS\ilHandlerObjectHelper;

require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextILIAS.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextCourse.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextUser.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailRecipient.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailAttachments.php');

/**
 * When a memberlist is finalized, members should recieve a mail with their status.
 * This is the base-class for different lp-status.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class LPStatusOccasion implements MailingOccasion
{
    use ilHandlerObjectHelper;
    use CourseSendMailHelper;

    const TEMPLATE_IDENT = '';
    const RELEVANT_LPSTATUS = -1;
    const EVENT_MEMBERLIST_FINALIZED = 'memberlist_finalized';

    protected static $events = array(
        self::EVENT_MEMBERLIST_FINALIZED
    );

    /**
     * @var	Entity
     */
    protected $entity;

    /**
     * @var	\ilObjCourseMember
     */
    protected $owner;

    /**
     * @var	\Closure
     */
    protected $txt;

    public function __construct(Entity $entity, \ilObjCourseMember $owner, callable $txt)
    {
        $this->entity = $entity;
        $this->owner = $owner;
        $this->txt = $txt;
    }

    /**
     * @inheritdoc
     */
    public function listEvents()
    {
        return self::$events;
    }

    /**
     * @inheritdoc
     */
    public function doesProvideMailForEvent($event)
    {
        return false; //this is a base-class and does not provide actual mails.
    }

    /**
     * @inheritdoc
     */
    public function getNextScheduledDate()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getMails($event, $parameter)
    {
        assert('is_string($event)');

        $attachments = $this->getAttachments();
        $template_ident = $this->templateIdent();
        $contexts = array(
            $this->getIliasContext(),
            $this->getCourseContext()
        );
        $contexts = array_merge($contexts, $this->getEnteContexts());

        //get mails for users with certain status
        //relevant is the ILIAS-LP (as int)
        $mails = array();
        $usr_ids = $this->getUsersWithLPStatus(static::RELEVANT_LPSTATUS);

        if ($event === 'manual') {
            $usr_id = $parameter['usr_id'];
            if (in_array($usr_id, $usr_ids)) {
                $usr_ids = [$usr_id];
            } else {
                $usr_name = \ilObjUser::_lookupName($usr_id);
                $msg = sprintf(
                    $this->txt('no_status_for_user'),
                    $usr_name['firstname'],
                    $usr_name['lastname'],
                    $usr_name['login']
                );

                if (!\ilSession::get('failure')) {
                    \ilUtil::sendFailure($msg, 1);
                } else {
                    $pre = \ilSession::get('failure');
                    \ilUtil::sendFailure(
                        implode("<br>", [$pre, $msg]),
                        1
                    );
                }
                $usr_ids = [];
            }
        }

        foreach ($usr_ids as $usr_id) {
            $recipient = $this->getRecipient($usr_id);
            $contexts_user = array_merge($contexts, array($this->getUserContext($usr_id)));
            $mails[] = new TMSMail($recipient, $template_ident, $contexts_user, $attachments);
        }

        return $mails;
    }

    /**
     * Get all user-ids of members with a certain status.
     *
     * @param 	int 	$status
     * @return 	int[] 	usr_ids
     */
    protected function getUsersWithLPStatus($status)
    {
        assert('is_int($status)');

        switch ($status) {
            case \ilLPStatus::LP_STATUS_COMPLETED_NUM:
                return $this->owner->getLPCompleted();
                break;
            case \ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM:
            case \ilLPStatus::LP_STATUS_FAILED_NUM:
                return $this->owner->getLPFailed();
                break;
            default:
                return array();
        }
    }


    /**
     * @param int $usr_id
     * @return ilTMSMailRecipient
     */
    protected function getRecipient($usr_id)
    {
        return new \ilTMSMailRecipient($usr_id);
    }

    /**
     * @return ilTMSMailContextCourse
     */
    protected function getCourseContext()
    {
        return new \ilTMSMailContextCourse($this->getEntityRefId());
    }

    /**
     * @param int $usr_id
     * @return ilTMSMailContextUser
     */
    protected function getUserContext($usr_id)
    {
        return new \ilTMSMailContextUser($usr_id);
    }

    /**
     * @return ilTMSMailContextILIAS
     */
    protected function getIliasContext()
    {
        return new \ilTMSMailContextILIAS();
    }

    /**
     * @inheritdoc
     */
    protected function getAttachments()
    {
        $attachments = new \ilTMSMailAttachments();
        return $attachments;
    }

    /**
     * @return Mailing\MailContext[]
     */
    protected function getEnteContexts()
    {
        $components = $this->getComponentsOfType(MailContext::class);
        return $components;
    }

    /**
     * @inheritdoc
     */
    public function templateIdent()
    {
        return static::TEMPLATE_IDENT;
    }

    /**
     * @inheritdoc
     */
    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    protected function getEntityId()
    {
        return (int) $this->entity()->object()->getId();
    }

    /**
     * @inheritdoc
     */
    protected function getEntityRefId()
    {
        return (int) $this->entity()->object()->getRefId();
    }

    /**
     * @inheritdoc
     */
    public function entity()
    {
        return $this->entity;
    }

    /**
     * @inheritdoc
     */
    protected function txt($id)
    {
        assert('is_string($id)');
        return call_user_func($this->txt, $id);
    }

    /**
     * @inheritdoc
     */
    public function maybeSend()
    {
        if (
            !$this->isCourseMailingInSubTree() ||
            $this->isCopySettingsInSubTree() ||
            !$this->isCourseOnline()
        ) {
            return false;
        }
        return true;
    }
}
