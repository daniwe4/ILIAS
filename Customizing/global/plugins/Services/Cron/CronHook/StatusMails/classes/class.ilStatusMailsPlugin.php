<?php

declare(strict_types=1);

use CaT\Plugins\StatusMails;
use CaT\Plugins\StatusMails\Orgu;
use CaT\Plugins\StatusMails\History;
use CaT\Plugins\StatusMails\Mailing;
use ILIAS\TMS\Mailing as TMSMailing;
use CaT\Ente\ILIAS\Entity;

/**
 * Plugin base class. Keeps all information the plugin needs
 */
class ilStatusMailsPlugin extends ilCronHookPlugin
{
    const JOB_ACTIVITIES = StatusMails\ilStatusMailsUserActivities::ID;
    const JOB_UPCOMING = StatusMails\ilStatusMailsUpcoming::ID;
    const TEMPLATE_ACTIVITIES = 'S01';
    const TEMPLATE_UPCOMING = 'S02';

    /**
     * @inheritdoc
     */
    protected function beforeActivation()
    {
        parent::beforeActivation();

        //on activation, also install global provider
        StatusMails\UnboundGlobalProvider::createGlobalProvider();
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function afterDeactivation()
    {
        //on deactivation, also de-install global provider
        StatusMails\UnboundGlobalProvider::deleteGlobalProvider();
    }

    /**
     * @inheritDoc
     */
    public function getPluginName()
    {
        return "StatusMails";
    }

    /**
     * @inheritDoc
     */
    public function getCronJobInstances()
    {
        return array(
            $this->getJobActivities(),
            $this->getJobUpcoming()
        );
    }

    protected function getJobActivities() : StatusMails\ilStatusMailsUserActivities
    {
        return new StatusMails\ilStatusMailsUserActivities(
            $this->getOrguDB(),
            $this->getHistoryDB(),
            $this->getMailFactory(
                self::TEMPLATE_ACTIVITIES,
                $this->getContextStatus()
            ),
            $this->getMailClerk(),
            $this->getTree(),
            $this->txtClosure()
        );
    }

    protected function getOrguDB() : Orgu\DB
    {
        require_once("Services/TMS/Positions/TMSPositionHelper.php");
        require_once("Modules/OrgUnit/classes/Positions/UserAssignment/class.ilOrgUnitUserAssignmentQueries.php");
        return new Orgu\ilDB(new \TMSPositionHelper(\ilOrgUnitUserAssignmentQueries::getInstance()));
    }

    protected function getHistoryDB() : History\DB
    {
        global $DIC;
        return new History\UserCourseHistorizingDB($DIC->database());
    }

    protected function getMailFactory(string $template_ident, TMSMailing\MailContext $context) : Mailing\MailFactory
    {
        return new Mailing\MailFactory($template_ident, $context);
    }

    public function getContextStatus() : Mailing\ilMailContextStatus
    {
        $template = $this->getTemplateUserEntry();
        return new Mailing\ilMailContextStatus($template, $this->txtClosure());
    }

    protected function getTemplateUserEntry() : Mailing\ContentBlocks\TemplateUserEntry
    {
        return new Mailing\ContentBlocks\TemplateUserEntry();
    }

    public function txtClosure() : Closure
    {
        return function ($code) {
            return $this->txt($code);
        };
    }

    protected function getMailClerk() : TMSMailing\TMSMailClerk
    {
        require_once("./Services/TMS/Mailing/classes/ilTMSMailing.php");
        $mailing = new \ilTMSMailing();
        return $mailing->getClerk();
    }

    protected function getTree() : ilTree
    {
        global $DIC;
        return $DIC['tree'];
    }

    protected function getJobUpcoming() : StatusMails\ilStatusMailsUpcoming
    {
        return new StatusMails\ilStatusMailsUpcoming(
            $this->getOrguDB(),
            $this->getHistoryDB(),
            $this->getMailFactory(
                self::TEMPLATE_UPCOMING,
                $this->getContextUpcoming()
            ),
            $this->getMailClerk(),
            $this->getTree(),
            $this->txtClosure()
        );
    }

    public function getContextUpcoming() : Mailing\ilMailContextUpcoming
    {
        $template = $this->getTemplateUserEntry();
        return new Mailing\ilMailContextUpcoming($template, $this->txtClosure());
    }

    /**
     * @inheritDoc
     */
    public function getCronJobInstance($a_job_id)
    {
        if ($a_job_id === self::JOB_ACTIVITIES) {
            return $this->getJobActivities();
        }
        if ($a_job_id === self::JOB_UPCOMING) {
            return $this->getJobUpcoming();
        }
    }

    public function getContextsForGlobalProvider(Entity $entity, \ilObjRootFolder $owner) : array
    {
        return [
            new Mailing\ilGlobalContextStatus($entity, $owner, $this->txtClosure()),
            new Mailing\ilGlobalContextUpcoming($entity, $owner, $this->txtClosure())
        ];
    }
}
