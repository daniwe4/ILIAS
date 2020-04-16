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

class ilRequestParticipationsJobTest extends TestCase
{
    public function test_run_without_request_cases()
    {
        $op_limits_db = $this->createMock(
            \CaT\Plugins\WBDCommunicator\Config\OperationLimits\DB::class
        );

        $op_limits_db->expects($this->never())
            ->method("getMaxNumberOfCancellations")
        ;

        $op_limits_db->expects($this->never())
            ->method("setMaxNumberOfCancellations")
        ;

        $op_limits_db->expects($this->once())
            ->method("getLimitForRequest")
            ->with()
            ->willReturn(10);

        $op_limits_db->expects($this->once())
            ->method("getOffsetForRequest")
            ->with()
            ->willReturn(0);

        $op_limits_db->expects($this->never())
            ->method("setOffsetForRequest")
        ;

        $op_limits_db->expects($this->never())
            ->method("setLimitForRequest")
        ;

        $op_limits_db->expects($this->never())
            ->method("getMaxNumberOfAnnouncemence")
        ;

        $op_limits_db->expects($this->never())
            ->method("setMaxNumberOfAnnouncemence")
        ;

        $cases_db = $this->getMockBuilder(\ILIAS\TMS\WBD\Cases\DB::class)
            ->setMethods(["getIdsForParticipationRequest"])
            ->getMock()
        ;

        $cases_db->expects($this->once())
            ->method("getIdsForParticipationRequest")
            ->with(2, 3)
            ->willReturn([])
        ;

        $wbd_log = $this->createMock(\CaT\WBD\Log\Log::class);

        $wbd_log->expects($this->never())
            ->method("write")
        ;

        $wbd_service = $this->createMock(\CaT\WBD\Services\Services::class);

        $wbd_service->expects($this->never())
            ->method("reportParticipation")
        ;

        $wbd_service->expects($this->never())
            ->method("cancelParticipation")
        ;

        $wbd_service->expects($this->never())
            ->method("getParticipationsOf");

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

        $job = new ilRequestParticipationsJob(
            $wbd_log,
            $wbd_service,
            function ($c) {
                return $c;
            },
            $cases_db,
            $responses_db,
            $op_limits_db,
            $udf_db,
            $cron_manager
        );
        $job->run();
    }

