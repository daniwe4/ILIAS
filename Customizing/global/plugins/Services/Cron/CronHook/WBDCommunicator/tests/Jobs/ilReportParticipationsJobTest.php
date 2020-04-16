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

class ilReportParticipationsJobTest extends TestCase
{
    public function test_run_without_reports()
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

        $op_limits_db->expects($this->once())
            ->method("getMaxNumberOfAnnouncemence")
            ->willReturn(10)
        ;

        $cases_db = $this->getMockBuilder(\ILIAS\TMS\WBD\Cases\DB::class)
            ->setMethods(["getParticipationsToReport"])
            ->getMock()
        ;

        $cases_db->expects($this->once())
            ->method("getParticipationsToReport")
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
            ->method("cancelParticipation")
        ;

        $wbd_service->expects($this->never())
            ->method("reportParticipation")
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

        $job = new ilReportParticipationsJob(
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
            ->method("getMaxNumberOfCancellations")
        ;

        $op_limits_db->expects($this->never())
            ->method("setMaxNumberOfCancellations")
        ;

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

        $op_limits_db->expects($this->once())
            ->method("getMaxNumberOfAnnouncemence")
            ->willReturn(0)
        ;

        $cases_db = $this->getMockBuilder(\ILIAS\TMS\WBD\Cases\DB::class)
            ->setMethods(["getParticipationsToReport"])
            ->getMock()
        ;

        $cases_db->expects($this->never())
            ->method("getParticipationsToReport")
        ;

        $wbd_log = $this->createMock(\CaT\WBD\Log\Log::class);

        $wbd_log->expects($this->once())
            ->method("write")
            ->with("Limit for max announcements is 0. No participation was reported.")
        ;

        $wbd_service = $this->createMock(\CaT\WBD\Services\Services::class);

        $wbd_service->expects($this->never())
            ->method("getParticipationsOf")
        ;

        $wbd_service->expects($this->never())
            ->method("cancelParticipation")
        ;

        $wbd_service->expects($this->never())
            ->method("reportParticipation")
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

        $job = new ilReportParticipationsJob(
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
            ->method("getMaxNumberOfCancellations")
        ;

        $op_limits_db->expects($this->never())
            ->method("setMaxNumberOfCancellations")
        ;

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

        $op_limits_db->expects($this->once())
            ->method("getMaxNumberOfAnnouncemence")
            ->willReturn(10)
        ;

        $crs_id = 10;
        $usr_id = 20;
        $gutberaten_id = "20180327-100234-05";
        $title = "Greate Course";
        $time_in_minutes = 20;
        $start_date = new \DateTime();
        $end_date = new \DateTime();
        $type = "001";
        $topic = "002";
        $internal_id = "my_course";
        $contact_title = "Mr";
        $contact_firstname = "Contact";
        $contact_lastname = "name";
        $contact_telno = "0221 5638547";
        $contact_email = "contact@mail.com";

        $participation = new \CaT\WBD\Cases\ReportParticipation(
            $crs_id,
            $usr_id,
            $gutberaten_id,
            $title,
            $time_in_minutes,
            $start_date,
            $end_date,
            $type,
            $topic,
            $internal_id,
            $contact_title,
            $contact_firstname,
            $contact_lastname,
            $contact_telno,
            $contact_email
        );

        $message = "There was an error.";
        $result = new \CaT\WBD\Responses\Participation($participation, "", $message);

        $cases_db = $this->getMockBuilder(\ILIAS\TMS\WBD\Cases\DB::class)
            ->setMethods(["getParticipationsToReport"])
            ->getMock()
        ;

        $cases_db->expects($this->once())
            ->method("getParticipationsToReport")
            ->willReturn([$participation])
        ;

        $wbd_log = $this->createMock(\CaT\WBD\Log\Log::class);

        $wbd_log->expects($this->once())
            ->method("write")
            ->with($message)
        ;

        $wbd_service = $this->createMock(\CaT\WBD\Services\Services::class);

        $wbd_service->expects($this->never())
            ->method("getParticipationsOf")
        ;

        $wbd_service->expects($this->never())
            ->method("cancelParticipation")
        ;

        $wbd_service->expects($this->once())
            ->method("reportParticipation")
            ->with($participation)
            ->willReturn($result)
        ;

        $responses_db = $this->getMockBuilder(\ILIAS\TMS\WBD\Responses\DB::class)
            ->setMethods(
                [
                    "setAsReported",
                    "setBookingStatusSuccess",
                    "announceWBDBookingId",
                    "updateParticipation"
                ]
            )
            ->getMock()
        ;

        $responses_db->expects($this->never())
            ->method("setAsReported")
        ;

        $responses_db->expects($this->never())
            ->method("announceWBDBookingId")
        ;

        $responses_db->expects($this->never())
            ->method("updateParticipation")
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

        $job = new ilReportParticipationsJob(
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

    public function test_run_with_report()
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

        $op_limits_db->expects($this->once())
            ->method("getMaxNumberOfAnnouncemence")
            ->willReturn(10)
        ;

        $crs_id = 10;
        $usr_id = 20;
        $gutberaten_id = "20180327-100234-05";
        $title = "Greate Course";
        $time_in_minutes = 20;
        $start_date = new \DateTime();
        $end_date = new \DateTime();
        $type = "001";
        $topic = "002";
        $internal_id = "my_course";
        $contact_title = "Mr";
        $contact_firstname = "Contact";
        $contact_lastname = "name";
        $contact_telno = "0221 5638547";
        $contact_email = "contact@mail.com";
        $participation = new \CaT\WBD\Cases\ReportParticipation(
            $crs_id,
            $usr_id,
            $gutberaten_id,
            $title,
            $time_in_minutes,
            $start_date,
            $end_date,
            $type,
            $topic,
            $internal_id,
            $contact_title,
            $contact_firstname,
            $contact_lastname,
            $contact_telno,
            $contact_email
        );

        $wbd_booking_id = "2019-06-18-565";
        $result = new \CaT\WBD\Responses\Participation($participation, $wbd_booking_id, null);

        $cases_db = $this->getMockBuilder(\ILIAS\TMS\WBD\Cases\DB::class)
            ->setMethods(["getParticipationsToReport"])
            ->getMock()
        ;

        $cases_db->expects($this->once())
            ->method("getParticipationsToReport")
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
            ->method("cancelParticipation")
        ;

        $wbd_service->expects($this->once())
            ->method("reportParticipation")
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
                    "saveAnnouncedValues",
                    "removeCurrentBookingStatusBy"
                ]
            )
            ->getMock()
        ;

        $responses_db->expects($this->once())
            ->method("setBookingStatusSuccess")
            ->with($crs_id, $usr_id, $wbd_booking_id)
        ;

        $responses_db->expects($this->once())
            ->method("announceWBDBookingId")
            ->with($crs_id, $usr_id, $wbd_booking_id)
        ;

        $responses_db->expects($this->once())
            ->method("updateParticipation")
            ->with($crs_id, $usr_id, $wbd_booking_id, $start_date, $end_date, $time_in_minutes)
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

        $job = new ilReportParticipationsJob(
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
