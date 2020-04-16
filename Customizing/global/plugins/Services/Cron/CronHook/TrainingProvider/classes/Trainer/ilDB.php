<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

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
     * @var /*ilDBPdoMySQLInnoDB
     */
    protected $db = null;

    public function __construct(/*ilDBPdoMySQLInnoDB*/ $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function install()
    {
        $this->createTable();
        $this->createSequence();
    }

    /**
     * @inheritdoc
     */
    public function create(
        $title,
        $salutation,
        $firstname,
        $lastname,
        $provider_id = null,
        $email = "",
        $phone = "",
        $mobile_number = "",
        $fee = null,
        $extra_infos = null,
        $active = true
    ) {
        $next_id = $this->getNextId();
        $trainer = new Trainer($next_id, $title, $salutation, $firstname, $lastname, $provider_id, $email, $phone, $mobile_number, $fee, $extra_infos, $active);

        $values = array("id" => array("integer", $trainer->getId())
                      , "title" => array("text", $trainer->getTitle())
                      , "salutation" => array("text", $trainer->getSalutation())
                      , "firstname" => array("text", $trainer->getFirstname())
                      , "lastname" => array("text", $trainer->getLastname())
                      , "provider_id" => array("text", $trainer->getProviderId())
                      , "email" => array("text", $trainer->getEmail())
                      , "phone" => array("text", $trainer->getPhone())
                      , "mobile_number" => array("text", $trainer->getMobileNumber())
                      , "fee" => array("float", $trainer->getFee())
                      , "extra_infos" => array("float", $trainer->getExtraInfos())
                      , "active" => array("integer", $trainer->getActive())
                    );

        $this->getDB()->insert(self::TABLE_NAME, $values);

        return $trainer;
    }

    /**
     * @inheritdoc
     */
    public function select($id)
    {
        $query = "SELECT title, salutation, firstname, lastname, provider_id, email, phone, mobile_number, fee, extra_infos, active\n"
                . " FROM " . self::TABLE_NAME . "\n"
                . " WHERE id = " . $this->getDB()->quote($id, "integer");

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

    /**
     * @inheritdoc
     */
    public function update(\CaT\Plugins\TrainingProvider\Trainer\Trainer $trainer)
    {
        $where = array("id" => array("integer", $trainer->getId()));

        $values = array("title" => array("text", $trainer->getTitle())
                      , "salutation" => array("text", $trainer->getSalutation())
                      , "firstname" => array("text", $trainer->getFirstname())
                      , "lastname" => array("text", $trainer->getLastname())
                      , "provider_id" => array("text", $trainer->getProviderId())
                      , "email" => array("text", $trainer->getEmail())
                      , "phone" => array("text", $trainer->getPhone())
                      , "mobile_number" => array("text", $trainer->getMobileNumber())
                      , "fee" => array("float", $trainer->getFee())
                      , "extra_infos" => array("float", $trainer->getExtraInfos())
                      , "active" => array("integer", $trainer->getActive())
                    );

        $this->getDB()->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function delete($id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . "\n"
                . " WHERE id = " . $this->getDB()->quote($id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * Get trainer working for provider
     *
     * @param int 													$provider_id
     *
     * @return Trainer[] | []
     */
    public function getTrainerOf($provider_id)
    {
        $query = "SELECT id, title, salutation, firstname, lastname, email, phone, mobile_number, fee, extra_infos, active\n"
                . " FROM " . self::TABLE_NAME . "\n"
                . " WHERE provider_id = " . $this->getDB()->quote($provider_id, "integer");

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
                (float) $row["firstname"],
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
    public function getTrainersRaw($provider_id, array $active_filter = array())
    {
        $where = null;
        if ($provider_id) {
            $where = " WHERE train.provider_id = " . $this->getDB()->quote($provider_id, "integer") . "\n";
        }

        if ($active_filter && array($active_filter) > 0) {
            if (!$where) {
                $where = " WHERE ";
            } else {
                $where .= "     AND ";
            }

            $where .= $this->getDB()->in("train.active", $active_filter, false, "integer");
        }

        $query = "SELECT train.id, train.lastname, train.firstname, train.title,\n"
                . " train.email, train.phone, train.mobile_number, train.fee, train.extra_infos, train.active\n"
                . " , prov.name AS provider\n"
                . " FROM " . self::TABLE_NAME . " train\n"
                . " JOIN " . self::TABLE_PROVIDER . " prov\n"
                . "     ON train.provider_id = prov.id\n"
                . $where;

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
     *
     * @param int 		$provider_id
     */
    public function deleteByProvider($provider_id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . "\n"
                . " WHERE provider_id = " . $this->getDB()->quote($provider_id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * Creates needed tables
     */
    protected function createTable()
    {
        if (!$this->getDB()->tableExists(self::TABLE_NAME)) {
            $fields = array(
                    "id" => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    "firstname" => array(
                        'type' => 'text',
                        'length' => 32,
                        'notnull' => true
                    ),
                    "lastname" => array(
                        'type' => 'text',
                        'length' => 32,
                        'notnull' => true
                    ),
                    "provider_id" => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    ),
                    "email" => array(
                        'type' => 'text',
                        'length' => 32,
                        'notnull' => false
                    ),
                    "phone" => array(
                        'type' => 'text',
                        'length' => 32,
                        'notnull' => false
                    ),
                    "fee" => array(
                        'type' => 'float',
                        'notnull' => false
                    ),
                    "active" => array(
                        'type' => 'integer',
                        'length' => 1,
                        'notnull' => true
                    )
                );

            $this->getDB()->createTable(self::TABLE_NAME, $fields);
            $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("id"));
        }
    }

    /**
     * Update columns of table
     *
     * @return null
     */
    public function updateTable1()
    {
        $attributes = array('type' => 'text',
                             'length' => 128,
                             'notnull' => false
                );
        $this->getDB()->modifyTableColumn(self::TABLE_NAME, "email", $attributes);
    }

    /**
     * Update columns of table
     *
     * @return null
     */
    public function updateTable2()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "title")) {
            $attributes = array('type' => 'text',
                             'length' => 64,
                             'notnull' => false
                );
            $this->getDB()->addTableColumn(self::TABLE_NAME, "title", $attributes);
        }

        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "salutation")) {
            $attributes = array('type' => 'text',
                             'length' => 32,
                             'notnull' => false
                );
            $this->getDB()->addTableColumn(self::TABLE_NAME, "salutation", $attributes);
        }

        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "mobile_number")) {
            $attributes = array('type' => 'text',
                             'length' => 64,
                             'notnull' => false
                );
            $this->getDB()->addTableColumn(self::TABLE_NAME, "mobile_number", $attributes);
        }

        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "costs")) {
            $attributes = array('type' => 'float',
                                'notnull' => false
                );
            $this->getDB()->addTableColumn(self::TABLE_NAME, "costs", $attributes);
        }
    }

    /**
     * Update columns of table
     *
     * @return null
     */
    public function updateTable3()
    {
        $attributes = array('type' => 'text',
                             'length' => 256,
                             'notnull' => false
                );
        if ($this->getDB()->tableColumnExists(self::TABLE_NAME, "costs")) {
            $this->getDB()->renameTableColumn(self::TABLE_NAME, "costs", "extra_infos");
        }

        if ($this->getDB()->tableColumnExists(self::TABLE_NAME, "extra_infos")) {
            $this->getDB()->modifyTableColumn(self::TABLE_NAME, "extra_infos", $attributes);
        }
    }

    /**
     * Creates needed sequences
     */
    protected function createSequence()
    {
        if (!$this->getDB()->sequenceExists(self::TABLE_NAME)) {
            $this->getDB()->createSequence(self::TABLE_NAME);
        }
    }

    /**
     * Get the DB handler
     *
     * @return \ilDB
     */
    protected function getDB()
    {
        if ($this->db === null) {
            throw new \Exception("No databse defined in trainer db implementation");
        }

        return $this->db;
    }

    /**
     * Get the next id for new provider
     *
     * @return int
     */
    protected function getNextId()
    {
        return (int) $this->getDB()->nextId(self::TABLE_NAME);
    }
}