    public function test_run_with_cancel_request()
    {
        $participation_request = new \CaT\WBD\Cases\RequestParticipations(10, "20180327-100234-05");

        $usr_id = 10;
        $wbd_buchungs_id = "2019-06-18-565";
        $gutberaten_id = "20180327-100234-05";
        $title = "Beste ever.";
        $time_in_minutes = 20;
        $start_date = new \DateTime();
        $end_date = new \DateTime();
        $type = "001";
        $topic = "002";
        $organisation = "CaT";
        $internal_id = "my_internal";
        $cancelled = true;
        $correction = false;
        $based_on_wbd_buchungs_id = "";
        $wbd_participation = new\CaT\WBD\Responses\WBDParticipation(
            $usr_id,
            $wbd_buchungs_id,
            $gutberaten_id,
            $title,
            $time_in_minutes,
            $start_date,
            $end_date,
            $type,
            $topic,
            $organisation,
            $internal_id,
            $cancelled,
            $correction,
            $based_on_wbd_buchungs_id
        );

        $op_limits_db = $this->createMock(
            \CaT\Plugins\WBDCommunicator\Config\OperationLimits\DB::class
        );

        $op_limits_db->expects($this->never())
            ->method("getMaxNumberOfCancellations")
        ;

        $op_limits_db->expects($this->never())
            ->method("setMaxNumberOfCancellations")
        ;

        $op_limits_db->expects($this->once())
            ->method("getLimitForRequest")
            ->with()
            ->willReturn(10);

        $op_limits_db->expects($this->once())
            ->method("getOffsetForRequest")
            ->with()
            ->willReturn(0);

        $offset = (0 + 1) % 1;
        $op_limits_db->expects($this->once())
            ->method("setOffsetForRequest")
            ->with($offset)
        ;

        $op_limits_db->expects($this->never())
            ->method("setLimitForRequest")
        ;

        $op_limits_db->expects($this->never())
            ->method("getMaxNumberOfAnnouncemence")
        ;

        $op_limits_db->expects($this->never())
            ->method("setMaxNumberOfAnnouncemence")
        ;

        $cases_db = $this->getMockBuilder(\ILIAS\TMS\WBD\Cases\DB::class)
            ->setMethods(["getIdsForParticipationRequest"])
            ->getMock()
        ;

        $cases_db->expects($this->once())
            ->method("getIdsForParticipationRequest")
            ->with(2, 3)
            ->willReturn([$participation_request])
        ;

        $wbd_log = $this->createMock(\CaT\WBD\Log\Log::class);

        $wbd_log->expects($this->never())
            ->method("write")
        ;

        $wbd_service = $this->createMock(\CaT\WBD\Services\Services::class);

        $wbd_service->expects($this->never())
            ->method("reportParticipation")
        ;

        $wbd_service->expects($this->never())
            ->method("cancelParticipation")
        ;

        $wbd_service->expects($this->once())
            ->method("getParticipationsOf")
            ->with($participation_request)
            ->willReturn([$wbd_participation])
        ;

        $responses_db = $this->getMockBuilder(\ILIAS\TMS\WBD\Responses\DB::class)
            ->setMethods(
                [
                    "getCourseIdOf",
                    "cancelParticipation",
                    "importParticipation",
                    "setBookingStatusSuccess",
                    "updateParticipation",
                    "removeFor"
                ]
            )
            ->getMock()
        ;

        $responses_db->expects($this->exactly(1))
            ->method("getCourseIdOf")
            ->withConsecutive(
                ["2019-06-18-565"]
            )
            ->willReturn(365)
        ;

        $responses_db->expects($this->once())
            ->method("removeFor")
            ->with(365);

        $responses_db->expects($this->never())
            ->method("importParticipation")
        ;

        $responses_db->expects($this->never())
            ->method("setBookingStatusSuccess")
        ;

        $responses_db->expects($this->never())
            ->method("updateParticipation")
        ;

        $responses_db->expects($this->once())
            ->method("cancelParticipation")
            ->with(365, 10)
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

        $job = new ilRequestParticipationsJob(
            $wbd_log,
            $wbd_service,
            function ($c) {
                return $c;
            },
            $cases_db,
            $responses_db,
            $op_limits_db,
            $udf_db,
            $cron_manager
        );
        $job->run();
    }

