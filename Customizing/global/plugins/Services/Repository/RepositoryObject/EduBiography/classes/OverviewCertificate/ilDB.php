<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\OverviewCertificate;

class ilDB implements DB
{
    const TABLE_NAME = "xebr_curr_certificates";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function save(int $usr_id, int $obj_id, int $minutes)
    {
        $values = [
            "usr_id" => [
                "integer",
                $usr_id
            ],
            "obj_id" => [
                "integer",
                $obj_id
            ],
            "minutes" => [
                "integer",
                $minutes
            ],
        ];

        $this->db->insert(self::TABLE_NAME, $values);
    }

    public function selectFor(int $usr_id) : array
    {
        $q = "SELECT usr_id, obj_id, minutes" . PHP_EOL
            . " FROM " . self::TABLE_NAME . PHP_EOL
            . " WHERE usr_id = " . $usr_id;

        $res = $this->db->query($q);
        $ret = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $obj_id = (int) $row["obj_id"];
            $ret[$obj_id] = new ExistingCertificate(
                (int) $row["usr_id"],
                $obj_id,
                (int) $row["minutes"]
            );
        }
        return $ret;
    }

    public function createTable()
    {
        if (!$this->db->tableExists("xebr_curr_certificates")) {
            $fields = [
                "usr_id" => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                "obj_id" => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                "minutes" => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ]
            ];
        }

        $this->db->createTable("xebr_curr_certificates", $fields);
    }

    public function createPrimaryKey()
    {
        try {
            $this->db->addPrimaryKey("xebr_curr_certificates", ["usr_id", "obj_id"]);
        } catch (\PDOException $e) {
            $this->db->dropPrimaryKey("xebr_curr_certificates");
            $this->db->addPrimaryKey("xebr_curr_certificates", ["usr_id", "obj_id"]);
        }
    }
}
