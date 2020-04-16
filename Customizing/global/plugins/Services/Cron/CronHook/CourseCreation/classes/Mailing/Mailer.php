<?php

namespace CaT\Plugins\CourseCreation\Mailing;

use \ILIAS\TMS\Mailing\TMSMail;
use \ILIAS\TMS\Mailing\TMSMailClerk;
use \ILIAS\TMS\CourseCreation\Request;
use \CaT\Plugins\CourseCreation\SendMails;

require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextILIAS.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextCourse.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextUser.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailRecipient.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailAttachments.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextTargetUser.php');

/**
 * The mailer builds mails (according to the job-result)
 * and gives them to the TMSClerk for sending.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class Mailer implements SendMails
{
    const TEMPLATE_IDENT_SUCCESS = 'P01';
    const TEMPLATE_IDENT_FAILURE = 'P02';
    const TEMPLATE_IDENT_FAILURE_ALERT = 'P03';
    const TEMPLATE_IDENT_ABORT = 'P04';
    const TEMPLATE_IDENT_SUCCESS_NOLOCALROLE = 'P05';
    const TEMPLATE_IDENT_SUCCESS_MEMBERROLE = 'P06';

    const EVENT_SUCCESS = 'course_creation_success';
    const EVENT_FAILURE = 'course_creation_fail';
    const EVENT_ABORT = 'course_creation_abort';

    /**
     * @var TMSMailClerk
     */
    protected $clerk;

    /**
     * @var int[]
     */
    protected $alert_to;

    /**
     * @param 	TMSMailClerk 	$clerk
     * @param 	int[] 	$alert_to 	a list of user-ids to notice
     */
    public function __construct(TMSMailClerk $clerk, array $alert_to)
    {
        $this->clerk = $clerk;
        $this->alert_to = $alert_to;
    }

    /**
     * @inheritdoc
     */
    public function sendSuccessMails(Request $request)
    {
        $this->sendUserMail(self::EVENT_SUCCESS, $request);
    }

    /**
     * @inheritdoc
     */
    public function sendAbortMails(Request $request)
    {
        $this->sendUserMail(self::EVENT_ABORT, $request);
    }

    /**
     * @inheritdoc
     */
    public function sendFailMails(Request $request, \Exception $e)
    {
        $this->sendUserMail(self::EVENT_FAILURE, $request);
    }

    /**
     * @param string $event
     * @param Request $request
     * @return  void
     */
    protected function sendUserMail($event, Request $request)
    {
        $usr_id = $request->getUserId();
        $crs_ref = $request->getTargetRefId(); //why can this be null?!
        $attachments = $this->getAttachments();
        $recipient = $this->getRecipient($usr_id);
        $base_contexts = array(
            $this->getIliasContext(),
            $this->getCourseContext($crs_ref)
        );

        $contexts = $base_contexts;
        $contexts[] = $this->getUserContext($usr_id);
        $lookup = array(
            self::EVENT_SUCCESS => [],
            self::EVENT_ABORT => [self::TEMPLATE_IDENT_ABORT],
            self::EVENT_FAILURE => [self::TEMPLATE_IDENT_FAILURE]
        );
        if (
            !is_null($crs_ref) &&
            !$this->userHasLocalRoleAtCourse($usr_id, $crs_ref) &&
            !$this->userHasCourseAdminOrTutorRole($usr_id, $crs_ref)
        ) {
            $lookup[self::EVENT_SUCCESS][] = self::TEMPLATE_IDENT_SUCCESS_NOLOCALROLE;
        }

        if (!is_null($crs_ref) && $this->userHasCourseMemberRole($usr_id, $crs_ref)) {
            $lookup[self::EVENT_SUCCESS][] = self::TEMPLATE_IDENT_SUCCESS_MEMBERROLE;
        }

        if (!is_null($crs_ref) && $this->userHasCourseAdminOrTutorRole($usr_id, $crs_ref)) {
            $lookup[self::EVENT_SUCCESS][] = self::TEMPLATE_IDENT_SUCCESS;
        }

        $mails = [];
        foreach ($lookup[$event] as $template_ident) {
            $mails[] = new TMSMail($recipient, $template_ident, $contexts, $attachments);
        }

        //alert mails to admins
        if ($event === self::EVENT_FAILURE) {
            $template_ident = self::TEMPLATE_IDENT_FAILURE_ALERT;
            foreach ($this->alert_to as $recipient_id) {
                $contexts = $base_contexts;
                $contexts[] = $this->getUserContext($recipient_id);
                $contexts[] = $this->getTargetUserContext($usr_id);
                $recipient = $this->getRecipient($recipient_id);
                $mails[] = new TMSMail($recipient, $template_ident, $contexts, $attachments);
            }
        }

        $this->clerk->process($mails, $event);
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
     * @param int $crs_ref_id
     * @return ilTMSMailContextCourse
     */
    protected function getCourseContext($crs_ref_id)
    {
        return new \ilTMSMailContextCourse($crs_ref_id);
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
     * @param int $usr_id
     * @return ilTMSMailContextUser
     */
    protected function getTargetUserContext($usr_id)
    {
        return new \ilTMSMailContextTargetUser($usr_id);
    }

    /**
     * @return ilTMSMailContextILIAS
     */
    protected function getIliasContext()
    {
        return new \ilTMSMailContextILIAS();
    }

    /**
     * @return ilTMSMailAttachments
     */
    protected function getAttachments()
    {
        $attachments = new \ilTMSMailAttachments();
        return $attachments;
    }

    /**
     * @param int $usr_id
     * @param int $crs_ref_id
     * @return bool
     */
    protected function userHasLocalRoleAtCourse($usr_id, $crs_ref_id)
    {
        require_once('Services/Membership/classes/class.ilParticipants.php');
        return \ilParticipants::_isParticipant($crs_ref_id, $usr_id);
    }

    /**
     * @param int $usr_id
     * @param int $crs_ref_id
     * @return bool
     */
    protected function userHasCourseMemberRole($usr_id, $crs_ref_id)
    {
        $crs_id = \ilObject::_lookupObjId($crs_ref_id);
        $crs_participant = \ilCourseParticipant::_getInstanceByObjId($crs_id, $usr_id);
        return $crs_participant->isMember();
    }

    /**
     * @param int $usr_id
     * @param int $crs_ref_id
     * @return bool
     */
    protected function userHasCourseAdminOrTutorRole($usr_id, $crs_ref_id)
    {
        $crs_id = \ilObject::_lookupObjId($crs_ref_id);
        $crs_participant = \ilCourseParticipant::_getInstanceByObjId($crs_id, $usr_id);
        return ($crs_participant->isAdmin() || $crs_participant->isTutor());
    }
}