    public function test_run_with_correction_request()
    {
        $participation_request = new \CaT\WBD\Cases\RequestParticipations(10, "20180327-100234-05");

        $usr_id = 10;
        $wbd_booking_id = "2019-06-18-565";
        $gutberaten_id = "20180327-100234-05";
        $title = "Beste ever.";
        $time_in_minutes = 480;
        $start_date = new \DateTime();
        $end_date = new \DateTime();
        $type = "001";
        $topic = "002";
        $organisation = "CaT";
        $internal_id = "my_internal";
        $cancelled = false;
        $correction = true;
        $based_on_wbd_buchungs_id = "2018-06-18-565";
        $crs_id = 365;
        $based_on_crs_id = 366;
        $wbd_participation = new\CaT\WBD\Responses\WBDParticipation(
            $usr_id,
            $wbd_booking_id,
            $gutberaten_id,
            $title,
            $time_in_minutes,
            $start_date,
            $end_date,
            $type,
            $topic,
            $organisation,
            $internal_id,
            $cancelled,
            $correction,
            $based_on_wbd_buchungs_id
        );

        $op_limits_db = $this->createMock(
            \CaT\Plugins\WBDCommunicator\Config\OperationLimits\DB::class
        );

        $op_limits_db->expects($this->never())
            ->method("getMaxNumberOfCancellations")
        ;

        $op_limits_db->expects($this->never())
            ->method("setMaxNumberOfCancellations")
        ;

        $op_limits_db->expects($this->once())
            ->method("getLimitForRequest")
            ->with()
            ->willReturn(10);

        $op_limits_db->expects($this->once())
            ->method("getOffsetForRequest")
            ->with()
            ->willReturn(0);

        $offset = (0 + 1) % 1;
        $op_limits_db->expects($this->once())
            ->method("setOffsetForRequest")
            ->with($offset)
        ;

        $op_limits_db->expects($this->never())
            ->method("setLimitForRequest")
        ;

        $op_limits_db->expects($this->never())
            ->method("getMaxNumberOfAnnouncemence")
        ;

        $op_limits_db->expects($this->never())
            ->method("setMaxNumberOfAnnouncemence")
        ;

        $cases_db = $this->getMockBuilder(\ILIAS\TMS\WBD\Cases\DB::class)
            ->setMethods(["getIdsForParticipationRequest"])
            ->getMock()
        ;

        $cases_db->expects($this->once())
            ->method("getIdsForParticipationRequest")
            ->with(2, 3)
            ->willReturn([$participation_request])
        ;

        $wbd_log = $this->createMock(\CaT\WBD\Log\Log::class);

        $wbd_log->expects($this->never())
            ->method("write")
        ;

        $wbd_service = $this->createMock(\CaT\WBD\Services\Services::class);

        $wbd_service->expects($this->never())
            ->method("reportParticipation")
        ;

        $wbd_service->expects($this->never())
            ->method("cancelParticipation")
        ;

        $wbd_service->expects($this->once())
            ->method("getParticipationsOf")
            ->with($participation_request)
            ->willReturn([$wbd_participation])
        ;

        $responses_db = $this->getMockBuilder(\ILIAS\TMS\WBD\Responses\DB::class)
            ->setMethods(
                [
                    "getCourseIdOf",
                    "cancelParticipation",
                    "importParticipation",
                    "setBookingStatusSuccess",
                    "updateParticipation",
                    "removeFor"
                ]
            )
            ->getMock()
        ;

        $responses_db->expects($this->exactly(2))
            ->method("getCourseIdOf")
            ->withConsecutive(
                [$based_on_wbd_buchungs_id],
                [$wbd_booking_id]
            )
            ->will($this->onConsecutiveCalls($based_on_crs_id, null))
        ;

        $responses_db->expects($this->once())
            ->method("importParticipation")
            ->willReturn($crs_id)
        ;

        $responses_db->expects($this->once())
            ->method("setBookingStatusSuccess")
            ->with($crs_id, $usr_id, $wbd_booking_id)
        ;

        $responses_db->expects($this->once())
            ->method("updateParticipation")
            ->with($crs_id, $usr_id, $wbd_booking_id, $start_date, $end_date, $time_in_minutes)
        ;

        $responses_db->expects($this->once())
            ->method("cancelParticipation")
            ->with($based_on_crs_id, $usr_id)
        ;


        $responses_db->expects($this->once())
            ->method("removeFor")
            ->with($based_on_crs_id);


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

        $job = new ilRequestParticipationsJob(
            $wbd_log,
            $wbd_service,
            function ($c) {
                return $c;
            },
            $cases_db,
            $responses_db,
            $op_limits_db,
            $udf_db,
            $cron_manager
        );
        $job->run();
    }

