<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WorkflowReminder\MinMember;

if (!class_exists(\ilCronJob::class)) {
    require_once "Services/Cron/classes/class.ilCronJob.php";
}

class MinMemberJob extends \ilCronJob
{
    const ID = "rminmember";

    /**
     * @var DB
     */
    protected $db;

    /**
     * @var \ilAppEventHandler
     */
    protected $event_handler;

    /**
     * @var \Closure
     */
    protected $txt;

    public function __construct(
        DB $db,
        \ilAppEventHandler $event_handler,
        \Closure $txt
    ) {
        $this->db = $db;
        $this->event_handler = $event_handler;
        $this->txt = $txt;
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
        return $this->txt("min_member_title");
    }

    public function getDescription()
    {
        return $this->txt("min_member_description");
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

        if (\ilPluginAdmin::isPluginActive("xbkm")) {
            $pl = \ilPluginAdmin::getPluginObjectById("xbkm");
            $min_member_settings = $pl->getReminderSettings();

            if ($min_member_settings->getSendMail() === true) {
                \ilCronManager::ping($this->getId());

                $offset = $min_member_settings->getDaysBeforeCourse();
                $crs_datas = $this->db->getCoursesWithoutMinMembers($offset);

                foreach ($crs_datas as $crs_ref_id => $crs_data) {
                    $crs = new \ilObjCourse($crs_data->getCrsRefId());
                    $crs_members = $crs->getMembersObject();

                    $param = [
                        "crs_ref_id" => $crs_ref_id,
                    ];
                    if ($crs_members->getCountMembers() < $crs_data->getMinMember()) {
                        foreach ($crs_members->getAdmins() as $admin) {
                            $param["usr_id"] = $admin;
                            $this->event_handler->raise(
                                "Modules/Course",
                                \ILIAS\TMS\Booking\Actions::EVENT_REMINDER_MIN_MEMBER,
                                $param
                                );

                            \ilCronManager::ping($this->getId());
                        }
                    }

                    \ilCronManager::ping($this->getId());
                }
            }
        }

        $cron_result->setStatus(\ilCronJobResult::STATUS_OK);
        return $cron_result;
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}
