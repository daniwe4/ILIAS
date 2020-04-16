<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDCommunicator\Jobs;

use PHPUnit\Framework\TestCase;
use \CaT\Plugins\WBDCommunicator\Config\UDF;

if (!class_exists(\ilCronJob::class)) {
    require_once "ilCronJob.php";
}
if (!class_exists(\ilCronJobResult::class)) {
    require_once "ilCronJobResult.php";
}

class ilCancelParticipationsJobTest extends TestCase
{
    public function test_run_without_cancel()
    {
        $op_limits_db = $this->createMock(
            \CaT\Plugins\WBDCommunicator\Config\OperationLimits\DB::class
        );

        $op_limits_db->expects($this->never())
            ->method("getLimitForRequest")
        ;

        $op_limits_db->expects($this->never())
            ->method("getOffsetForRequest")
        ;

        $op_limits_db->expects($this->never())
            ->method("setOffsetForRequest")
        ;

        $op_limits_db->expects($this->never())
            ->method("setLimitForRequest")
        ;

        $op_limits_db->expects($this->never())
            ->method("setMaxNumberOfAnnouncemence")
        ;

        $op_limits_db->expects($this->never())
            ->method("getMaxNumberOfAnnouncemence")
        ;

        $op_limits_db->expects($this->never())
            ->method("setMaxNumberOfCancellations")
        ;

        $op_limits_db->expects($this->once())
            ->method("getMaxNumberOfCancellations")
            ->willReturn(10)
        ;

        $cases_db = $this->getMockBuilder(\ILIAS\TMS\WBD\Cases\DB::class)
            ->setMethods(["getParticipationsToCancel"])
            ->getMock()
        ;

        $cases_db->expects($this->once())
            ->method("getParticipationsToCancel")
            ->willReturn([])
        ;

        $wbd_log = $this->createMock(\CaT\WBD\Log\Log::class);

        $wbd_log->expects($this->once())
            ->method("write")
            ->with("No participations found.")
        ;

        $wbd_service = $this->createMock(\CaT\WBD\Services\Services::class);

        $wbd_service->expects($this->never())
            ->method("getParticipationsOf")
        ;

        $wbd_service->expects($this->never())
            ->method("reportParticipation")
        ;

        $wbd_service->expects($this->never())
            ->method("cancelParticipation")
        ;

        $responses_db = $this->getMockBuilder(\ILIAS\TMS\WBD\Responses\DB::class)
            ->getMock()
        ;

        $udf_db = $this->createMock(\CaT\Plugins\WBDCommunicator\Config\UDF\DB::class);

        $udf_1 = new UDF\UDFDefinition("gutberaten_id", 2);
        $udf_2 = new UDF\UDFDefinition("announce_id", 3);

        $udf_db->expects($this->once())
            ->method("getUDFFieldIdForWBDID")
            ->willReturn($udf_1)
        ;

        $udf_db->expects($this->once())
            ->method("getUDFFieldIdForStatus")
            ->willReturn($udf_2)
        ;

        $udf_db->expects($this->never())
            ->method("saveUDFFieldIdForWBDID")
        ;

        $udf_db->expects($this->never())
            ->method("saveUDFFieldIdForStatus")
        ;

        $udf_db->expects($this->never())
            ->method("getUDFDefinitions")
        ;

        $cron_manager = $this->createMock(CronManager::class);

        $job = new ilCancelParticipationsJob(
            $wbd_log,
            $wbd_service,
            function ($c) {
                return $c;
            },
            $cases_db,
            $responses_db,
            $udf_db,
            $op_limits_db,
            $cron_manager
        );
        $job->run();
    }

