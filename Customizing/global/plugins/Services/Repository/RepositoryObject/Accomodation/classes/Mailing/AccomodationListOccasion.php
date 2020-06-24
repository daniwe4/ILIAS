<?php
namespace CaT\Plugins\Accomodation\Mailing;

use \ILIAS\TMS\Mailing\MailingOccasion;
use \ILIAS\TMS\Mailing\TMSMail;
use \ILIAS\TMS\Mailing\MailContext;
use \CaT\Ente\Entity;
use CaT\Ente\ILIAS\ilHandlerObjectHelper;
use \CaT\Plugins\Accomodation\Reservation\Export;
use ILIAS\TMS\Mailing\CourseSendMailHelper;

require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextILIAS.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextCourse.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailRecipient.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailAttachments.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailAttachment.php');

/**
 * Occasion for the sending of a AccomodationLists.
 * The event will be deferred, i.e. issued by ScheduledEvents.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class AccomodationListOccasion implements MailingOccasion
{
    use ilHandlerObjectHelper;
    use CourseSendMailHelper;

    const TEMPLATE_IDENT = 'O04';
    const EVENT_SEND_LIST = 'accomodationlist_due';
    const EVENT_REMIND_LIST = 'accomodationlist_reminder_due';

    const PARAM_OWNER_REF = 'xoac_ref_id';

    protected static $events = array(
        self::EVENT_SEND_LIST,
        self::EVENT_REMIND_LIST
    );

    /**
     * @var	Entity
     */
    protected $entity;

    /**
     * @var	\ilObjAccomodation
     */
    protected $owner;

    /**
     * @var	\Closure
     */
    protected $txt;

    public function __construct(Entity $entity, \ilObjAccomodation $owner, callable $txt)
    {
        $this->entity = $entity;
        $this->owner = $owner;
        $this->txt = $txt;

        global $DIC;
        $this->logger = $DIC->logger()->root();
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
        if (in_array($event, self::$events)) {
            if (!$this->owner->getActions()->getUserReservationsExist()) {
                $this->logger->write("no event/mail from occasion because the list is empty.");
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getNextScheduledDate()
    {
        list($invite, $reminder) = $this->owner()->getDueDates();
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
        //mails for the correct list only.
        $owner_ref = $this->owner->getRefId();
        $param_ref = $parameter[$this->getOwnerParameterName()];
        if ((int) $owner_ref !== (int) $param_ref) {
            $this->logger->write("no mail from occasion, owner-ref ($owner_ref) does not match param ($param_ref)");
            return array();
        }

        //no mails, if recipient is null
        $recipient = $this->getRecipient();
        if (is_null($recipient)) {
            $this->logger->write("no mail from occasion, recipient is null at owner-ref $owner_ref");
            return array();
        }

        $template_ident = $this->templateIdent();
        $contexts = array(
            $this->getIliasContext(),
            $this->getCourseContext()
        );
        $contexts = array_merge($contexts, $this->getEnteContexts());
        if ($event === self::EVENT_REMIND_LIST) {
            $contexts = array_merge($contexts, array($this->getReminderContext()));
        }

        $attachments = $this->getAttachments();

        $mails = array(
            new TMSMail($recipient, $template_ident, $contexts, $attachments)
        );
        return $mails;
    }

    /**
     * Get recipient.
     * A course's venue can be configured as free text.
     * In this case, there is no recipient-information.
     *
     * @return ilTMSMailRecipient | null
     */
    protected function getRecipient()
    {
        $mail = $this->owner->getEffectiveMailRecipient();
        if (is_null($mail)) {
            return null;
        }
        $recipient = new \ilTMSMailRecipient();
        $recipient = $recipient->withMail($mail);
        return $recipient;
    }

    /**
     * @return ilTMSMailContextCourse
     */
    protected function getCourseContext()
    {
        return new \ilTMSMailContextCourse($this->getEntityRefId());
    }

    /**
     * @return ilTMSMailContextILIAS
     */
    protected function getIliasContext()
    {
        return new \ilTMSMailContextILIAS();
    }


    /**
     * @return Mailing\MailContext
     */
    protected function getReminderContext()
    {
        return new ilMailContextOACReminder($this->entity, $this->owner);
    }

    /**
     * @inheritdoc
     */
    protected function getAttachments()
    {
        $exporter = new Export\PDFExport($this->owner, $this->owner->getActions());
        $exporter->writeOutput();
        $attachments = new \ilTMSMailAttachments();
        $accomodationlist = new \ilTMSMailAttachment();
        $accomodationlist = $accomodationlist->withAttachmentPath($exporter->getFilePath());
        $attachments->addAttachment($accomodationlist);
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
        return self::TEMPLATE_IDENT;
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
    protected function getEntityRefId()
    {
        return $this->entity()->object()->getRefId();
    }

    /**
     * @inheritdoc
     */
    public function getEntityId()
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
     * Return the owner of this component.
     * @return ilObjAccomodation
     */
    public function owner()
    {
        return $this->owner;
    }

    /**
     * Get the name of the parameter holding the ref-id for the owner-object.
     * @return string
     */
    public function getOwnerParameterName()
    {
        return self::PARAM_OWNER_REF;
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
