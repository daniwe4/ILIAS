<?php

require_once("./Services/Cron/classes/class.ilCronHookPlugin.php");
require_once __DIR__ . '/../vendor/autoload.php';

use CaT\Plugins\ScheduledEvents;

/**
 * Plugin base class. Keeps all information the plugin needs
 */
class ilScheduledEventsPlugin extends \ilCronHookPlugin
{
    const MAIL_RECIPIENT = 'ilias@cat06.de';

    /**
     * Get the name of the Plugin.
     *
     * @return string
     */
    public function getPluginName()
    {
        return "ScheduledEvents";
    }

    /**
     * Get the name of the current ILIAS installation.
     *
     * @return string
     */
    public function getInstallationId()
    {
        return CLIENT_NAME;
    }

    /**
     * Get the email-address to send errors to.
     *
     * @return string
     */
    public function getMailRecipient()
    {
        return static::MAIL_RECIPIENT;
    }

    /**
     * Get an array with 1 to n numbers of cronjob objects.
     *
     * @return ilScheduledEventsJob[]
     */
    public function getCronJobInstances()
    {
        return array($this->getCronJobInstance(0));
    }

    /**
     * Get the mailer of this plugin.
     *
     * @return Mail\Mailer
     */
    protected function getMailer()
    {
        $installation_id = $this->getInstallationId();
        $recipient_address = $this->getMailRecipient();
        $mailer = new ScheduledEvents\Mail\Mailer($recipient_address, $installation_id);
        return $mailer;
    }

    /**
     * Get a single cronjob object.
     *
     * @return ilScheduledEventsJob
     */
    public function getCronJobInstance($a_job_id)
    {
        require_once(__DIR__ . "/class.ilScheduledEventsJob.php");
        return new ilScheduledEventsJob(
            $this,
            $this->getTMSEventSchedule(),
            $this->getEventHandler(),
            $this->getMailer()
        );
    }

    /**
     * Get a closure to get txts from plugin.
     *
     * @return \Closure
     */
    public function txtClosure()
    {
        return function ($code) {
            return $this->txt($code);
        };
    }

    /**
     * @return  DIC
     */
    protected function getDIC()
    {
        global $DIC;
        return $DIC;
    }

    /**
     * @return  \ilDBInterface
     */
    protected function getDB()
    {
        return $this->getDIC()->database();
    }

    /**
     * @return  \ilAppEventHandler
     */
    protected function getEventHandler()
    {
        return $this->getDIC()["ilAppEventHandler"];
    }

    /**
     * @return  ILIAS\TMS\ScheduledEvents\DB
     */
    protected function getTMSEventSchedule()
    {
        require_once('./Services/TMS/ScheduledEvents/classes/Schedule.php');
        return new Schedule($this->getDB());
    }

    /**
     * @return  ScheduledEvents\ilActions
     */
    public function getActions()
    {
        return new ScheduledEvents\ilActions($this->getTMSEventSchedule());
    }
}