    public function test_run_no_max_announcements()
    {
        $op_limits_db = $this->createMock(
            \CaT\Plugins\WBDCommunicator\Config\OperationLimits\DB::class
        );

        $op_limits_db->expects($this->never())
            ->method("getLimitForRequest")
        ;

        $op_limits_db->expects($this->never())
            ->method("getOffsetForRequest")
        ;

        $op_limits_db->expects($this->never())
            ->method("setOffsetForRequest")
        ;

        $op_limits_db->expects($this->never())
            ->method("setLimitForRequest")
        ;

        $op_limits_db->expects($this->never())
        ->method("setMaxNumberOfAnnouncemence")
    ;

        $op_limits_db->expects($this->never())
            ->method("getMaxNumberOfAnnouncemence")
        ;

        $op_limits_db->expects($this->never())
            ->method("setMaxNumberOfCancellations")
        ;

        $op_limits_db->expects($this->once())
            ->method("getMaxNumberOfCancellations")
            ->willReturn(0)
        ;

        $cases_db = $this->getMockBuilder(\ILIAS\TMS\WBD\Cases\DB::class)
            ->setMethods(["getParticipationsToCancel"])
            ->getMock()
        ;

        $cases_db->expects($this->never())
            ->method("getParticipationsToCancel")
        ;

        $wbd_log = $this->createMock(\CaT\WBD\Log\Log::class);

        $wbd_log->expects($this->once())
            ->method("write")
            ->with("Limit for max cancellations is 0. No participation was cancelled.")
        ;

        $wbd_service = $this->createMock(\CaT\WBD\Services\Services::class);

        $wbd_service->expects($this->never())
            ->method("getParticipationsOf")
        ;

        $wbd_service->expects($this->never())
            ->method("reportParticipation")
        ;

        $wbd_service->expects($this->never())
            ->method("cancelParticipation")
        ;

        $responses_db = $this->getMockBuilder(\ILIAS\TMS\WBD\Responses\DB::class)
            ->getMock()
        ;

        $udf_db = $this->createMock(\CaT\Plugins\WBDCommunicator\Config\UDF\DB::class);

        $udf_1 = new UDF\UDFDefinition("gutberaten_id", 2);
        $udf_2 = new UDF\UDFDefinition("announce_id", 3);

        $udf_db->expects($this->once())
            ->method("getUDFFieldIdForWBDID")
            ->willReturn($udf_1)
        ;

        $udf_db->expects($this->once())
            ->method("getUDFFieldIdForStatus")
            ->willReturn($udf_2)
        ;

        $udf_db->expects($this->never())
            ->method("saveUDFFieldIdForWBDID")
        ;

        $udf_db->expects($this->never())
            ->method("saveUDFFieldIdForStatus")
        ;

        $udf_db->expects($this->never())
            ->method("getUDFDefinitions")
        ;

        $cron_manager = $this->createMock(CronManager::class);

        $job = new ilCancelParticipationsJob(
            $wbd_log,
            $wbd_service,
            function ($c) {
                return $c;
            },
            $cases_db,
            $responses_db,
            $udf_db,
            $op_limits_db,
            $cron_manager
        );
        $job->run();
    }

