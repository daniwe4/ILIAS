<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace CaT\Plugins\CourseCreation;

use ILIAS\TMS\CourseCreation\Request;

/**
 * A database for requests.
 */
class ilRequestDB implements RequestDB
{
    const TABLE_NAME = "xccr_requests";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @param   int     $user_id
     * @param   string  $session_id
     * @param   int     $crs_ref_id
     * @param   int     $new_parent_ref_id
     * @param   array<int,int> $copy_options
     * @param   array<int,mixed> $configuration
     * @param   \DateTime $requested_ts
     * @return  Request
     */
    public function create($user_id, $session_id, $crs_ref_id, $new_parent_ref_id, array $copy_options, array $configuration, \DateTime $requested_ts)
    {
        assert('is_int($user_id)');
        assert('is_string($session_id)');
        assert('is_int($crs_ref_id)');
        assert('is_int($new_parent_ref_id)');
        $id = (int) $this->db->nextId(self::TABLE_NAME);
        $this->db->insert(
            self::TABLE_NAME,
            [ "id" => ["integer", $id]
            , "user_id" => ["integer", $user_id]
            , "session_id" => ["string", $session_id]
            , "crs_ref_id" => ["int", $crs_ref_id]
            , "new_parent_ref_id" => ["int", $new_parent_ref_id]
            , "copy_options" => ["string", json_encode($copy_options)]
            , "configuration" => ["string", json_encode($configuration)]
            , "requested_ts" => ["string", $requested_ts->format(\DateTime::ISO8601)]
            ]
        );
        return new Request($id, $user_id, $session_id, $crs_ref_id, $new_parent_ref_id, [], [], $requested_ts);
    }

    /**
     * @param   Request $request
     * @return  void
     */
    public function update(Request $request)
    {
        $id = $request->getId();
        $this->db->update(
            self::TABLE_NAME,
            [ "user_id" => ["integer", $request->getUserId()]
            , "session_id" => ["string", $request->getSessionId()]
            , "crs_ref_id" => ["int", $request->getCourseRefId()]
            , "new_parent_ref_id" => ["int", $request->getNewParentRefId()]
            , "copy_options" => ["string", json_encode($request->getCopyOptions())]
            , "configuration" => ["string", json_encode($request->getConfigurations())]
            , "requested_ts" => ["string", $request->getRequestedTS()->format(\DateTime::ISO8601)]
            , "target_crs_ref_id" => ["string", $request->getTargetRefId()]
            , "finished_ts" => ["string", $request->getFinishedTS()->format(\DateTime::ISO8601)]
            ],
            [ "id" => ["integer", $id] ]
        );
    }

    /**
     * @return	Request|null
     */
    public function getNextDueRequest()
    {
        $query =
            "SELECT * FROM " . self::TABLE_NAME .
            " WHERE finished_ts IS NULL " .
            " ORDER BY requested_ts ASC " .
            " LIMIT 1 ";
        $result = $this->db->query($query);
        if ($r = $this->db->fetchAssoc($result)) {
            return new Request(
                (int) $r["id"],
                (int) $r["user_id"],
                $r["session_id"],
                (int) $r["crs_ref_id"],
                (int) $r["new_parent_ref_id"],
                json_decode($r["copy_options"], true),
                json_decode($r["configuration"], true),
                \DateTime::createFromFormat(\DateTime::ISO8601, $r["requested_ts"]),
                $r["target_crs_ref_id"] ? (int) $r["target_crs_ref_id"] : null,
                $r["finished_ts"] ? (\DateTime::createFromFormat(\DateTime::ISO8601, $r["finished_ts"])) : null
            );
        }
        return null;
    }

