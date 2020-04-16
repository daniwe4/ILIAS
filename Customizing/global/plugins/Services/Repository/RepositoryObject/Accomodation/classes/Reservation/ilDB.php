<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Accomodation\Reservation;

use Exception;
use ilDateTime;
use ilDBInterface;

/**
 * DB handle of reservations
 */
class ilDB implements DB
{
    const TABLE_NAME = "xoac_reservations";

    /**
     * @var ilDBInterface
     */
    protected $db;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }


    private function buildReservation(
        int $id,
        int $oac_obj_id,
        int $usr_id,
        string $date,
        bool $selfpay
    ) {
        $r = new Reservation(
            $id,
            $oac_obj_id,
            $usr_id,
            new ilDateTime($date, IL_CAL_DATE),
            $selfpay
        );

        return $r;
    }


    /**
     * @inheritdoc
     */
    public function selectForUserInObject(int $usr_id, int $aco_obj_id) : array
    {
        $query = "SELECT id, oac_obj_id, usr_id, rdate, ses_obj_id, selfpay\n"
                . " FROM " . static::TABLE_NAME . " \n"
                . " WHERE usr_id = " . $this->getDB()->quote($usr_id, "integer") . "\n"
                . " AND oac_obj_id = " . $this->getDB()->quote($aco_obj_id, "integer");

        $res = $this->getDB()->query($query);
        $reservations = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $reservations[] = $this->buildReservation(
                (int) $row['id'],
                (int) $row['oac_obj_id'],
                (int) $row['usr_id'],
                $row['rdate'],
                (bool) $row['selfpay']
            );
        }
        return $reservations;
    }


    /**
     * @inheritdoc
     */
    public function selectAllForObj(int $oac_obj_id) : array
    {
        $query = "SELECT DISTINCT usr_id\n"
                . " FROM " . static::TABLE_NAME . " \n"
                . " WHERE oac_obj_id = " . $this->getDB()->quote($oac_obj_id, "integer");

        $res = $this->getDB()->query($query);
        $ret = array();

        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[$row['usr_id']] = $this->selectForUserInObject((int) $row['usr_id'], $oac_obj_id);
        }

        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function update(Reservation $reservation)
    {
        $where = array("id" => array("int", $reservation->getId()));

        $date = $reservation->getDate()->get(IL_CAL_DATE);
        $values = array(
            "oac_obj_id" => array("int", $reservation->getAccomodationObjId()),
            "usr_id" => array("int", $reservation->getUserId()),
            "rdate" => array("string", $date),
            //"ses_obj_id" => array("int", $reservation->getSessionObjId()),
            "selfpay" => array("int", $reservation->getSelfpay())
        );

        $this->getDB()->update(static::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function deleteForId(int $id)
    {
        $query = "DELETE FROM " . static::TABLE_NAME . "\n"
            . " WHERE id = " . $this->getDB()->quote($id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * @inheritdoc
     */
    public function createReservation(
        int $oac_obj_id,
        int $usr_id,
        string $date,
        bool $selfpay
    ) : Reservation {
        $reservation = $this->buildReservation(
            (int) $this->getDB()->nextId(static::TABLE_NAME),
            $oac_obj_id,
            $usr_id,
            $date,
            $selfpay
        );

        $values = array(
            "id" => array("int", $reservation->getId()),
            "oac_obj_id" => array("int", $reservation->getAccomodationObjId()),
            "usr_id" => array("int", $reservation->getUserId()),
            "rdate" => array("string", $reservation->getDate()->get(IL_CAL_DATE)),
            "selfpay" => array("int", $reservation->getSelfpay())
        );

        $this->getDB()->insert(static::TABLE_NAME, $values);

        return $reservation;
    }

    /**
     * @inheritdoc
     */
    public function deleteAllUserReservations(int $oac_obj_id, int $usr_id)
    {
        $query = "DELETE FROM " . static::TABLE_NAME . "\n"
            . " WHERE oac_obj_id = " . $this->getDB()->quote($oac_obj_id, "integer") . "\n"
            . " AND usr_id = " . $this->getDB()->quote($usr_id, "integer") . "\n";

        $this->getDB()->manipulate($query);
    }

    /**
     * @inheritdoc
     */
    public function deleteAllForObj(int $oac_obj_id)
    {
        $query = "DELETE FROM " . static::TABLE_NAME . "\n"
            . " WHERE oac_obj_id = " . $this->getDB()->quote($oac_obj_id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * @inheritdoc
     */
    public function deleteAllForUser(int $usr_id)
    {
        $query = "DELETE FROM " . static::TABLE_NAME . "\n"
            . " WHERE usr_id = " . $this->getDB()->quote($usr_id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * @inheritdoc
     */
    public function getUserReservations(int $oac_obj_id, int $usr_id) : array
    {
        return $this->selectForUserInObject($usr_id, $oac_obj_id);
    }

    /**
     * Get instance of db
     *
     * @throws Exception
     * @return ilDBInterface
     */
    protected function getDB() : ilDBInterface
    {
        if (!$this->db) {
            throw new Exception("no Database");
        }
        return $this->db;
    }

    public function createTable()
    {
        $db = $this->getDB();
        if (!$db->tableExists(static::TABLE_NAME)) {
            $fields =
                array(
                    'id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'oac_obj_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'usr_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'rdate' => array(
                        'type' => 'text',
                        'length' => 16,
                        'notnull' => true
                    ),
                    'ses_obj_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'selfpay' => array(
                        'type' => 'integer',
                        'length' => 1,
                        'notnull' => false
                    )
                );

            $db->createTable(static::TABLE_NAME, $fields);
        }

        if (!$db->tableExists($db->getSequenceName(static::TABLE_NAME))) {
            $db->createSequence(static::TABLE_NAME);
        }
    }

    /**
     * Set primary key for table
     *
     * @return void
     */
    public function createPrimaryKey()
    {
        $this->getDB()->addPrimaryKey(static::TABLE_NAME, array("id"));
    }
}