    public function test_run_with_report_errored()
    {
        $op_limits_db = $this->createMock(
            \CaT\Plugins\WBDCommunicator\Config\OperationLimits\DB::class
        );

        $op_limits_db->expects($this->never())
            ->method("getLimitForRequest")
        ;

        $op_limits_db->expects($this->never())
            ->method("getOffsetForRequest")
        ;

        $op_limits_db->expects($this->never())
            ->method("setOffsetForRequest")
        ;

        $op_limits_db->expects($this->never())
            ->method("setLimitForRequest")
        ;

        $op_limits_db->expects($this->never())
            ->method("setMaxNumberOfAnnouncemence")
        ;

        $op_limits_db->expects($this->never())
            ->method("getMaxNumberOfAnnouncemence")
        ;

        $op_limits_db->expects($this->never())
            ->method("setMaxNumberOfCancellations")
        ;

        $op_limits_db->expects($this->once())
            ->method("getMaxNumberOfCancellations")
            ->willReturn(10)
        ;

        $crs_id = 10;
        $usr_id = 15;
        $title = "Best course ever.";
        $minutes = 20;
        $wbd_booking_id = "2019-06-18-565";
        $gutberaten_id = "20180327-100234-05";
        $participation = new \CaT\WBD\Cases\CancelParticipation(
            $crs_id,
            $usr_id,
            $title,
            $minutes,
            $wbd_booking_id,
            $gutberaten_id
        );

        $result = new \CaT\WBD\Responses\Cancellation(
            $participation,
            "",
            "There was an error."
        );

        $cases_db = $this->getMockBuilder(\ILIAS\TMS\WBD\Cases\DB::class)
            ->setMethods(["getParticipationsToCancel"])
            ->getMock()
        ;

        $cases_db->expects($this->once())
            ->method("getParticipationsToCancel")
            ->willReturn([$participation])
        ;

        $wbd_log = $this->createMock(\CaT\WBD\Log\Log::class);

        $wbd_log->expects($this->once())
            ->method("write")
            ->with("There was an error.")
        ;

        $wbd_service = $this->createMock(\CaT\WBD\Services\Services::class);

        $wbd_service->expects($this->never())
            ->method("getParticipationsOf")
        ;

        $wbd_service->expects($this->never())
            ->method("reportParticipation")
        ;

        $wbd_service->expects($this->once())
            ->method("cancelParticipation")
            ->with($participation)
            ->willReturn($result)
        ;

        $responses_db = $this->getMockBuilder(\ILIAS\TMS\WBD\Responses\DB::class)
            ->setMethods(
                [
                    "setAsReported",
                    "setBookingStatusSuccess",
                    "announceWBDBookingId",
                    "updateParticipation",
                    "removeWBDBookingId",
                    "setParticipationCancelled"
                ]
            )
            ->getMock()
        ;

        $responses_db->expects($this->never())
            ->method("setAsReported")
        ;

        $responses_db->expects($this->never())
            ->method("setBookingStatusSuccess")
        ;

        $responses_db->expects($this->never())
            ->method("announceWBDBookingId")
        ;
        $responses_db->expects($this->never())
            ->method("updateParticipation")
        ;
        $responses_db->expects($this->never())
            ->method("removeWBDBookingId")
        ;
        $responses_db->expects($this->never())
            ->method("setParticipationCancelled")
        ;

        $udf_db = $this->createMock(\CaT\Plugins\WBDCommunicator\Config\UDF\DB::class);

        $udf_1 = new UDF\UDFDefinition("gutberaten_id", 2);
        $udf_2 = new UDF\UDFDefinition("announce_id", 3);

        $udf_db->expects($this->once())
            ->method("getUDFFieldIdForWBDID")
            ->willReturn($udf_1)
        ;

        $udf_db->expects($this->once())
            ->method("getUDFFieldIdForStatus")
            ->willReturn($udf_2)
        ;

        $udf_db->expects($this->never())
            ->method("saveUDFFieldIdForWBDID")
        ;

        $udf_db->expects($this->never())
            ->method("saveUDFFieldIdForStatus")
        ;

        $udf_db->expects($this->never())
            ->method("getUDFDefinitions")
        ;

        $cron_manager = $this->createMock(CronManager::class);

        $job = new ilCancelParticipationsJob(
            $wbd_log,
            $wbd_service,
            function ($c) {
                return $c;
            },
            $cases_db,
            $responses_db,
            $udf_db,
            $op_limits_db,
            $cron_manager
        );
        $job->run();
    }

