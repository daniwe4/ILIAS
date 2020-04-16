<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingApprovals\Approvals;

/**
 * ILIAS implementation of storage for BookingRequests and Approvals
 */
class ilDB implements ApprovalDB
{
    const TABLE_REQUESTS = "xbka_requests";
    const TABLE_APPROVALS = "xbka_approvals";
    const DATE_FORMAT = "d.m.Y H:i";

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Create a BookingRequest
     */
    public function createBookingRequest(
        int $acting_usr_id,
        int $usr_id,
        int $crs_ref_id,
        int $crs_id,
        string $booking_data
    ) : BookingRequest {
        $next_id = $this->getNextId(self::TABLE_REQUESTS);
        $now = new \DateTime('now');
        $booking_request = new BookingRequest(
            $next_id,
            $acting_usr_id,
            $usr_id,
            $crs_ref_id,
            $crs_id,
            $now,
            $booking_data,
            BookingRequest::OPEN
        );

        $dat = date_format($booking_request->getRequestDate(), self::DATE_FORMAT);
        $values = array(
            "id" => array("integer", $booking_request->getId()),
            "acting_usr_id" => array("integer", $booking_request->getActingUserId()),
            "usr_id" => array("integer", $booking_request->getUserId()),
            "crs_ref_id" => array("integer", $booking_request->getCourseRefId()),
            "crs_id" => array("integer", $booking_request->getCourseId()),
            "creation_date" => array("text", $dat),
            "booking_data" => array("text", $booking_data),
            "state" => array("integer", $booking_request->getState()),
        );

        $this->getDB()->insert(self::TABLE_REQUESTS, $values);
        return $booking_request;
    }

    public function selectBookingRequests(array $user_ids, bool $open = true) : array
    {
        $where = "WHERE A.state = " . BookingRequest::OPEN . PHP_EOL;

        if (!$open) {
            $where = "WHERE A.state != " . BookingRequest::OPEN . PHP_EOL;
        }

        if (count($user_ids) > 0) {
            $where .= "AND A.usr_id IN (" . implode(", ", $user_ids) . ")" . PHP_EOL;
        }

        $query =
             "SELECT" . PHP_EOL
             . "    A.id," . PHP_EOL
             . "    A.acting_usr_id," . PHP_EOL
             . "    A.usr_id," . PHP_EOL
             . "    A.crs_ref_id," . PHP_EOL
             . "    A.crs_id," . PHP_EOL
             . "    A.creation_date," . PHP_EOL
             . "    A.booking_data," . PHP_EOL
             . "    A.state" . PHP_EOL
             . " FROM " . self::TABLE_REQUESTS . " A" . PHP_EOL
             . " JOIN object_reference B" . PHP_EOL
             . "     ON A.crs_ref_id = B.ref_id" . PHP_EOL
            . $where
        ;

        $result = $this->getDB()->query($query);

        if ($this->getDB()->numRows($result) == 0) {
            return array();
        }

        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($result)) {
            $ret[] = $this->createRequestObject($row);
        }