    public function test_run_only_import()
    {
        $participation_request = new \CaT\WBD\Cases\RequestParticipations(10, "20180327-100234-05");

        $usr_id = 10;
        $wbd_booking_id = "2019-06-18-565";
        $gutberaten_id = "20180327-100234-05";
        $title = "Beste ever.";
        $time_in_minutes = 480;
        $start_date = new \DateTime();
        $end_date = new \DateTime();
        $type = "001";
        $topic = "002";
        $organisation = "CaT";
        $internal_id = "my_internal";
        $cancelled = false;
        $correction = false;
        $based_on_wbd_buchungs_id = null;
        $crs_id = 366;
        $wbd_participation = new\CaT\WBD\Responses\WBDParticipation(
            $usr_id,
            $wbd_booking_id,
            $gutberaten_id,
            $title,
            $time_in_minutes,
            $start_date,
            $end_date,
            $type,
            $topic,
            $organisation,
            $internal_id,
            $cancelled,
            $correction,
            $based_on_wbd_buchungs_id
        );

        $op_limits_db = $this->createMock(
            \CaT\Plugins\WBDCommunicator\Config\OperationLimits\DB::class
        );

        $op_limits_db->expects($this->never())
            ->method("getMaxNumberOfCancellations")
        ;

        $op_limits_db->expects($this->never())
            ->method("setMaxNumberOfCancellations")
        ;

        $op_limits_db->expects($this->once())
            ->method("getLimitForRequest")
            ->with()
            ->willReturn(10);

        $op_limits_db->expects($this->once())
            ->method("getOffsetForRequest")
            ->with()
            ->willReturn(0);

        $offset = (0 + 1) % 1;
        $op_limits_db->expects($this->once())
            ->method("setOffsetForRequest")
            ->with($offset)
        ;

        $op_limits_db->expects($this->never())
            ->method("setLimitForRequest")
        ;

        $op_limits_db->expects($this->never())
            ->method("getMaxNumberOfAnnouncemence")
        ;

        $op_limits_db->expects($this->never())
            ->method("setMaxNumberOfAnnouncemence")
        ;

        $cases_db = $this->getMockBuilder(\ILIAS\TMS\WBD\Cases\DB::class)
            ->setMethods(["getIdsForParticipationRequest"])
            ->getMock()
        ;

        $cases_db->expects($this->once())
            ->method("getIdsForParticipationRequest")
            ->with(2, 3)
            ->willReturn([$participation_request])
        ;

        $wbd_log = $this->createMock(\CaT\WBD\Log\Log::class);

        $wbd_log->expects($this->never())
            ->method("write")
        ;

        $wbd_service = $this->createMock(\CaT\WBD\Services\Services::class);

        $wbd_service->expects($this->never())
            ->method("reportParticipation")
        ;

        $wbd_service->expects($this->never())
            ->method("cancelParticipation")
        ;

        $wbd_service->expects($this->once())
            ->method("getParticipationsOf")
            ->with($participation_request)
            ->willReturn([$wbd_participation])
        ;

        $responses_db = $this->getMockBuilder(\ILIAS\TMS\WBD\Responses\DB::class)
            ->setMethods(
                [
                    "getCourseIdOf",
                    "cancelParticipation",
                    "importParticipation",
                    "setBookingStatusSuccess",
                    "updateParticipation"
                ]
            )
            ->getMock()
        ;

        $responses_db->expects($this->once())
            ->method("getCourseIdOf")
            ->with($wbd_booking_id)
            ->willReturn(null)
        ;

        $responses_db->expects($this->once())
            ->method("importParticipation")
            ->willReturn($crs_id)
        ;

        $responses_db->expects($this->once())
            ->method("setBookingStatusSuccess")
            ->with($crs_id, $usr_id, $wbd_booking_id)
        ;

        $responses_db->expects($this->once())
            ->method("updateParticipation")
            ->with($crs_id, $usr_id, $wbd_booking_id, $start_date, $end_date, $time_in_minutes)
        ;

        $responses_db->expects($this->never())
            ->method("cancelParticipation")
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

        $job = new ilRequestParticipationsJob(
            $wbd_log,
            $wbd_service,
            function ($c) {
                return $c;
            },
            $cases_db,
            $responses_db,
            $op_limits_db,
            $udf_db,
            $cron_manager
        );
        $job->run();
        ;
    }

