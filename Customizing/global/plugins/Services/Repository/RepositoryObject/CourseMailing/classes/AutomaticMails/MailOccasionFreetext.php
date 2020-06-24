<?php
namespace CaT\Plugins\CourseMailing\AutomaticMails;

use \ILIAS\TMS\Mailing\MailingOccasion;
use \ILIAS\TMS\Mailing\TMSMail;
use \ILIAS\TMS\Mailing\MailContext;
use ILIAS\TMS\Mailing\CourseSendMailHelper;
use \CaT\Ente\Entity;
use \CaT\Ente\ILIAS\ilHandlerObjectHelper;
use ILIAS\TMS\CourseInfoHelper;

class MailOccasionFreetext implements MailingOccasion
{
    use ilHandlerObjectHelper;
    use CourseInfoHelper;
    use CourseSendMailHelper;

    const TEMPLATE_IDENT = "freetext";
    const TEMPLATE_IDENT_FOR_LOG = "Freitext";

    /**
     * @var	Entity
     */
    protected $entity;

    /**
     * @var \ilObjCourseMailing
     */
    protected $owner;

    protected static $events = array(
        \ilCourseMailingPlugin::EVENT_FREETEXT
    );


    public function __construct(
        Entity $entity,
        \ilObjCourseMailing $owner
    ) {
        $this->entity = $entity;
        $this->owner = $owner;
    }

    /**
     * Return the owner of this component.
     * @return \ilObjCourseMailing
     */
    public function owner()
    {
        return $this->owner;
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
        return new \DateTime(date("Y-m-d"));
    }

    /**
     * @inheritdoc
     */
    public function getMails($event, $parameter)
    {
        assert(is_string($event));
        $template_ident = $this->templateIdent();
        $contexts = array_merge(
            array($this->getIliasContext(),	$this->getCourseContext()),
            $this->getEnteContexts()
        );

        $mails = array();
        global $DIC;
        $log = $DIC->logger()->root();
        $subject = $parameter["subject"];
        $body = $parameter["body"];
        $usr_id = $parameter["usr_id"];
        $attachments = $this->getAttachments($parameter["attachments"]);

        $cons = array_merge($contexts, array($this->getUserContext($usr_id)));
        $recipient = $this->getRecipient($usr_id);
        $mails[] = new TMSMail(
            $recipient,
            self::TEMPLATE_IDENT_FOR_LOG,
            $cons,
            $attachments,
            true,
            $subject,
            $body
        );

        $log->write($template_ident . ' with recipient_id ' . $usr_id);
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
     * @return ilTMSMailContextILIAS
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
        return self::TEMPLATE_IDENT;
    }

    /**
     * @inheritdoc
     */
    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    protected function txt($code)
    {
        if ($this->txt === null) {
            $this->txt = $this->owner()->txtClosure();
        }

        $txt = $this->txt;
        return $txt($code);
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
    protected function getEntityId()
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

    protected function getAttachments($files)
    {
        if (count($files) == 0) {
            return null;
        }

        $attachments = new \ilTMSMailAttachments();
        foreach ($files as $file) {
            $attachment = new \ilTMSMailAttachment();
            $attachment = $attachment->withAttachmentPath($file);
            $attachments->addAttachment($attachment);
        }

        return $attachments;
    }
}
