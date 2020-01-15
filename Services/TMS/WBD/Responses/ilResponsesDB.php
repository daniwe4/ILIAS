<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

use ILIAS\TMS\WBD\Responses\DB;
use CaT\WBD\Responses as WBD_RESPONSE;

class ilResponsesDB implements DB
{
    const FIELD_USR_ID = 'usr_id';
    const FIELD_CRS_ID = 'crs_id';
    const FIELD_WBD_BOOKING_ID = 'wbd_booking_id';
    const FIELD_WBD_ANNOUNCEMENT_STATUS = 'wbd_booking_status';

    const TABLE_ANNOUNCEMENT_STATUS = 'xwbd_book_status';
    const TABLE_ANNOUNCED_CASES = 'xwbd_announced_cases';
    const TABLE_IMPORTED_COURSES = 'xwbd_imported_courses';
    const TABLE_REPORTED_CRS_VALUES = 'xwbd_report_crs_values';

    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var \ilAppEventHandler
     */
    protected $eventhandler;

    public function __construct(\ilDBInterface $db, \ilAppEventHandler $eventhandler)
    {
        $this->db = $db;
        $this->eventhandler = $eventhandler;
    }

    public function cancelParticipation(int $crs_id, int $usr_id)
    {
        $this->raise(
            'stornoParticipationWBD',
            [	'crs_id' => $crs_id,
                'usr_id' => $usr_id,
            ]
        );
    }

    public function importParticipation(WBD_RESPONSE\WBDParticipation $participation) : int
    {
        $crs_id = -(int) $this->db->nextId(self::TABLE_IMPORTED_COURSES);
        $payload = [
            'crs_id' => $crs_id,
            'begin_date' => $participation->getStartDate()->format('Y-m-d'),
            'end_date' => $participation->getEndDate()->format('Y-m-d'),
            'provider' => $participation->getOrganisation(),
            'idd_learning_time' => $participation->getTimeInMinutes(),
            'wbd_learning_type' => $participation->getType(),
            'wbd_learning_content' => $participation->getTopic(),
            'title' => $participation->getTitle()
        ];

        $this->db->insert(
            self::TABLE_IMPORTED_COURSES,
            [
                'crs_id' => ['integer',$payload['crs_id']],
                'begin_date' => ['date',$payload['begin_date']],
                'end_date' => ['date',$payload['end_date']],
                'idd_learning_time' => ['integer',$payload['idd_learning_time']],
                'wbd_learning_type' => ['text',$payload['wbd_learning_type']],
                'wbd_learning_content' => ['text',$payload['wbd_learning_content']],
                'title' => ['text',$payload['title']]
            ]
        );

        $this->raise('importCourseWBD', $payload);
        return $crs_id;
    }

    /**
     * @inheritDoc
     */
    public function updateParticipation(
        int $crs_id,
        int $usr_id,
        string $wbd_booking_id,
        \DateTime $start_date,
        \DateTime $end_date,
        int $minutes
    ) {
        $this->raise(
            'importParticipationWBD',
            [	'crs_id' => $crs_id,
                'usr_id' => $usr_id,
                'booking_date' => $start_date->format('Y-m-d'),
                'ps_acquired_date' => $end_date->format('Y-m-d'),
                'idd_learning_time' => $minutes,
                'booking_status' => 'participant',
                'participation_status' => 'successful',
                'wbd_booking_id' => $wbd_booking_id
            ]
        );
    }

    public function setAsReported(int $crs_id, int $usr_id)
    {
        $this->db->insert(
            self::TABLE_ANNOUNCED_CASES,
            [
                self::FIELD_CRS_ID => ['integer', $crs_id],
                self::FIELD_USR_ID => ['integer', $usr_id]
            ]
        );
    }

    public function announceWBDBookingId(int $crs_id, int $usr_id, string $wbd_booking_id)
    {
        $this->raise(
            'addWBDBookingId',
            [
                'crs_id' => $crs_id,
                'usr_id' => $usr_id,
                'wbd_booking_id' => $wbd_booking_id
            ]
        );
    }

    public function removeWBDBookingId(int $crs_id, int $usr_id, string $source_wbd_booking_id)
    {
        $this->raise(
            'removeWBDBookingId',
            [
                'crs_id' => $crs_id,
                'usr_id' => $usr_id,
                'wbd_booking_id' => null
            ]
        );
    }

    public function getCourseIdOf(string $wbd_booking_id)
    {
        $query = "SELECT " . self::FIELD_CRS_ID . " AS crs_id" . PHP_EOL
            . "FROM " . self::TABLE_ANNOUNCEMENT_STATUS . PHP_EOL
            . "WHERE " . self::FIELD_WBD_BOOKING_ID . " = " . $this->db->quote($wbd_booking_id, "text");

        $res = $this->db->query($query);
        if ($this->db->numRows($res) == 0) {
            return null;
        }
        $row = $this->db->fetchAssoc($res);

        return (int) $row["crs_id"];
    }