    public function test_run_import_without_based_on_id()
    {
        $participation_request = new \CaT\WBD\Cases\RequestParticipations(10, "20180327-100234-05");

        $usr_id = 10;
        $wbd_booking_id = "2019-06-18-565";
        $gutberaten_id = "20180327-100234-05";
        $title = "Beste ever.";
        $time_in_minutes = 480;
        $start_date = new \DateTime();
        $end_date = new \DateTime();
        $type = "001";
        $topic = "002";
        $organisation = "CaT";
        $internal_id = "my_internal";
        $cancelled = false;
        $correction = false;
        $based_on_wbd_buchungs_id = null;
        $crs_id = 366;
        $wbd_participation = new\CaT\WBD\Responses\WBDParticipation(
            $usr_id,
            $wbd_booking_id,
            $gutberaten_id,
            $title,
            $time_in_minutes,
            $start_date,
            $end_date,
            $type,
            $topic,
            $organisation,
            $internal_id,
            $cancelled,
            $correction,
            $based_on_wbd_buchungs_id
        );

        $op_limits_db = $this->createMock(
            \CaT\Plugins\WBDCommunicator\Config\OperationLimits\DB::class
        );

        $op_limits_db->expects($this->never())
            ->method("getMaxNumberOfCancellations")
        ;

        $op_limits_db->expects($this->never())
            ->method("setMaxNumberOfCancellations")
        ;

        $op_limits_db->expects($this->once())
            ->method("getLimitForRequest")
            ->with()
            ->willReturn(10);

        $op_limits_db->expects($this->once())
            ->method("getOffsetForRequest")
            ->with()
            ->willReturn(0);

        $offset = (0 + 1) % 1;
        $op_limits_db->expects($this->once())
            ->method("setOffsetForRequest")
            ->with($offset)
        ;

        $op_limits_db->expects($this->never())
            ->method("setLimitForRequest")
        ;

        $op_limits_db->expects($this->never())
            ->method("getMaxNumberOfAnnouncemence")
        ;

        $op_limits_db->expects($this->never())
            ->method("setMaxNumberOfAnnouncemence")
        ;

        $cases_db = $this->getMockBuilder(\ILIAS\TMS\WBD\Cases\DB::class)
            ->setMethods(["getIdsForParticipationRequest"])
            ->getMock()
        ;

        $cases_db->expects($this->once())
            ->method("getIdsForParticipationRequest")
            ->with(2, 3)
            ->willReturn([$participation_request])
        ;

        $wbd_log = $this->createMock(\CaT\WBD\Log\Log::class);

        $wbd_log->expects($this->never())
            ->method("write")
        ;

        $wbd_service = $this->createMock(\CaT\WBD\Services\Services::class);

        $wbd_service->expects($this->never())
            ->method("reportParticipation")
        ;

        $wbd_service->expects($this->never())
            ->method("cancelParticipation")
        ;

        $wbd_service->expects($this->once())
            ->method("getParticipationsOf")
            ->with($participation_request)
            ->willReturn([$wbd_participation])
        ;

        $responses_db = $this->getMockBuilder(\ILIAS\TMS\WBD\Responses\DB::class)
            ->setMethods(
                [
                    "getCourseIdOf",
                    "cancelParticipation",
                    "importParticipation",
                    "setBookingStatusSuccess",
                    "updateParticipation"
                ]
            )
            ->getMock()
        ;

        $responses_db->expects($this->once())
            ->method("getCourseIdOf")
            ->withConsecutive(
                [$wbd_booking_id]
            )
            ->will($this->onConsecutiveCalls(null, $crs_id))
        ;

        $responses_db->expects($this->once())
            ->method("importParticipation")
            ->willReturn($crs_id)
        ;

        $responses_db->expects($this->once())
            ->method("setBookingStatusSuccess")
            ->with($crs_id, $usr_id, $wbd_booking_id)
        ;

        $responses_db->expects($this->once())
            ->method("updateParticipation")
            ->with($crs_id, $usr_id, $wbd_booking_id, $start_date, $end_date, $time_in_minutes)
        ;

        $responses_db->expects($this->never())
            ->method("cancelParticipation")
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

        $job = new ilRequestParticipationsJob(
            $wbd_log,
            $wbd_service,
            function ($c) {
                return $c;
            },
            $cases_db,
            $responses_db,
            $op_limits_db,
            $udf_db,
            $cron_manager
        );
        $job->run();
        ;
    }
}
