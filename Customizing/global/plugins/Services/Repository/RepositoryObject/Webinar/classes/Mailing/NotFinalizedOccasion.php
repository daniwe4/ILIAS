<?php
namespace CaT\Plugins\Webinar\Mailing;

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
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class NotFinalizedOccasion implements MailingOccasion
{
    use ilHandlerObjectHelper;
    use CourseSendMailHelper;

    const TEMPLATE_IDENT = 'R04';
    const EVENT_MEMBERLIST_NOT_FINALIZED = 'webinar_not_finalized';

    protected static $events = array(
        self::EVENT_MEMBERLIST_NOT_FINALIZED
    );

    /**
     * @var	Entity
     */
    protected $entity;

    /**
     * @var	\ilObjWebinar
     */
    protected $owner;

    /**
     * @var	\Closure
     */
    protected $txt;

    public function __construct(
        Entity $entity,
        \ilObjWebinar $owner,
        callable $txt
    ) {
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
        assert(is_string($event));
        return in_array($event, self::$events);
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
        $usr_id = (int) $parameter['usr_id'];
        $template_ident = $this->templateIdent();
        $recipient = $this->getRecipient($usr_id);
        $contexts = array(
            $this->getIliasContext(),
            $this->getUserContext($usr_id),
            $this->getCourseContext()
        );
        $contexts = array_merge($contexts, $this->getEnteContexts());

        $mails = array(
            new TMSMail($recipient, $template_ident, $contexts, null)
        );
        return $mails;
    }

    /**
     * @param int $usr_id
     * @return \ilTMSMailRecipient
     */
    protected function getRecipient($usr_id)
    {
        return new \ilTMSMailRecipient($usr_id);
    }

    /**
     * @return \ilTMSMailContextCourse
     */
    protected function getCourseContext()
    {
        return new \ilTMSMailContextCourse($this->getEntityRefId());
    }

    /**
     * @param int $usr_id
     * @return \ilTMSMailContextUser
     */
    protected function getUserContext($usr_id)
    {
        return new \ilTMSMailContextUser($usr_id);
    }

    /**
     * @return \ilTMSMailContextILIAS
     */
    protected function getIliasContext()
    {
        return new \ilTMSMailContextILIAS();
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
    protected function txt(string $id)
    {
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