    /**
     * @param	int	$user_id
     * @return	Request[]
     */
    public function getDueRequestsOf($user_id)
    {
        assert('is_int($user_id)');
        $query =
            "SELECT * FROM " . self::TABLE_NAME .
            " WHERE user_id = " . $this->db->quote($user_id, "integer") .
            " AND finished_ts IS NULL";
        $result = $this->db->query($query);
        $requests = [];
        while ($r = $this->db->fetchAssoc($result)) {
            $requests[] = new Request(
                (int) $r["id"],
                (int) $r["user_id"],
                $r["session_id"],
                (int) $r["crs_ref_id"],
                (int) $r["new_parent_ref_id"],
                json_decode($r["copy_options"], true),
                json_decode($r["configuration"], true),
                \DateTime::createFromFormat(\DateTime::ISO8601, $r["requested_ts"])
            );
        }
        return $requests;
    }

    /**
     * Get all open request
     * open = no target id && no finished ts
     *
     * @param int | null	$offset
     * @param int | null	$limit
     *
     * @return Request[]
     */
    public function getOpenRequests($offset = null, $limit = null)
    {
        $query = "SELECT id, user_id, session_id, crs_ref_id, copy_options,"
                . " configuration, requested_ts, target_crs_ref_id, finished_ts,"
                . " new_parent_ref_id"
                . " FROM " . self::TABLE_NAME
                . " WHERE target_crs_ref_id IS NULL"
                . "     AND finished_ts IS NULL";

        if (!is_null($offset) && !is_null($limit)) {
            $query .= " LIMIT " . $limit . " OFFSET " . $offset;
        }

        $requests = [];

        $res = $this->db->query($query);
        while ($r = $this->db->fetchAssoc($res)) {
            $requests[] = new Request(
                (int) $r["id"],
                (int) $r["user_id"],
                $r["session_id"],
                (int) $r["crs_ref_id"],
                (int) $r["new_parent_ref_id"],
                json_decode($r["copy_options"], true),
                json_decode($r["configuration"], true),
                \DateTime::createFromFormat(\DateTime::ISO8601, $r["requested_ts"])
            );
        }

        return $requests;
    }

    /**
     * Count all open request
     * open = no target id && no finished ts
     *
     * @return int
     */
    public function getCountOpenRequests()
    {
        $query = "SELECT count(id) as cnt"
                . " FROM " . self::TABLE_NAME
                . " WHERE target_crs_ref_id IS NULL"
                . "     AND finished_ts IS NULL";

        $res = $this->db->query($query);
        $row = $this->db->fetchAssoc($res);

        return (int) $row["cnt"];
    }

    /**
     * Get all not usccessful finished requests
     *
     * @param int | null	$offset
     * @param int | null	$limit
     *
     * @return Request[]
     */
    public function getNotSuccessfulRequests($offset = null, $limit = null)
    {
        $query = "SELECT id, user_id, session_id, crs_ref_id, copy_options,"
                . " configuration, requested_ts, target_crs_ref_id, finished_ts,"
                . " new_parent_ref_id"
                . " FROM " . self::TABLE_NAME
                . " WHERE target_crs_ref_id IS NULL"
                . "     AND finished_ts IS NOT NULL"
                . " ORDER BY requested_ts DESC";

        if (!is_null($offset) && !is_null($limit)) {
            $query .= " LIMIT " . $limit . " OFFSET " . $offset;
        }

        $requests = [];

        $res = $this->db->query($query);
        while ($r = $this->db->fetchAssoc($res)) {
            $requests[] = new Request(
                (int) $r["id"],
                (int) $r["user_id"],
                $r["session_id"],
                (int) $r["crs_ref_id"],
                (int) $r["new_parent_ref_id"],
                json_decode($r["copy_options"], true),
                json_decode($r["configuration"], true),
                \DateTime::createFromFormat(\DateTime::ISO8601, $r["requested_ts"]),
                (int) $r["target_crs_ref_id"],
                \DateTime::createFromFormat(\DateTime::ISO8601, $r["finished_ts"])
            );
        }

        return $requests;
    }

    /**
     * Count all not usccessful finished requests
     *
     * @return int
     */
    public function getCountNotSuccessfulRequests()
    {
        $query = "SELECT count(id) as cnt"
                . " FROM " . self::TABLE_NAME
                . " WHERE target_crs_ref_id IS NULL"
                . "     AND finished_ts IS NOT NULL"
                . " ORDER BY requested_ts DESC";

        $res = $this->db->query($query);
        $row = $this->db->fetchAssoc($res);

        return (int) $row["cnt"];
    }

