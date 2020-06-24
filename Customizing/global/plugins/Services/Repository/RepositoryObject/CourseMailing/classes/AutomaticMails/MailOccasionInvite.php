<?php
namespace CaT\Plugins\CourseMailing\AutomaticMails;

use \ILIAS\TMS\Mailing\MailingOccasion;
use \ILIAS\TMS\Mailing\TMSMail;
use \ILIAS\TMS\Mailing\MailContext;
use ILIAS\TMS\Mailing\EluceoICalBuilder;
use ILIAS\TMS\Mailing\CourseSendMailHelper;
use \CaT\Ente\Entity;
use \CaT\Ente\ILIAS\ilHandlerObjectHelper;
use \ILIAS\TMS\File;
use ILIAS\TMS\CourseInfo;
use ILIAS\TMS\CourseInfoHelper;

class MailOccasionInvite implements MailingOccasion
{
    use ilHandlerObjectHelper;
    use CourseInfoHelper;
    use CourseSendMailHelper;

    /**
     * @var	Entity
     */
    protected $entity;

    /**
     * @var	\ilObjCourseMailing
     */
    protected $owner;

    /**
     * @var	string
     */
    protected $template_ident;

    /**
     * @var	int[]
     */
    protected $recipient_ids;

    /**
     * @var	string[]
     */
    protected $attachment_ids;


    protected static $events = array(
        \ilCourseMailingPlugin::EVENT_INVITATION,
        \ilCourseMailingPlugin::EVENT_REMINDER,
        \ilCourseMailingPlugin::EVENT_INVITATION_SINGLE
    );


    public function __construct(
        Entity $entity,
        \ilObjCourseMailing $owner,
        $template_ident,
        array $recipient_ids,
        array $attachment_ids
    ) {
        $this->entity = $entity;
        $this->owner = $owner;
        $this->template_ident = $template_ident;
        $this->recipient_ids = $recipient_ids;
        $this->attachment_ids = $attachment_ids;
    }

    /**
     * Return the owner of this component.
     * @return ilObjCourseMailing
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
        list($invite, $reminder) = $this->owner()->getActions()->getInvitationDates();
        $now = new \DateTime();
        if (!is_null($invite) && $now < $invite) {
            return $invite;
        }
        if (!is_null($reminder) && $now < $reminder) {
            return $reminder;
        }
        return null;
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

        if ($event === \ilCourseMailingPlugin::EVENT_REMINDER) {
            $contexts = array_merge($contexts, array($this->getReminderContext()));
        }

        $mails = array();
        global $DIC;
        $log = $DIC->logger()->root();

        /*
        recipient_ids are set by UnboundProvider::getMailingOccasions();
        the role-mapping is active there. Override this for manual sending.
        */
        if ($event == 'manual') {
            $this->recipient_ids = array($parameter['usr_id']);
            if ($parameter['attachments'] !== false) {
                $this->attachment_ids = $parameter['attachments'];
            }
        }
        /**
         * Modules/Course::addParticipant will be deferred as EVENT_INVITATION_SINGLE
         */
        if ($event == \ilCourseMailingPlugin::EVENT_INVITATION_SINGLE) {
            $this->recipient_ids = [];
            $this->attachment_ids = [];
            $template_ident = false;

            $usr_id = (int) $parameter['usr_id'];
            $mappings = $this->owner()->getRoleMappings();
            $actions = $this->owner()->getActions();
            $roles = $actions->getRoleIdsForMember($usr_id);

            foreach ($mappings as $mapping) {
                if (in_array($mapping->getRoleId(), $roles)) {
                    $template_id = $mapping->getTemplateId();
                    if ($template_id > 0) {
                        $template = $actions->getMailTemplate($template_id);
                        if (!$template) {
                            $this->recipient_ids = [];
                            $log->write("template not found: " . $template_id);
                        } else {
                            $template_ident = $template->getTitle();
                            $this->attachment_ids = $mapping->getAttachmentIds();
                            $this->recipient_ids = [$usr_id];
                        }
                    }
                }
            }
        }

        foreach ($this->recipient_ids as $usr_id) {
            if (array_key_exists('usr_id', $parameter) == false
                || $usr_id == $parameter['usr_id'] //only for users matched by role-mapping
            ) {
                $cons = array_merge($contexts, array($this->getUserContext($usr_id)));
                $recipient = $this->getRecipient($usr_id);
                $attachments = $this->getAttachments($usr_id);
                $mails[] = new TMSMail($recipient, $template_ident, $cons, $attachments);
            }
            $log->write($template_ident . ' with recipient_id(s) ' . implode(',', $this->recipient_ids));
        }
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
     * @inheritdoc
     */
    protected function getAttachments(int $usr_id)
    {
        $attachments = new \ilTMSMailAttachments();
        $provided_files = $this->getComponentsOfType(File::class);
        foreach ($provided_files as $file) {
            if (in_array($file->getId(), $this->attachment_ids)) {
                $attachment = new \ilTMSMailAttachment();
                $attachment = $attachment->withAttachmentPath($file->getFilePath());
                $attachments->addAttachment($attachment);
            }
        }


        $key = $this->getEntityId() . ":" . $this->owner->getRefId() . ":ical";
        if (in_array($key, $this->attachment_ids)) {
            $attachments = $this->addICalAsAttachment($attachments, $usr_id);
        }

        return $attachments;
    }

    /**
     * Adds the iCal as an attachment
     *
     * @param \ilTMSMailAttachments
     *
     * @return \ilTMSMailAttachments
     */
    protected function addICalAsAttachment(\ilTMSMailAttachments $attachments, int $usr_id)
    {
        $crs = \ilObjectFactory::getInstanceByRefId($this->getEntityRefId());
        if ($crs->getCourseStart() == null) {
            return $attachments;
        }

        $crs_ref_id = $crs->getRefId();
        $dic = $this->getDIC();
        $il_mail_sys = $dic["mail.mime.sender.factory"]->system();
        $sender_name = $il_mail_sys->getFromName();
        $sender_mail = $il_mail_sys->getFromAddress();

        $iCal = new EluceoICalBuilder(CLIENT_ID, $sender_name, $sender_mail);
        $attachment = new \ilTMSMailAttachment();

        $comp = $this->getICalContexts();
        $ical_path = $iCal->saveICal(
            "crs_$crs_ref_id",
            $comp,
            sprintf($this->txt("calendar_entry"), $usr_id, $crs_ref_id)
        );
        $attachment = $attachment
            ->withAttachmentPath($ical_path);
        $attachments->addAttachment($attachment);
        return $attachments;
    }

    /**
     * @return Mailing\MailContext[]
     */
    protected function getICalContexts()
    {
        $components = $this->getCourseInfo(CourseInfo::CONTEXT_ICAL);
        return $components;
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
     * @return Mailing\MailContext
     */
    protected function getReminderContext()
    {
        return new ilMailContextReminder();
    }

    /**
     * @inheritdoc
     */
    public function templateIdent()
    {
        return $this->template_ident;
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
}
