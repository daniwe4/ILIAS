<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDManagement\Reports\ErrorReport;

class ilDB implements DB
{
    const TABLE_NAME = "wbd_request_errors";
    const TABLE_USR_DATA = "usr_data";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function setStatusToResolved(int $id)
    {
        $where = array(
            "id" => array("integer", $id)
        );

        $values = array(
            "status" => array("text", Entry::STATUS_RESOLVED)
        );

        $this->db->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function setStatusToNotResolvable(int $id)
    {
        $where = array(
            "id" => array("integer", $id)
        );

        $values = array(
            "status" => array("text", Entry::STATUS_NOT_RESOLVABLE)
        );

        $this->db->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function getErrorInfosFor(array $ids) : array
    {
        $query = "SELECT wbd_e.id, wbd_e.usr_id, wbd_e.gutberaten_id," . PHP_EOL
            . " wbd_e.crs_id, wbd_e.crs_title, wbd_e.learning_time, wbd_e.message," . PHP_EOL
            . " wbd_e.request_date, wbd_e.status," . PHP_EOL
            . " usrd.login, usrd.firstname, usrd.lastname" . PHP_EOL
            . " FROM " . self::TABLE_NAME . " wbd_e" . PHP_EOL
            . " LEFT JOIN " . self::TABLE_USR_DATA . " usrd" . PHP_EOL
            . " ON usrd.usr_id = wbd_e.usr_id" . PHP_EOL
            . " WHERE " . $this->db->in("id", $ids, false, "integer");

        $res = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($res)) {
            $ret[] = $this->createErrorObject($row);
        }

        return $ret;
    }

    protected function createErrorObject(array $row) : Entry
    {
        return new Entry(
            (int) $row["id"],
            (int) $row["usr_id"],
            $row["gutberaten_id"],
            (int) $row["crs_id"],
            $row["crs_title"],
            (int) $row["learning_time"],
            $row["message"],
            new \DateTime($row["request_date"]),
            $row["status"],
            $row["login"],
            $row["firstname"],
            $row["lastname"]
        );
    }
}