    public function test_run_with_cancellation()
    {
        $op_limits_db = $this->createMock(
            \CaT\Plugins\WBDCommunicator\Config\OperationLimits\DB::class
        );

        $op_limits_db->expects($this->never())
            ->method("getLimitForRequest")
        ;

        $op_limits_db->expects($this->never())
            ->method("getOffsetForRequest")
        ;

        $op_limits_db->expects($this->never())
            ->method("setOffsetForRequest")
        ;

        $op_limits_db->expects($this->never())
            ->method("setLimitForRequest")
        ;

        $op_limits_db->expects($this->never())
            ->method("setMaxNumberOfAnnouncemence")
        ;

        $op_limits_db->expects($this->never())
            ->method("getMaxNumberOfAnnouncemence")
        ;

        $op_limits_db->expects($this->never())
            ->method("setMaxNumberOfCancellations")
        ;

        $op_limits_db->expects($this->once())
            ->method("getMaxNumberOfCancellations")
            ->willReturn(10)
        ;

        $crs_id = 10;
        $usr_id = 15;
        $title = "Best course ever.";
        $minutes = 20;
        $wbd_booking_id = "2019-06-18-565";
        $gutberaten_id = "20180327-100234-05";
        $participation = new \CaT\WBD\Cases\CancelParticipation(
            $crs_id,
            $usr_id,
            $title,
            $minutes,
            $wbd_booking_id,
            $gutberaten_id
        );

        $result = new \CaT\WBD\Responses\Cancellation(
            $participation,
            "2020-01-13-565",
            null
        );

        $cases_db = $this->getMockBuilder(\ILIAS\TMS\WBD\Cases\DB::class)
            ->setMethods(["getParticipationsToCancel"])
            ->getMock()
        ;

        $cases_db->expects($this->once())
            ->method("getParticipationsToCancel")
            ->willReturn([$participation])
        ;

        $wbd_log = $this->createMock(\CaT\WBD\Log\Log::class);

        $wbd_log->expects($this->never())
            ->method("write")
        ;

        $wbd_service = $this->createMock(\CaT\WBD\Services\Services::class);

        $wbd_service->expects($this->never())
            ->method("getParticipationsOf")
        ;

        $wbd_service->expects($this->never())
            ->method("reportParticipation")
        ;

        $wbd_service->expects($this->once())
            ->method("cancelParticipation")
            ->with($participation)
            ->willReturn($result)
        ;

        $responses_db = $this->getMockBuilder(\ILIAS\TMS\WBD\Responses\DB::class)
            ->setMethods(
                [
                    "setAsReported",
                    "setBookingStatusSuccess",
                    "announceWBDBookingId",
                    "updateParticipation",
                    "removeWBDBookingId",
                    "setParticipationCancelled",
                    "removeCurrentBookingStatusBy",
                    "removeAnnouncedCase"
                ]
            )
            ->getMock()
        ;

        $responses_db->expects($this->never())
            ->method("setBookingStatusSuccess")
        ;

        $responses_db->expects($this->never())
            ->method("announceWBDBookingId")
        ;

        $responses_db->expects($this->never())
            ->method("updateParticipation")
        ;

        $responses_db->expects($this->once())
            ->method("setParticipationCancelled")
            ->with($crs_id, $usr_id, "2020-01-13-565")
        ;

        $responses_db->expects($this->once())
            ->method("removeWBDBookingId")
            ->with($crs_id, $usr_id, $wbd_booking_id)
        ;

        $responses_db->expects($this->once())
            ->method("removeAnnouncedCase")
            ->with($crs_id, $usr_id)
        ;

        $responses_db->expects($this->once())
            ->method("removeCurrentBookingStatusBy")
            ->with($crs_id, $usr_id)
        ;

        $udf_db = $this->createMock(\CaT\Plugins\WBDCommunicator\Config\UDF\DB::class);

        $udf_1 = new UDF\UDFDefinition("gutberaten_id", 2);
        $udf_2 = new UDF\UDFDefinition("announce_id", 3);

        $udf_db->expects($this->once())
            ->method("getUDFFieldIdForWBDID")
            ->willReturn($udf_1)
        ;

        $udf_db->expects($this->once())
            ->method("getUDFFieldIdForStatus")
            ->willReturn($udf_2)
        ;

        $udf_db->expects($this->never())
            ->method("saveUDFFieldIdForWBDID")
        ;

        $udf_db->expects($this->never())
            ->method("saveUDFFieldIdForStatus")
        ;

        $udf_db->expects($this->never())
            ->method("getUDFDefinitions")
        ;

        $cron_manager = $this->createMock(CronManager::class);

        $job = new ilCancelParticipationsJob(
            $wbd_log,
            $wbd_service,
            function ($c) {
                return $c;
            },
            $cases_db,
            $responses_db,
            $udf_db,
            $op_limits_db,
            $cron_manager
        );
        $job->run();
    }
}
