<?php

namespace CaT\Plugins\Accounting\Fees\Fee;

class ilDB implements DB
{
    const TABLE_NAME = "xacc_fee";

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
    public function create(int $obj_id, float $fee_value = null)
    {
        $fee = new Fee($obj_id, $fee_value);

        $values = array(
            "obj_id" => array("integer", $fee->getObjId())
            , "fee_value" => array("float", $fee->getFee())
        );

        $this->getDB()->insert(self::TABLE_NAME, $values);

        return $fee;
    }

    /**
     * @inheritdoc
     */
    public function update(Fee $fee)
    {
        $where = array("obj_id" => array("integer", $fee->getObjId()));
        $values = array("fee_value" => array("float", $fee->getFee()));

        $this->getDB()->replace(self::TABLE_NAME, $where, $values);
    }

    /**
     * @inheritdoc
     */
    public function select(int $obj_id)
    {
        $query = "SELECT fee_value" . PHP_EOL
                . " FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $res = $this->getDB()->query($query);

        if ($this->getDB()->numRows($res) == 0) {
            return new Fee($obj_id);
        }

        $row = $this->getDB()->fetchAssoc($res);

        $fee_value = $row["fee_value"];
        if (!is_null($fee_value)) {
            $fee_value = (float) $fee_value;
        }

        return new Fee($obj_id, $fee_value);
    }

    /**
     * @inheritdoc
     */
    public function delete(int $obj_id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * Create fee table
     *
     * @return void
     */
    public function createTable()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $fields = array(
                'obj_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'fee_value' => array(
                    'type' => 'float'
                )
            );
            $this->db->createTable(self::TABLE_NAME, $fields);
        }
    }

    /**
     * Set primary key on table
     *
     * @return null
     */
    public function setPrimaryKey()
    {
        $this->db->addPrimaryKey(self::TABLE_NAME, array("obj_id"));
    }

    /**
     * Get the current db object
     *
     * @throws \Exception if no db is set
     *
     * @return \ilDBInterface
     */
    protected function getDB()
    {
        if (!$this->db) {
            throw new \Exception("no Database");
        }
        return $this->db;
    }
}
