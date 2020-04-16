<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDCommunicator\Jobs;

use CaT\WBD\Services\Services as WBD_SERVICE;
use CaT\WBD\Log\Log as WBD_LOG;
use CaT\WBD\Responses as WBD_RESPONSES;

use ILIAS\TMS\WBD\Cases;
use ILIAS\TMS\WBD\Responses;

use CaT\Plugins\WBDCommunicator\Config;

class ilCancelParticipationsJob extends \ilCronJob
{
    const ID = "wbd_cancel_participations";

    /**
     * @var WBD_LOG
     */
    protected $wbd_log;

    /**
     * @var WBD_SERVICE
     */
    protected $services;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var Cases\DB
     */
    protected $cases_db;

    /**
     * @var Responses\DB
     */
    protected $response_db;

    /**
     * @var Config\OperationLimits\DB
     */
    protected $op_limits_db;

    /**
     * @var Config\UDF\DB
     */
    protected $udf_db;

    /**
     * @var CronManager
     */
    protected $cron_manager;

    public function __construct(
        WBD_LOG $wbd_log,
        WBD_SERVICE $services,
        \Closure $txt,
        Cases\DB $cases_db,
        Responses\DB $response_db,
        Config\UDF\DB $udf_db,
        Config\OperationLimits\DB $op_limits_db,
        CronManager $cron_manager
    ) {
        $this->wbd_log = $wbd_log;
        $this->services = $services;
        $this->txt = $txt;

        $this->cases_db = $cases_db;
        $this->response_db = $response_db;
        $this->op_limits_db = $op_limits_db;
        $this->cron_manager = $cron_manager;

        $this->udf_db = $udf_db;
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
        return $this->txt("job_cancel_participations");
    }

    public function getDescription()
    {
        return $this->txt("job_cancel_participations_description");
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
        $gutberaten_udf_id = $this->udf_db->getUDFFieldIdForWBDID();
        $announce_udf_id = $this->udf_db->getUDFFieldIdForStatus();

        $max_cancellations = $this->op_limits_db->getMaxNumberOfCancellations();
        if ($max_cancellations == 0) {
            $this->wbd_log->write("Limit for max cancellations is 0. No participation was cancelled.");
            $cron_result->setStatus(\ilCronJobResult::STATUS_OK);
            return $cron_result;
        }

        $this->cancelParticipations(
            $gutberaten_udf_id->getFieldId(),
            $announce_udf_id->getFieldId(),
            $max_cancellations
        );

        $cron_result->setStatus(\ilCronJobResult::STATUS_OK);
        return $cron_result;
    }

    protected function cancelParticipations(
        int $gutberaten_udf_id,
        int $announce_udf_id,
        int $max_announcements
    ) {
        $participations = $this->cases_db->getParticipationsToCancel();

        $this->cron_manager->ping($this->getId());

        if (count($participations) == 0) {
            $this->wbd_log->write("No participations found.");
            $this->cron_manager->ping($this->getId());
            return;
        }

        foreach ($participations as $participation) {
            $result = $this->services->cancelParticipation($participation);
            if ($result->isError()) {
                $this->wbd_log->write($result->getErrorMessage());
                $this->cron_manager->ping($this->getId());
                continue;
            }

            $this->announceReported($result);
            $this->cron_manager->ping($this->getId());

            $max_announcements--;
            if ($max_announcements == 0) {
                $this->cron_manager->ping($this->getId());
                break;
            }
        }
    }

    protected function announceReported(WBD_RESPONSES\Cancellation $participation)
    {
        $send_data = $participation->getSentData();

        $this->response_db->removeAnnouncedCase(
            $send_data->getCrsId(),
            $send_data->getUsrId()
        );

        $this->response_db->removeCurrentBookingStatusBy(
            $send_data->getCrsId(),
            $send_data->getUsrId()
        );

        $this->response_db->setParticipationCancelled(
            $send_data->getCrsId(),
            $send_data->getUsrId(),
            $participation->getWbdBookingId()
        );

        $this->response_db->removeWBDBookingId(
            $send_data->getCrsId(),
            $send_data->getUsrId(),
            $send_data->getWbdBookingId()
        );
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}
