<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseMailing\Invites;

class ilDB implements DB
{
    const TABLE_NAME = "xcml_invited_users";
    const TABLE_UDF_TEXT = "udf_text";
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
     * @inheritDoc
     * @throws \Exception
     */
    public function getInvitedUserFor(
        int $obj_id,
        string $order_field,
        string $order_direction,
        $offset,
        $limit,
        array $udf_columns = []
    ) : array {
        list($additional_select, $additional_join, $need_org_units) =
            $this->getUDFSelectElements($udf_columns);

        $q = $this->getSelectBase($additional_select) . PHP_EOL
            . ' ' . $additional_join
            . ' WHERE obj_id = ' . $this->db->quote($obj_id, 'integer') . PHP_EOL
            . '     AND deleted = 0' . PHP_EOL
            . ' ORDER BY ' . $order_field . ' ' . $order_direction . PHP_EOL
            . ' LIMIT ' . $limit . ' OFFSET ' . $offset
        ;

        $res = $this->db->query($q);

        $ret = [];
        while ($row = $this->db->fetchAssoc($res)) {
            if ($need_org_units) {
                $row["org_units"] = $this->getOrgUnitsForUserId((int) $row['usr_id']);
            }
            $ret[] = $this->createInviteByRow($row, $udf_columns);
        }

        return $ret;
    }

    public function countInvitedUserFor(int $obj_id) : int
    {
        $q = 'SELECT count(obj_id) AS cnt' . PHP_EOL
            . ' FROM ' . self::TABLE_NAME . PHP_EOL
            . ' WHERE obj_id = ' . $this->db->quote($obj_id, 'integer') . PHP_EOL
            . '     AND deleted = 0'
        ;

        $res = $this->db->query($q);
        $row = $this->db->fetchAssoc($res);
        return (int) $row["cnt"];
    }

    /**
     * @inheritDoc
     */
    public function addUser(int $usr_id, int $obj_id, int $added_by)
    {
        $id = $this->getNextId();

        $values = [
            "id" => [
                "integer",
                $id
            ],
            "usr_id" => [
                "integer",
                $usr_id
            ],
            "obj_id" => [
                "integer",
                $obj_id
            ],
            "added_by" => [
                "integer",
                $added_by
            ],
            "added_at" => [
                "timestamp",
                date("Y-m-d H:i:s")
            ]
        ];

        $this->db->insert(self::TABLE_NAME, $values);
    }

    /**
     * @inheritDoc
     */
    public function deleteUser(int $id)
    {
        $where = [
            "id" => [
                "integer",
                $id
            ]
        ];

        $value = [
            "deleted" => [
                "integer",
                1
            ]
        ];

        $this->db->update(self::TABLE_NAME, $value, $where);
    }

