<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */


require_once "Services/Cron/classes/class.ilCronJob.php";
require_once "Services/Cron/classes/class.ilCronManager.php";
require_once "Services/Cron/classes/class.ilCronJobResult.php";

require_once("class.ilScheduledEventsPlugin.php");

use \CaT\Plugins\ScheduledEvents\Mail;

/**
* Implementation of the cron job
*/
class ilScheduledEventsJob extends ilCronJob
{
    const ID = 'scheduledevents';

    protected $plugin;
    protected $schedule;
    protected $event_handler;

    /**
    *@var Mail\Mailer
    */
    protected $mailer;

    public function __construct(
        \ilScheduledEventsPlugin $plugin,
        ILIAS\TMS\ScheduledEvents\DB $schedule,
        \ilAppEventHandler $event_handler,
        Mail\Mailer $mailer
        ) {
        $this->plugin = $plugin;
        $this->schedule = $schedule;
        $this->event_handler = $event_handler;
        $this->mailer = $mailer;
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return self::ID;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->plugin->txt("title");
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->plugin->txt("description");
    }

    /**
     * Is to be activated on "installation"
     *
     * @return boolean
     */
    public function hasAutoActivation()
    {
        return true;
    }

    /**
     * Can the schedule be configured?
     *
     * @return boolean
     */
    public function hasFlexibleSchedule()
    {
        return true;
    }

    /**
     * Get schedule type
     *
     * @return int
     */
    public function getDefaultScheduleType()
    {
        return ilCronJob::SCHEDULE_TYPE_IN_MINUTES;
    }

    /**
     * Get schedule value
     *
     * @return int|array
     */
    public function getDefaultScheduleValue()
    {
        return 15;
    }

    /**
     * Get called if the cronjob is started
     * Executing the ToDo's of the cronjob
     */
    public function run()
    {
        global $DIC;
        $logger = $DIC->logger()->root();
        $errors = [];

        $due_events = $this->schedule->getAllDue();
        foreach ($due_events as $event) {
            $component = $event->getComponent();
            $event_id = $event->getEvent();
            $params = $event->getParameters();

            $logmsg = "Due scheduled event #" . $event->getId();
            $logger->write($logmsg);

            try {
                $this->event_handler->raise($component, $event_id, $params);
                $logmsg = 'Raised scheduled event: ' . $component . ', ' . $event_id . ' (';
                foreach ($params as $key => $value) {
                    $logmsg .= " $key=>$value";
                }
                $logmsg .= ')';
                $logger->write($logmsg);

                $this->schedule->setAccountedFor(array($event));
                $logger->write('ok.');
            } catch (\Throwable $e) {
                $logger->write('Execution FAILED.');
                $logger->write($e->getMessage());
                $logger->write($e->getTraceAsString());
                $errors[] = $e;
            }

            \ilCronManager::ping($this->getId());
        }

        if (count($errors) > 0) {
            $this->mailer->send($errors);
        }

        $cron_result = new \ilCronJobResult();
        $cron_result->setStatus(\ilCronJobResult::STATUS_OK);
        return $cron_result;
    }
}
