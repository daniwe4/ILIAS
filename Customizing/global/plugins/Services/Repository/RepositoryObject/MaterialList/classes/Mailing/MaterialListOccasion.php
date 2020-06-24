<?php
namespace CaT\Plugins\MaterialList\Mailing;

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
 * Occasion for the sending of a MaterialList.
 * The event will be deferred, i.e. issued by ScheduledEvents.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class MaterialListOccasion implements MailingOccasion
{
    use ilHandlerObjectHelper;
    use CourseSendMailHelper;

    const TEMPLATE_IDENT = 'O01';
    const EVENT_SEND_LIST = 'materiallist_due';

    const PARAM_OWNER_REF = 'xmat_ref_id';

    protected static $events = array(
        self::EVENT_SEND_LIST
    );

    /**
     * @var	Entity
     */
    protected $entity;

    /**
     * @var	\ilObjMaterialList
     */
    protected $owner;

    /**
     * @var	\Closure
     */
    protected $txt;

    public function __construct(Entity $entity, \ilObjMaterialList $owner, callable $txt)
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
            $list_entries = $this->owner->getActions()->getListEntiesForCurrentObj();
            if (count($list_entries) === 0) {
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
        return $this->owner()->getDueDate();
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
        $settings = $this->owner->getSettings();
        $actions = $this->owner->getActions();

        $mode = $settings->getRecipientMode();
        if ($mode === $actions::M_SELECTION) { //configured at object
            $mail = $settings->getRecipient();
        }
        if ($mode === $actions::M_COURSE_VENUE) { //venue from course
            $course_venue = $this->owner->getAssignedVenueFromParentCourse();
            if (is_null($course_venue)) {
                return null;
            }
            $mail = $course_venue->getService()->getMailMaterialList();
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
     * @inheritdoc
     */
    protected function getAttachments()
    {
        $plug = \ilPluginAdmin::getPluginObjectById('xmat');
        $exporter = $plug->getListXLSExporter(array($this->owner));
        list($tmp_folder, $file_name) = $exporter->getFileLocation();
        $exporter->export();

        $materiallist = new \ilTMSMailAttachment();
        $materiallist = $materiallist->withAttachmentPath($tmp_folder . $file_name);
        $attachments = new \ilTMSMailAttachments();
        $attachments->addAttachment($materiallist);
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
     * @return ilObjMaterialList
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
        if (!$this->isCourseMailingInSubTree() ||
            $this->isCopySettingsInSubTree() ||
            !$this->isCourseOnline()
        ) {
            return false;
        }
        return true;
    }
}
