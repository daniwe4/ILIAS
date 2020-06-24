<?php
namespace CaT\Plugins\BookingModalities\MailingOccasions;

use \ILIAS\TMS\Mailing\MailingOccasion;
use \ILIAS\TMS\Mailing\TMSMail;
use \ILIAS\TMS\Mailing\MailContext;
use \ILIAS\TMS\Mailing\CourseSendMailHelper;
use \CaT\Ente\Entity;
use CaT\Ente\ILIAS\ilHandlerObjectHelper;
use ILIAS\TMS\Mailing\EluceoICalBuilder;

abstract class MailOccasionBase implements MailingOccasion
{
    use ilHandlerObjectHelper;
    use CourseSendMailHelper;

    /**
     * @var	Entity
     */
    protected $entity;

    /**
     * @var callable
     */
    protected $txt;


    protected static $events = array(
        //overwrite this.
    );


    public function __construct(Entity $entity, callable $txt)
    {
        $this->entity = $entity;
        $this->txt = $txt;
    }

    /**
     * @inheritdoc
     */
    public function listEvents()
    {
        return static::$events;
    }

    /**
     * @inheritdoc
     */
    public function doesProvideMailForEvent($event)
    {
        assert(is_string($event));
        return in_array($event, static::$events);
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
        assert(is_string($event));

        $usr_id = (int) $parameter['usr_id'];
        $template_ident = $this->templateIdent();
        $recipient = $this->getRecipient($usr_id);
        $contexts = array(
            $this->getIliasContext(),
            $this->getUserContext($usr_id),
            $this->getCurrentUserContext(),
            $this->getCourseContext()
        );
        $contexts = array_merge($contexts, $this->getEnteContexts());
        $attachments = $this->getAttachments($usr_id);

        $mails = array(
            new TMSMail($recipient, $template_ident, $contexts, $attachments)
        );
        return $mails;
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
     *
     * @return ilTMSMailContextCurrentUser
     */
    protected function getCurrentUserContext()
    {
        return new \ilTMSMailContextCurrentUser();
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
    protected function getAttachments(int $usr_id)
    {
        return new \ilTMSMailAttachments();
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

    /**
     * @inheritdoc
     */
    protected function getEntityRefId() : int
    {
        return (int) $this->entity()->object()->getRefId();
    }

    protected function getEntityId() : int
    {
        return (int) $this->entity()->object()->getId();
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

    protected function getICal()
    {
        $dic = $this->getDIC();
        $il_mail_sys = $dic["mail.mime.sender.factory"]->system();
        $sender_name = $il_mail_sys->getFromName();
        $sender_mail = $il_mail_sys->getFromAddress();

        $iCal = new EluceoICalBuilder(CLIENT_ID, $sender_name, $sender_mail);

        return $iCal;
    }
}