    public function setBookingStatusSuccess(
        int $crs_id,
        int $usr_id,
        string $wbd_booking_id
    ) {
        $this->setBookingStatus(
            $crs_id,
            $usr_id,
            $wbd_booking_id,
            self::WBDA_STATUS_ANNOUNCED
        );
    }

    public function setBookingStatusCancelled(
        int $crs_id,
        int $usr_id,
        string $wbd_booking_id
    ) {
        $this->setBookingStatus(
            $crs_id,
            $usr_id,
            $wbd_booking_id,
            self::WBDA_STATUS_STORNO
        );
    }

    public function setParticipationCancelled(
        int $crs_id,
        int $usr_id,
        string $wbd_booking_id
    ) {
        $this->setBookingStatus(
            $crs_id,
            $usr_id,
            $wbd_booking_id,
            self::WBDA_STATUS_CANCELLED
        );
    }

    public function saveAnnouncedValues(WBD_RESPONSE\Participation $participation)
    {
        $send_data = $participation->getSentData();
        $id = $this->db->nextId(self::TABLE_REPORTED_CRS_VALUES);
        $values = [
            "id" => [
                "integer",
                $id
            ],
            "gutberaten_id" => [
                "text",
                $send_data->getGutBeratenId()
            ],
            "usr_id" => [
                "integer",
                $send_data->getUsrId()
            ],
            "crs_id" => [
                "integer",
                $send_data->getCrsId()
            ],
            "title" => [
                "text",
                $send_data->getTitle()
            ],
            "minutes" => [
                "integer",
                $send_data->getTimeInMinutes()
            ],
            "begin_date" => [
                "text",
                $send_data->getStartDate()->format("Y-m-d")
            ],
            "end_date" => [
                "text",
                $send_data->getEndDate()->format("Y-m-d")
            ],
            "wbd_booking_id" => [
                "text",
                $participation->getWbdBookingId()
            ]
        ];

        $this->db->insert(self::TABLE_REPORTED_CRS_VALUES, $values);
    }

    protected function setBookingStatus(
        int $crs_id,
        int $usr_id,
        string $wbd_booking_id,
        string $status
    ) {
        $q = 'INSERT INTO ' . self::TABLE_ANNOUNCEMENT_STATUS
            . '	(' . self::FIELD_CRS_ID
            . '	,' . self::FIELD_USR_ID
            . '	,' . self::FIELD_WBD_BOOKING_ID
            . '	,' . self::FIELD_WBD_ANNOUNCEMENT_STATUS . ')'
            . '	VALUES'
            . '	(' . $this->db->quote($crs_id, 'integer')
            . '	,' . $this->db->quote($usr_id, 'integer')
            . '	,' . $this->db->quote($wbd_booking_id, 'text')
            . '	,' . $this->db->quote($status, 'text') . ')'
            . '	ON DUPLICATE KEY UPDATE'
            . '		' . self::FIELD_WBD_BOOKING_ID . ' = ' . $this->db->quote($wbd_booking_id, 'text')
            . '		,' . self::FIELD_WBD_ANNOUNCEMENT_STATUS . ' = ' . $this->db->quote($status, 'text');
        $this->db->manipulate($q);
    }

    protected function raise(string $type, array $payload)
    {
        $this->eventhandler->raise('Plugin/WBDInterface', $type, $payload);
    }

    public function createReportedCourseValuesTable()
    {
        if (!$this->db->tableExists("xwbd_report_crs_values")) {
            $fields = [
                "id" => [
                    "type" => "integer",
                    "length" => 4,
                    "notnull" => true
                ],
                "gutberaten_id" => [
                    "type" => "text",
                    "length" => 18,
                    "notnull" => true
                ],
                "usr_id" => [
                    "type" => "integer",
                    "length" => 4,
                    "notnull" => true
                ],
                "crs_id" => [
                    "type" => "integer",
                    "length" => 4,
                    "notnull" => true
                ],
                "title" => [
                    "type" => "clob",
                    "notnull" => true
                ],
                "minutes" => [
                    "type" => "integer",
                    "length" => 4,
                    "notnull" => true
                ],
                "begin_date" => [
                    "type" => "text",
                    "length" => 11,
                    "notnull" => true
                ],
                "end_date" => [
                    "type" => "text",
                    "length" => 11,
                    "notnull" => true
                ],
                "wbd_booking_id" => [
                    "type" => "clob",
                    "notnull" => true
                ]
            ];

            $this->db->createTable("xwbd_report_crs_values", $fields);
        }
    }

    public function createReportedCourseValuesSequence()
    {
        if (!$this->db->sequenceExists("xwbd_report_crs_values")) {
            $this->db->createSequence("xwbd_report_crs_values");
        }
    }

    public function createReportedCourseValuesKeys()
    {
        try {
            $this->db->addPrimaryKey("xwbd_report_crs_values", array("id"));
        } catch (Exception $e) {
            $this->db->dropPrimaryKey("xwbd_report_crs_values");
            $this->db->addPrimaryKey("xwbd_report_crs_values", array("id"));
        }
    }
}
