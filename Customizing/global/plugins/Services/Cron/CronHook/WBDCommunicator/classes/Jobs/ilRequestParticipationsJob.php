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

class ilRequestParticipationsJob extends \ilCronJob
{
    const ID = "wbd_request_participations";

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
        Config\OperationLimits\DB $op_limits_db,
        Config\UDF\DB $udf_db,
        CronManager $cron_manager
    ) {
        $this->wbd_log = $wbd_log;
        $this->services = $services;
        $this->txt = $txt;

        $this->cases_db = $cases_db;
        $this->response_db = $response_db;
        $this->cron_manager = $cron_manager;

        $this->op_limits_db = $op_limits_db;
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
        return $this->txt("job_request_participations");
    }

    public function getDescription()
    {
        return $this->txt("job_request_participations_description");
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

        $this->cron_manager->ping($this->getId());

        $this->importParticipationsFromWBD(
            $gutberaten_udf_id->getFieldId(),
            $announce_udf_id->getFieldId()
        )
        ;

        $this->cron_manager->ping($this->getId());

        $cron_result->setStatus(\ilCronJobResult::STATUS_OK);
        return $cron_result;
    }

    protected function importParticipationsFromWBD(
        int $gutberaten_udf_id,
        int $announce_udf_id
    ) {
        $max_user_to_handle = $this->op_limits_db->getLimitForRequest();
        $offset = $this->op_limits_db->getOffsetForRequest();
        $this->cron_manager->ping($this->getId());
        if ($max_user_to_handle === 0) {
            return;
        }

        $affected_usrs = $this->cases_db->getIdsForParticipationRequest(
            $gutberaten_udf_id,
            $announce_udf_id
        );
        $this->cron_manager->ping($this->getId());

        $total = count($affected_usrs);
        if ($total === 0) {
            return;
        }
        $offset = $offset % $total; // no need to reloop
        $cnt = 0;
        while ($cnt < $offset) {
            array_push($affected_usrs, array_shift($affected_usrs));
            $cnt++;
            $this->cron_manager->ping($this->getId());
        }

        $cnt_handled = 0;
        foreach ($affected_usrs as $affected_usr) {
            if ($max_user_to_handle <= $cnt_handled || $cnt_handled >= $total) {
                break;
            }
            $possible_importable_crses = $this->services->getParticipationsOf(
                $affected_usr
            );

            foreach ($possible_importable_crses as $possible_importable_crs) {
                $this->importParticipation($possible_importable_crs);
                $this->cron_manager->ping($this->getId());
            }
            $cnt_handled++;
            $this->cron_manager->ping($this->getId());
        }
        $offset = ($offset + $cnt_handled) % $total;
        $this->op_limits_db->setOffsetForRequest($offset);
    }

    protected function importParticipation(WBD_RESPONSES\WBDParticipation $participation)
    {
        if ($participation->getCancelled()) {
            return $this->cancelParticipation($participation);
        }
        if ($participation->getBasedOnWBDBuchungsId()) {
            return $this->correctParticipation($participation);
        }
        return $this->justImportParticipation($participation);
    }

    protected function justImportParticipation(WBD_RESPONSES\WBDParticipation $participation)
    {
        $crs_id = $this->response_db->getCourseIdOf($participation->getWBDBuchungsId());

        if (!is_null($crs_id)) {
            return;
        }

        $import_crs_id = $this->response_db->importParticipation($participation);
        $this->response_db->setBookingStatusSuccess(
            $import_crs_id,
            $participation->getUsrId(),
            $participation->getWBDBuchungsId()
        );

        $this->response_db->updateParticipation(
            $import_crs_id,
            $participation->getUsrId(),
            $participation->getWBDBuchungsId(),
            $participation->getStartDate(),
            $participation->getEndDate(),
            $participation->getTimeInMinutes()
        );
    }

    protected function cancelParticipation(WBD_RESPONSES\WBDParticipation $participation)
    {
        if (!$participation->getCancelled()) {
            throw new \LogicException("This participation is not actually cancelled...");
        }

        $crs_id = $this->response_db->getCourseIdOf($participation->getWBDBuchungsId());

        if (is_null($crs_id)) {
            return;
        }

        $this->cancelCourse(
            $crs_id,
            $participation->getUsrId()
        );
    }

    protected function correctParticipation(WBD_RESPONSES\WBDParticipation $participation)
    {
        if (!$participation->getBasedOnWBDBuchungsId()) {
            throw new \LogicException("This participation is not actually a correction...");
        }

        $based_on_crs_id = $this->response_db->getCourseIdOf($participation->getBasedOnWBDBuchungsId());

        if ($based_on_crs_id) {
            $this->cancelCourse(
                $based_on_crs_id,
                $participation->getUsrId()
            );
        }

        $this->justImportParticipation($participation);
    }

    protected function cancelCourse(
        int $crs_id,
        int $usr_id
    ) {
        $this->response_db->cancelParticipation($crs_id, $usr_id);
        $this->response_db->removeFor($crs_id);
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}