        return $ret;
    }

    /**
     * Store a BookingRequest
     */
    public function updateBookingRequest(BookingRequest $booking_request)
    {
        $values = array("state" => ["integer", $booking_request->getState()]);
        $where = array("id" => ["integer", $booking_request->getId()]);

        $this->getDB()->update(self::TABLE_REQUESTS, $values, $where);
    }

    /**
     * @return BookingRequest[]
     */
    public function getBookingRequests(array $ids) : array
    {
        $query =
             "SELECT" . PHP_EOL
             . "    id," . PHP_EOL
             . "    acting_usr_id," . PHP_EOL
             . "    usr_id," . PHP_EOL
             . "    crs_ref_id," . PHP_EOL
             . "    crs_id," . PHP_EOL
             . "    creation_date," . PHP_EOL
             . "    booking_data," . PHP_EOL
             . "    state" . PHP_EOL
            . " FROM " . self::TABLE_REQUESTS . PHP_EOL
            . " WHERE id IN ('" . implode("', '", $ids) . "')" . PHP_EOL
        ;

        $result = $this->getDB()->query($query);

        if ($this->getDB()->numRows($result) == 0) {
            throw new \Exception("No booking request entry found for ids '" . join(",", $ids) . "'.");
        }

        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($result)) {
            $ret[] = $this->createRequestObject($row);
        }

        return $ret;
    }

    public function hasUserOpenRequestOnCourse(int $usr_id, int $crs_id) : bool
    {
        $query =
            "SELECT count(A.id) AS cnt" . PHP_EOL
            . " FROM " . self::TABLE_REQUESTS . " A" . PHP_EOL
            . " JOIN object_reference B" . PHP_EOL
            . "     ON A.crs_ref_id = B.ref_id" . PHP_EOL
            . "WHERE A.state = " . BookingRequest::OPEN . PHP_EOL
            . "    AND A.usr_id = " . $this->getDB()->quote($usr_id, "integer") . PHP_EOL
            . "    AND A.crs_id = " . $this->getDB()->quote($crs_id, "integer")
        ;

        $res = $this->getDB()->query($query);
        $row = $this->getDB()->fetchAssoc($res);

        return $row["cnt"] > 0;
    }

    /**
     * @return BookingRequests
     */
    public function getAllBookingRequets() : array
    {
        $query =
             "SELECT" . PHP_EOL
             . "    id," . PHP_EOL
             . "    acting_usr_id," . PHP_EOL
             . "    usr_id," . PHP_EOL
             . "    crs_ref_id," . PHP_EOL
             . "    crs_id," . PHP_EOL
             . "    creation_date," . PHP_EOL
             . "    booking_data," . PHP_EOL
             . "    state" . PHP_EOL
            . " FROM " . self::TABLE_REQUESTS . PHP_EOL
        ;

        $result = $this->getDB()->query($query);

        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($result)) {
            $ret[] = $this->createRequestObject($row);
        }

        return $ret;
    }

    /**
     * Select an approval by approval id.
     *
     * @return Approval[]
     */
    public function getApprovals(array $approval_ids) : array
    {
        $query =
             "SELECT" . PHP_EOL
            . "    id," . PHP_EOL
            . "    booking_request_id," . PHP_EOL
            . "    order_number," . PHP_EOL
            . "    approval_position," . PHP_EOL
            . "    state," . PHP_EOL
            . "    approving_usr_id," . PHP_EOL
            . "    approving_date" . PHP_EOL
            . "FROM " . self::TABLE_APPROVALS . PHP_EOL
            . "WHERE id IN (" . implode(", ", $approval_ids) . ")" . PHP_EOL
        ;

        $result = $this->getDB()->query($query);

        if ($this->getDB()->numRows($result) == 0) {
            throw new \Exception("No approval entry found." . PHP_EOL . $query);
        }

        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($result)) {
            $ret[] = $this->createApprovalObject($row);
        }

        return $ret;
    }

    /**
     * Create an Approval
     */
    public function createApproval(
        int $booking_request_id,
        int $order_number,
        int $approval_position
    ) : Approval {
        $next_id = $this->getNextId(self::TABLE_APPROVALS);
        $approval = new Approval(
            $next_id,
            $booking_request_id,
            $order_number,
            $approval_position,
            Approval::OPEN
        );

        $values = array(
            "id" => array("integer", $approval->getId()),
            "booking_request_id" => array("integer", $approval->getBookingRequestId()),
            "order_number" => array("integer", $approval->getOrderNumber()),
            "approval_position" => array("integer", $approval->getApprovalPosition()),
            "state" => array("integer", $approval->getState()),
        );

        $this->getDB()->insert(self::TABLE_APPROVALS, $values);
        return $approval;
    }

    public function updateApproval(Approval $approval)
    {
        $dat = date_format($approval->getApprovalDate(), self::DATE_FORMAT);
        $values = array(
            "state" => ["integer", $approval->getState()],
            "approving_usr_id" => ["integer", $approval->getApprovingUserId()],
            "approving_date" => ["string", $dat]
        );

        $where = ["id" => ["integer", $approval->getId()]];

        $this->getDB()->update(self::TABLE_APPROVALS, $values, $where);
    }

    /**
     * @return Approval[]
     */
    public function getApprovalsForRequest(int $request_id) : array
    {
        $query =
             "SELECT" . PHP_EOL
            . "    id," . PHP_EOL
            . "    booking_request_id," . PHP_EOL
            . "    order_number," . PHP_EOL
            . "    approval_position," . PHP_EOL
            . "    state," . PHP_EOL
            . "    approving_usr_id," . PHP_EOL
            . "    approving_date" . PHP_EOL
            . "FROM " . self::TABLE_APPROVALS . PHP_EOL
            . "WHERE booking_request_id = " . $this->getDB()->quote($request_id, "integer") . PHP_EOL
            . "ORDER BY order_number ASC" . PHP_EOL
        ;

        $result = $this->getDB()->query($query);

        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($result)) {
            $ret[] = $this->createApprovalObject($row);
        }

        return $ret;
    }

    /**
     * @return Approval[]
     */
    public function getApprovalsForBookingRequestIds(array $ids)
    {
        $query =
             "SELECT" . PHP_EOL
            . "    id," . PHP_EOL
            . "    booking_request_id," . PHP_EOL
            . "    order_number," . PHP_EOL
            . "    approval_position," . PHP_EOL
            . "    state," . PHP_EOL
            . "    approving_usr_id," . PHP_EOL
            . "    approving_date" . PHP_EOL
            . "FROM " . self::TABLE_APPROVALS . PHP_EOL
            . "WHERE booking_request_id IN (" . implode(", ", $ids) . ")" . PHP_EOL
        ;

        $result = $this->getDB()->query($query);

        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($result)) {
            $ret[] = $this->createApprovalObject($row);
        }

        return $ret;
    }

    protected function createApprovalObject(array $row) : Approval
    {
        $dat = null;
        if ($row["approving_date"] !== '') {
            $dat = new \DateTime($row["approving_date"]);
        }
        return new Approval(
            (int) $row["id"],
            (int) $row["booking_request_id"],
            (int) $row["order_number"],
            (int) $row["approval_position"],
            (int) $row["state"],
            (int) $row["approving_usr_id"],
            $dat
        );
    }

    protected function createRequestObject(array $row) : BookingRequest
    {
        return new BookingRequest(
            (int) $row["id"],
            (int) $row["acting_usr_id"],
            (int) $row["usr_id"],
            (int) $row["crs_ref_id"],
            (int) $row["crs_id"],
            new \DateTime($row["creation_date"]),
            $row["booking_data"],
            (int) $row["state"]
        );
    }

    /**
     * Create the table to save booking requests
     * @return void
     */
    public function createRequestsTable()
    {
        if (!$this->getDB()->tableExists(self::TABLE_REQUESTS)) {
            $fields = array(
                'id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'usr_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'crs_ref_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'creation_date' => array(
                    'type' => 'text',
                    'length' => 19,
                    'notnull' => true
                ),
                'booking_data' => array(
                    'type' => 'clob',
                    'notnull' => true
                ),
                'state' => array(
                    'type' => 'integer',
                    'length' => 2,
                    'notnull' => true
                )
            );
            $this->getDB()->createTable(self::TABLE_REQUESTS, $fields);
        }
    }

    /**
     * Create the table to save approvals
     * @return void
     */
    public function createApprovalsTable()
    {
        if (!$this->getDB()->tableExists(self::TABLE_APPROVALS)) {
            $fields = array(
                'id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'booking_request_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'order_number' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'approval_position' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'state' => array(
                    'type' => 'integer',
                    'length' => 2,
                    'notnull' => true
                ),
                'approving_usr_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'approving_date' => array(
                    'type' => 'text',
                    'length' => 19,
                    'notnull' => true
                )
            );
            $this->getDB()->createTable(self::TABLE_APPROVALS, $fields);
        }
    }

    /**
     * Create the primary keys
     * @return void
     */
    public function createPrimaryKeysRequests()
    {
        try {
            $this->getDB()->addPrimaryKey(self::TABLE_REQUESTS, array('id', 'usr_id', 'crs_ref_id'));
        } catch (\PDOException $e) {
            $this->getDB()->dropPrimaryKey(self::TABLE_REQUESTS);
            $this->getDB()->addPrimaryKey(self::TABLE_REQUESTS, array('id', 'usr_id', 'crs_ref_id'));
        }
    }

    /**
     * Create the primary keys
     * @return void
     */
    public function createPrimaryKeysApprovals()
    {
        try {
            $this->getDB()->addPrimaryKey(self::TABLE_APPROVALS, array('id', 'booking_request_id'));
        } catch (\PDOException $e) {
            $this->getDB()->dropPrimaryKey(self::TABLE_APPROVALS);
            $this->getDB()->addPrimaryKey(self::TABLE_APPROVALS, array('id', 'booking_request_id'));
        }
    }

    /**
     * Create sequence-tables
     *
     * @return void
     */
    public function createSequenceRequests()
    {
        if (!$this->getDB()->tableExists(self::TABLE_REQUESTS . '_seq')) {
            $this->getDB()->createSequence(self::TABLE_REQUESTS);
        }
    }

    /**
     * Create sequence-tables
     *
     * @return void
     */
    public function createSequenceApprovals()
    {
        if (!$this->getDB()->tableExists(self::TABLE_APPROVALS . '_seq')) {
            $this->getDB()->createSequence(self::TABLE_APPROVALS);
        }
    }

    /**
     * add acting_user_id to requests
     *
     * @return void
     */
    public function updateAddActingUser()
    {
        if ($this->getDB()->tableExists(self::TABLE_REQUESTS)) {
            $this->getDB()->addTableColumn(
                self::TABLE_REQUESTS,
                'acting_usr_id',
                [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ]
            );
        }
    }

    public function addCrsId()
    {
        if (
            $this->getDB()->tableExists(self::TABLE_REQUESTS) &&
            !$this->getDB()->tableColumnExists(self::TABLE_REQUESTS, "crs_id")
        ) {
            $this->getDB()->addTableColumn(
                self::TABLE_REQUESTS,
                'crs_id',
                [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ]
            );
        }
    }


    /**
     * Get the current db object
     * @throws \Exception if no db is set
     */
    protected function getDB() : \ilDBInterface
    {
        if (!$this->db) {
            throw new \Exception("no Database");
        }
        return $this->db;
    }

    /**
     * Get next id
     */
    protected function getNextId(string $table_name) : int
    {
        return (int) $this->getDB()->nextId($table_name);
    }
}
