<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WorkflowReminder\NotFinalized\Webinar;

use CaT\Plugins\WorkflowReminder\NotFinalized\Log;

if (!class_exists(\ilCronJob::class)) {
    require_once "Services/Cron/classes/class.ilCronJob.php";
}

class NotFinalizedJob extends \ilCronJob
{
    const ID = "xwbr_notfinalized";

    /**
     * @var DB
     */
    protected $db;

    /**
     * @var Log\DB
     */
    protected $log_db;

    /**
     * @var \ilAppEventHandler
     */
    protected $event_handler;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var \ilObjCourse[]
     */
    protected $courses;

    public function __construct(
        DB $db,
        Log\DB $log_db,
        \ilAppEventHandler $event_handler,
        \Closure $txt
    ) {
        $this->db = $db;
        $this->log_db = $log_db;
        $this->event_handler = $event_handler;
        $this->txt = $txt;
        $this->courses = [];
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return static::ID;
    }

    public function getTitle()
    {
        return $this->txt("xwbr_not_finalized_title");
    }

    public function getDescription()
    {
        return $this->txt("xwbr_not_finalized_description");
    }

    /**
     * Is to be activated on "installation"
     *
     * @return boolean
     */
    public function hasAutoActivation()
    {
        return false;
    }

    /**
     * Can the schedule be configured?
     *
     * @return boolean
     */
    public function hasFlexibleSchedule()
    {
        return false;
    }

    /**
     * Get schedule type
     *
     * @return int
     */
    public function getDefaultScheduleType()
    {
        return \ilCronJob::SCHEDULE_TYPE_DAILY;
    }

    /**
     * Get schedule value
     *
     * @return int|array
     */
    public function getDefaultScheduleValue()
    {
        return 1;
    }

    /**
     * Processes the creation requests one after the other.
     *
     * @throws \Exception
     * @return \ilCronJobResult
     */
    public function run()
    {
        $cron_result = new \ilCronJobResult();

        if (\ilPluginAdmin::isPluginActive("xwbr")) {
            $pl = \ilPluginAdmin::getPluginObjectById("xwbr");
            $reminder_settings = $pl->getReminderSettings();
            $today = new \DateTime();
            $today->sub(new \DateInterval("P" . $reminder_settings->getInterval() . "D"));

            \ilCronManager::ping($this->getId());

            $open_courses = $this->db->getNotFinalizedCourses($today->format("Y-m-d"));

            foreach ($open_courses as $not_finalized) {
                $crs = $this->createCourse($not_finalized->getCrsRefId());
                $crs_members = $crs->getMembersObject();

                if ($crs_members->getCountMembers() == 0) {
                    global $DIC;
                    $DIC["ilLog"]->dump("no members on course: " . $not_finalized->getCrsRefId());
                    continue;
                }

                $param = [
                    "crs_ref_id" => $not_finalized->getCrsRefId(),
                    "child_ref_id" => $not_finalized->getChildRefId()
                ];

                $recipients = $crs_members->getTutors();
                if (is_null($recipients) || count($recipients) == 0) {
                    $recipients = $crs_members->getAdmins();
                }

                foreach ($recipients as $recipient) {
                    $param["usr_id"] = $recipient;
                    $this->event_handler->raise(
                        "Modules/Course",
                        "webinar_not_finalized",
                        $param
                    );
                }

                $this->log_db->insert(
                    $not_finalized->getCrsRefId(),
                    $not_finalized->getChildRefId(),
                    new \DateTime()
                );
            }
        }

        $cron_result->setStatus(\ilCronJobResult::STATUS_OK);
        return $cron_result;
    }

    protected function createCourse(int $ref_id)
    {
        if (!array_key_exists($ref_id, $this->courses)) {
            $this->courses[$ref_id] = new \ilObjCourse($ref_id);
        }

        return $this->courses[$ref_id];
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}