    /**
     * @inheritDoc
     */
    public function setRejectedByUser(int $id, int $rejected_by)
    {
        $where = [
            "id" => [
                'integer',
                $id
            ]
        ];

        $values = [
            'rejected_by' => [
                'integer',
                $rejected_by
            ],
            'rejected_at' => [
                'timestamp',
                date('Y-m-d H:i:s')
            ]
        ];

        $this->db->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritDoc
     */
    public function setInvitedByUser(int $id, int $invite_by)
    {
        $where = [
            "id" => [
                'integer',
                $id
            ]
        ];

        $values = [
            'invite_by' => [
                'integer',
                $invite_by
            ],
            'invite_at' => [
                'timestamp',
                date('Y-m-d H:i:s')
            ]
        ];

        $this->db->update(self::TABLE_NAME, $values, $where);
    }

    public function setInvitedBy(int $usr_id, int $obj_id, int $invite_by)
    {
        $where = [
            'usr_id' => [
                'integer',
                $usr_id
            ],
            'obj_id' => [
                'integer',
                $obj_id
            ]
        ];

        $values = [
            'invite_by' => [
                'integer',
                $invite_by
            ],
            'invite_at' => [
                'timestamp',
                date('Y-m-d H:i:s')
            ]
        ];

        $this->db->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function createRejectHashFor(int $usr_id, int $obj_id) : string
    {
        $hash = $this->getHash();

        $where = [
            "usr_id" => [
                'integer',
                $usr_id
            ],
            "obj_id" => [
                'integer',
                $obj_id
            ]
        ];

        $values = [
            'hash' => [
                'text',
                $hash
            ]
        ];

        $this->db->update(self::TABLE_NAME, $values, $where);

        return $hash;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function rejectByHash(string $hash)
    {
        $q = 'UPDATE ' . self::TABLE_NAME . PHP_EOL
            . ' SET rejected_by = usr_id,' . PHP_EOL
            . ' rejected_at = ' . $this->db->quote(date('Y-m-d H:i:s'), 'text') . PHP_EOL
            . ' WHERE hash = ' . $this->db->quote($hash, 'text')
        ;

        $this->db->manipulate($q);
    }

    /**
     * @inheritDoc
     */
    public function isAdded(int $usr_id, int $obj_id, bool $check_deleted = false) : bool
    {
        $q = 'SELECT count(id) AS cnt' . PHP_EOL
            . ' FROM ' . self::TABLE_NAME . PHP_EOL
            . ' WHERE usr_id = ' . $this->db->quote($usr_id, 'integer') . PHP_EOL
            . '     AND obj_id = ' . $this->db->quote($obj_id, 'integer') . PHP_EOL
            . '     AND deleted = ' . $this->db->quote($check_deleted, "integer");
        ;

        $res = $this->db->query($q);
        $row = $this->db->fetchAssoc($res);
        return (int) $row['cnt'] > 0;
    }

    /**
     * @inheritDoc
     */
    public function getLoginById(int $id) : string
    {
        $q = 'SELECT usr.login ' . PHP_EOL
            . ' FROM ' . self::TABLE_USR_DATA . ' usr' . PHP_EOL
            . ' JOIN ' . self::TABLE_NAME . ' base' . PHP_EOL
            . '     ON base.usr_id = usr.usr_id' . PHP_EOL
            . ' WHERE base.id = ' . $this->db->quote($id, 'integer')
        ;

        $res = $this->db->query($q);
        if ($this->db->numRows($res) == 0) {
            return "";
        }

        $row = $this->db->fetchAssoc($res);
        return $row['login'];
    }

    /**
     * @inheritDoc
     */
    public function reactivateUser(int $usr_id, int $obj_id, int $added_by)
    {
        $where = [
            "usr_id" => [
                'integer',
                $usr_id
            ],
            "obj_id" => [
                'integer',
                $obj_id
            ]
        ];

        $values = [
            'deleted' => [
                'integer',
                0
            ],
            "added_by" => [
                "integer",
                $added_by
            ],
            "added_at" => [
                "timestamp",
                date("Y-m-d H:i:s")
            ]
        ];

        $this->db->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @throws \Exception
     */
    protected function createInviteByRow(array $row, array $udf_columns = []) : Invite
    {
        $udf_values = $this->getUDFValueArray($row, $udf_columns);

        $invite_by = $this->nullOrInt($row['invite_by']);
        $invite_at = $this->nullOrDateTime($row['invite_at']);
        $rejected_by = $this->nullOrInt($row['rejected_by']);
        $rejected_at = $this->nullOrDateTime($row['rejected_at']);

        return new Invite(
            (int) $row['id'],
            (int) $row['usr_id'],
            (string) $row['lastname'],
            (string) $row['firstname'],
            (int) $row['obj_id'],
            (int) $row['added_by'],
            new \DateTime($row['added_at']),
            $udf_values,
            $invite_by,
            $invite_at,
            $rejected_by,
            $rejected_at
        );
    }

    protected function getUDFValueArray(array $row, array $udf_columns) : array
    {
        $ret = [];
        foreach ($udf_columns as $column) {
            if (is_null($row[$column])) {
                $row[$column] = "";
            }
            $ret[$column] = $row[$column];
        }
        return $ret;
    }

    /**
     * @throws \Exception
     */
    protected function getHash() : string
    {
        $data = $this->getRandomString();
        return hash('sha512', $data, false);
    }

    /**
     * @throws \Exception
     */
    protected function getRandomString() : string
    {
        return (string) random_bytes(random_int(100, 350));
    }

    protected function getSelectBase(string $udf_part = '') : string
    {
        return 'SELECT base.id, base.usr_id, base.obj_id, base.added_by, base.added_at,' . PHP_EOL
            . ' base.invite_by, base.invite_at, base.rejected_by, base.rejected_at,' . PHP_EOL
            . ' usr.lastname, usr.firstname' . PHP_EOL
            . ' ' . $udf_part . PHP_EOL
            . ' FROM ' . self::TABLE_NAME . ' base' . PHP_EOL
            . ' JOIN ' . self::TABLE_USR_DATA . ' usr' . PHP_EOL
            . '    ON usr.usr_id = base.usr_id'
        ;
    }

    protected function getNextId()
    {
        return (int) $this->db->nextId(self::TABLE_NAME);
    }

    protected function getUDFSelectElements(array $udf_columns) : array
    {
        $additional_select = "";
        $additional_join = "";
        $need_org_units = false;

        foreach ($udf_columns as $field) {
            if ($field == "org_units") {
                $need_org_units = true;
                continue;
            }

            if ($this->isUdfField($field)) {
                $id = $this->getUdfId($field);

                $additional_join .=
                    "LEFT JOIN " . self::TABLE_UDF_TEXT . " " . $field
                    . " ON usr.usr_id = " . $field . ".usr_id AND " . $field . ".field_id = " . $id . PHP_EOL;
                $additional_select .= ", " . $field . ".value AS " . $field . PHP_EOL;
            } else {
                $additional_select .= ", usr." . $field . " AS " . $field . PHP_EOL;
            }
        }

        return [
            $additional_select,
            $additional_join,
            $need_org_units
        ];
    }

    /**
     * @return int|null
     */
    protected function nullOrInt(string $value = null)
    {
        if (is_null($value)) {
            return null;
        }

        return (int) $value;
    }

    /**
     * @throws \Exception
     * @return \DateTime|null
     */
    protected function nullOrDateTime(string $value = null)
    {
        if (is_null($value)) {
            return null;
        }

        return new \DateTime($value);
    }

    protected function getOrgUnitsForUserId(int $usr_id) : string
    {
        return \ilObjUser::lookupOrgUnitsRepresentation($usr_id);
    }

    protected function isUdfField(string $field) : bool
    {
        return strpos($field, "udf_") === 0;
    }

    private function getUdfId($field)
    {
        return (int) (explode("_", $field))[1];
    }
}