    /**
     * Get all successfull finished requests sorted bei finished ts
     *
     * @param int | null	$offset
     * @param int | null	$limit
     *
     * @return ILIAS\TMS\CourseCreation\Request[]
     */
    public function getFinishedRequests($offset = null, $limit = null)
    {
        $query = "SELECT id, user_id, session_id, crs_ref_id, copy_options,"
                . " configuration, requested_ts, target_crs_ref_id, finished_ts,"
                . " new_parent_ref_id"
                . " FROM " . self::TABLE_NAME
                . " WHERE target_crs_ref_id IS NOT NULL"
                . "     AND finished_ts IS NOT NULL"
                . " ORDER BY finished_ts DESC";

        if (!is_null($offset) && !is_null($limit)) {
            $query .= " LIMIT " . $limit . " OFFSET " . $offset;
        }

        $requests = [];

        $res = $this->db->query($query);
        while ($r = $this->db->fetchAssoc($res)) {
            $requests[] = new Request(
                (int) $r["id"],
                (int) $r["user_id"],
                $r["session_id"],
                (int) $r["crs_ref_id"],
                (int) $r["new_parent_ref_id"],
                json_decode($r["copy_options"], true),
                json_decode($r["configuration"], true),
                \DateTime::createFromFormat(\DateTime::ISO8601, $r["requested_ts"]),
                (int) $r["target_crs_ref_id"],
                \DateTime::createFromFormat(\DateTime::ISO8601, $r["finished_ts"])
            );
        }

        return $requests;
    }

    /**
     * Count all successfull finished requests sorted bei finished ts
     *
     * @return int
     */
    public function getCountFinishedRequests()
    {
        $query = "SELECT count(id) as cnt"
                . " FROM " . self::TABLE_NAME
                . " WHERE target_crs_ref_id IS NOT NULL"
                . "     AND finished_ts IS NOT NULL"
                . " ORDER BY finished_ts DESC";

        $res = $this->db->query($query);
        $row = $this->db->fetchAssoc($res);

        return (int) $row["cnt"];
    }

    /**
     * Set a request as finished
     *
     * @param int 	$request_id
     * @param \DateTime 	$finished_ts
     *
     * @return void
     */
    public function setRequestFinished($request_id, \DateTime $finished_ts)
    {
        assert('is_int($request_id)');
        $this->db->update(
            self::TABLE_NAME,
            [ "finished_ts" => ["string", $finished_ts->format(\DateTime::ISO8601)] ],
            [ "id" => ["integer", $request_id] ]
        );
    }

    /**
     * Create table.
     *
     * @return null
     */
    public function createTable()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $fields =
                ['id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                'user_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                'session_id' => [
                    'type' => 'text',
                    'length' => 250,
                    'notnull' => true
                ],
                'crs_ref_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                'copy_options' => [
                    'type' => 'clob',
                    'notnull' => true
                ],
                'configuration' => [
                    'type' => 'clob',
                    'notnull' => true
                ],
                "requested_ts" => [
                    'type' => 'text',
                    'length' => 32,
                    'notnull' => true
                ],
                'target_crs_ref_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => false
                ],
                "finished_ts" => [
                    'type' => 'text',
                    'length' => 32,
                    'notnull' => false
                ]
                ];

            $this->db->createTable(self::TABLE_NAME, $fields);
        }
        $this->db->addPrimaryKey(self::TABLE_NAME, array("id"));
    }

    /**
     * Update Table.
     *
     * @return null
     */
    public function updateTable1()
    {
        if (!$this->db->tableColumnExists(self::TABLE_NAME, "new_parent_ref_id")) {
            $this->db->addTableColumn(
                self::TABLE_NAME,
                'new_parent_ref_id',
                [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ]
            );
        }
    }

    /**
     * Create sequence for table.
     *
     * @return 	void
     */
    public function createSequence()
    {
        $this->db->createSequence(self::TABLE_NAME);
    }
}
