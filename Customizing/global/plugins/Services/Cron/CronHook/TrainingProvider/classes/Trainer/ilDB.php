<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace CaT\Plugins\TrainingProvider\Trainer;

/**
 * Implementation for trainer database handle
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_NAME = "tp_trainer";
    const TABLE_PROVIDER = "tp_provider";

    /**
     * @var \ilDBInterface
     */
    protected $db = null;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function install() : void
    {
        $this->createTable();
        $this->createSequence();
    }

    public function create(
        string $title,
        string $salutation,
        string $firstname,
        string $lastname,
        ?int $provider_id = null,
        string $email = "",
        string $phone = "",
        string $mobile_number = "",
        ?float $fee = null,
        ?string $extra_infos = null,
        bool $active = true
    ) : Trainer {
        $next_id = $this->getNextId();
        $trainer = new Trainer(
            $next_id,
            $title,
            $salutation,
            $firstname,
            $lastname,
            $provider_id,
            $email,
            $phone,
            $mobile_number,
            $fee,
            $extra_infos,
            $active
        );

        $values = [
            "id" => ["integer", $trainer->getId()],
            "title" => ["text", $trainer->getTitle()],
            "salutation" => ["text", $trainer->getSalutation()],
            "firstname" => ["text", $trainer->getFirstname()],
            "lastname" => ["text", $trainer->getLastname()],
            "provider_id" => ["integer", $trainer->getProviderId()],
            "email" => ["text", $trainer->getEmail()],
            "phone" => ["text", $trainer->getPhone()],
            "mobile_number" => ["text", $trainer->getMobileNumber()],
            "fee" => ["float", $trainer->getFee()],
            "extra_infos" => ["text", $trainer->getExtraInfos()],
            "active" => ["integer", (int) $trainer->getActive()]
        ];

        $this->getDB()->insert(self::TABLE_NAME, $values);

        return $trainer;
    }

    public function select(int $id) : Trainer
    {
        $query =
            "SELECT title, salutation, firstname, lastname, provider_id, email, phone, mobile_number, fee, extra_infos, active" . PHP_EOL
            . "FROM " . self::TABLE_NAME . PHP_EOL
            . "WHERE id = " . $this->getDB()->quote($id, "integer") . PHP_EOL
        ;

        $res = $this->getDB()->query($query);
        if ($this->getDB()->numRows($res) == 0) {
            throw new \Exception("No trainer found for id: " . $id);
        }

        $row = $this->getDB()->fetchAssoc($res);

        return new Trainer(
            $id,
            (string) $row["title"],
            (string) $row["salutation"],
            $row["firstname"],
            $row["lastname"],
            (int) $row["provider_id"],
            $row["email"],
            $row["phone"],
            (string) $row["mobile_number"],
            (float) $row["fee"],
            $row["extra_infos"],
            (bool) $row["active"]
        );
    }

    public function update(Trainer $trainer) : void
    {
        $where = ["id" => ["integer", $trainer->getId()]];

        $values = [
            "title" => ["text", $trainer->getTitle()],
            "salutation" => ["text", $trainer->getSalutation()],
            "firstname" => ["text", $trainer->getFirstname()],
            "lastname" => ["text", $trainer->getLastname()],
            "provider_id" => ["integer", $trainer->getProviderId()],
            "email" => ["text", $trainer->getEmail()],
            "phone" => ["text", $trainer->getPhone()],
            "mobile_number" => ["text", $trainer->getMobileNumber()],
            "fee" => ["float", $trainer->getFee()],
            "extra_infos" => ["text", $trainer->getExtraInfos()],
            "active" => ["integer", $trainer->getActive()]
        ];

        $this->getDB()->update(self::TABLE_NAME, $values, $where);
    }

    public function delete(int $id) : void
    {
        $query =
             "DELETE FROM " . self::TABLE_NAME . PHP_EOL
            . "WHERE id = " . $this->getDB()->quote($id, "integer") . PHP_EOL
        ;

        $this->getDB()->manipulate($query);
    }

    /**
     * Get trainer working for provider
     *
     * @return Trainer[] | []
     */
    public function getTrainerOf(int $provider_id) : array
    {
        $query =
             "SELECT id, title, salutation, firstname, lastname, email, phone, mobile_number, fee, extra_infos, active" . PHP_EOL
            . "FROM " . self::TABLE_NAME . PHP_EOL
            . "WHERE provider_id = " . $this->getDB()->quote($provider_id, "integer") . PHP_EOL
        ;

        $res = $this->getDB()->query($query);

        if ($this->getDB()->numRows($res) == 0) {
            return array();
        }

        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[$row["id"]] = new Trainer(
                $row["id"],
                $row["title"],
                $row["salutation"],
                $row["firstname"],
                $row["lastname"],
                (int) $provider_id,
                $row["email"],
                $row["phone"],
                $row["mobile_number"],
                (float) $row["fee"],
                $row["extra_infos"],
                (bool) $row["active"]
            );
        }

        return $ret;
    }

    /**
     * Get all trainer in raw format
     *
     * @param int | null 		$provider_id
     * @param int[] | null 		$active_filter
     *
     * @return array<mixed[]>
     */
    public function getTrainersRaw(?int $provider_id, array $active_filter = array()) : array
    {
        $where = null;
        if ($provider_id) {
            $where = "WHERE train.provider_id = " . $this->getDB()->quote($provider_id, "integer") . PHP_EOL;
        }

        if ($active_filter && count($active_filter) > 0) {
            if (!$where) {
                $where = " WHERE ";
            } else {
                $where .= "     AND ";
            }

            $where .= $this->getDB()->in("train.active", $active_filter, false, "integer") . PHP_EOL;
        }

        $query =
             "SELECT train.id, train.lastname, train.firstname, train.title," . PHP_EOL
            . "train.email, train.phone, train.mobile_number, train.fee, train.extra_infos, train.active," . PHP_EOL
            . "prov.name AS provider" . PHP_EOL
            . "FROM " . self::TABLE_NAME . " train" . PHP_EOL
            . "JOIN " . self::TABLE_PROVIDER . " prov" . PHP_EOL
            . "    ON train.provider_id = prov.id" . PHP_EOL
            . $where . PHP_EOL
        ;

        $res = $this->getDB()->query($query);

        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $row["name"] = trim($row["name"]);
            $ret[] = $row;
        }

        return $ret;
    }

    /**
     * Delete all trainers of deleted provider
     */
    public function deleteByProvider(int $provider_id) : void
    {
        $query =
             "DELETE FROM " . self::TABLE_NAME . PHP_EOL
            . "WHERE provider_id = " . $this->getDB()->quote($provider_id, "integer") . PHP_EOL
        ;

        $this->getDB()->manipulate($query);
    }

    /**
     * Creates needed tables
     */
    protected function createTable() : void
    {
        if (!$this->getDB()->tableExists(self::TABLE_NAME)) {
            $fields = [
                    "id" => [
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ],
                    "firstname" => [
                        'type' => 'text',
                        'length' => 32,
                        'notnull' => true
                    ],
                    "lastname" => [
                        'type' => 'text',
                        'length' => 32,
                        'notnull' => true
                    ],
                    "provider_id" => [
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    ],
                    "email" => [
                        'type' => 'text',
                        'length' => 32,
                        'notnull' => false
                    ],
                    "phone" => [
                        'type' => 'text',
                        'length' => 32,
                        'notnull' => false
                    ],
                    "fee" => [
                        'type' => 'float',
                        'notnull' => false
                    ],
                    "active" => [
                        'type' => 'integer',
                        'length' => 1,
                        'notnull' => true
                    ]
                ];

            $this->getDB()->createTable(self::TABLE_NAME, $fields);
            $this->getDB()->addPrimaryKey(self::TABLE_NAME, ["id"]);
        }
    }

    public function updateTable1() : void
    {
        $attributes = [
            'type' => 'text',
            'length' => 128,
            'notnull' => false
        ];
        $this->getDB()->modifyTableColumn(self::TABLE_NAME, "email", $attributes);
    }

    public function updateTable2() : void
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "title")) {
            $attributes = [
                'type' => 'text',
                'length' => 64,
                'notnull' => false
            ];
            $this->getDB()->addTableColumn(self::TABLE_NAME, "title", $attributes);
        }

        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "salutation")) {
            $attributes = [
                'type' => 'text',
                'length' => 32,
                'notnull' => false
            ];
            $this->getDB()->addTableColumn(self::TABLE_NAME, "salutation", $attributes);
        }

        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "mobile_number")) {
            $attributes = [
                'type' => 'text',
                'length' => 64,
                'notnull' => false
            ];
            $this->getDB()->addTableColumn(self::TABLE_NAME, "mobile_number", $attributes);
        }

        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "costs")) {
            $attributes = [
                'type' => 'float',
                'notnull' => false
            ];
            $this->getDB()->addTableColumn(self::TABLE_NAME, "costs", $attributes);
        }
    }

    public function updateTable3() : void
    {
        $attributes = [
            'type' => 'text',
            'length' => 256,
            'notnull' => false
        ];
        if ($this->getDB()->tableColumnExists(self::TABLE_NAME, "costs")) {
            $this->getDB()->renameTableColumn(self::TABLE_NAME, "costs", "extra_infos");
        }

        if ($this->getDB()->tableColumnExists(self::TABLE_NAME, "extra_infos")) {
            $this->getDB()->modifyTableColumn(self::TABLE_NAME, "extra_infos", $attributes);
        }
    }

    protected function createSequence() : void
    {
        if (!$this->getDB()->sequenceExists(self::TABLE_NAME)) {
            $this->getDB()->createSequence(self::TABLE_NAME);
        }
    }

    protected function getDB() : \ilDBInterface
    {
        if ($this->db === null) {
            throw new \Exception("No databse defined in trainer db implementation");
        }

        return $this->db;
    }

    protected function getNextId() : int
    {
        return (int) $this->getDB()->nextId(self::TABLE_NAME);
    }
}
