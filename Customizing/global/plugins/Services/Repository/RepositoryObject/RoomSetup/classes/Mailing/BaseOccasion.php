<?php
namespace CaT\Plugins\RoomSetup\Mailing;

use \ILIAS\TMS\Mailing\MailingOccasion;
use \ILIAS\TMS\Mailing\TMSMail;
use \ILIAS\TMS\Mailing\MailContext;
use \CaT\Ente\Entity;
use CaT\Ente\ILIAS\ilHandlerObjectHelper;
use ILIAS\TMS\Mailing\CourseSendMailHelper;

require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextILIAS.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextCourse.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailRecipient.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailAttachments.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailAttachment.php');

/**
 * Occasion for sending the RoomSetup
 * The event will be deferred, i.e. issued by ScheduledEvents.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
abstract class BaseOccasion implements MailingOccasion
{
    use ilHandlerObjectHelper;
    use CourseSendMailHelper;

    const TEMPLATE_IDENT = '';
    const EVENT_SEND_SERVICE = 'roomservice_due';
    const EVENT_SEND_ROOMSETUP = 'roomesetup_due';
    const PARAM_OWNER_REF = 'xrse_ref_id';

    protected static $events = array();

    /**
     * @var	Entity
     */
    protected $entity;

    /**
     * @var	\ilObjRoomSetup
     */
    protected $owner;

    /**
     * @var	\Closure
     */
    protected $txt;

    public function __construct(Entity $entity, \ilObjRoomSetup $owner, \Closure $txt)
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
        return static::$events;
    }

    /**
     * @inheritdoc
     */
    public function doesProvideMailForEvent($event)
    {
        assert('is_string($event)');
        if (in_array($event, static::$events)) {
            if ($this->existValuesForMailing() === false) {
                $this->logger->write("no event/mail from occasion because values are empty.");
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Mail should not be sent if there are no values.
     * @return bool
     */
    abstract protected function existValuesForMailing();

    /**
     * @inheritdoc
     */
    public function getMails($event, $parameter)
    {
        assert('is_string($event)');
        //mails for the correct list only.
        $owner_ref = $this->owner->getRefId();
        $param_ref = $parameter[$this->getOwnerParameterName()];
        if ((int) $owner_ref !== (int) $param_ref) {
            $this->logger->write("no mail from occasion, owner-ref($owner_ref) does not match param ($param_ref)");
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
    abstract protected function getRecipient();

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
    public function entity()
    {
        return $this->entity;
    }

    /**
     * @inheritdoc
     */
    public function getEntityId()
    {
        return (int) $this->entity()->object()->getId();
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
