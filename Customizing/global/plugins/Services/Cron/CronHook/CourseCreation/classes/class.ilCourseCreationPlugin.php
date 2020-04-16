<?php
require_once __DIR__ . "/../vendor/autoload.php";
include_once("./Services/Cron/classes/class.ilCronHookPlugin.php");
require_once(__DIR__ . "/class.ilCourseCreationJob.php");
require_once(__DIR__ . "/../vendor/autoload.php");

use CaT\Plugins\CourseCreation;

/**
 * Plugin base class. Keeps all information the plugin needs
 */
class ilCourseCreationPlugin extends ilCronHookPlugin
{
    use CourseCreation\DI;

    /**
     * Get the name of the Plugin
     *
     * @return string
     */
    public function getPluginName()
    {
        return "CourseCreation";
    }

    /**
     * Get the actions object for this plugin.
     *
     * @return CourseCreation\ilActions
     */
    public function getActions()
    {
        return $this->getDI()["actions"];
    }

    /**
     * Get an array with 1 to n numbers of cronjob objects
     *
     * @return ilCourseCreationJob[]
     */
    public function getCronJobInstances()
    {
        return [$this->getCronJobInstance(ilCourseCreationJob::ID)];
    }

    /**
     * Get a single cronjob object
     *
     * @param	string	$a_job_id
     * @return ilCourseCreationJob
     */
    public function getCronJobInstance($a_job_id)
    {
        assert('is_string($a_job_id)');
        global $DIC;
        if ($a_job_id != ilCourseCreationJob::ID) {
            throw new \InvalidArgumentException("Unknown id for ilCourseCreationPlugin: '$a_job_id'");
        }
        $process = new ILIAS\TMS\CourseCreation\Process($DIC->repositoryTree(), $DIC->database(), $DIC["objDefinition"]);
        $request_db = $this->getRequestDB();
        $send_mails = $this->getMailer();
        $logger = ilLoggerFactory::getLogger("xccr")->getLogger();
        return new ilCourseCreationJob($request_db, $process, $send_mails, $logger, $DIC["tree"]);
    }

    /**
     * Get the request database.
     *
     * @return ilRequestDB
     */
    private function getRequestDB()
    {
        return $this->getDI()["request.db"];
    }

    /**
     * Get the mailer.
     *
     * @return CourseCreation\Mailing\Mailer
     */
    private function getMailer()
    {
        return new CourseCreation\Mailing\Mailer(
            $this->getTMSMailClerk(),
            $this->getContactsForFailureMail()
        );
    }

    /**
     * Get TMS mail clerk.
     * @return TMSMailClerk
     */
    private function getTMSMailClerk()
    {
        require_once('./Services/TMS/Mailing/classes/ilTMSMailing.php');
        $mailing = new \ilTMSMailing();
        return  $mailing->getClerk();
    }

    /**
     * Get contacts from the ILIAS administration (general -> contacts).
     * @return int[]
     */
    private function getContactsForFailureMail()
    {
        return array_map(
            function (CourseCreation\Recipients\Recipient $recipient) {
                return $recipient->getUserId();
            },
            $this->getDI()["creationsettings.config.failedrecipients.db"]->getRecipients()
        );
    }

    public function txtClosure() : Closure
    {
        return function ($c) {
            return $this->txt($c);
        };
    }

    public function getDI()
    {
        if (is_null($this->di)) {
            global $DIC;
            $this->di = $this->getPluginDI(
                $this,
                $DIC
            );
        }

        return $this->di;
    }
}
