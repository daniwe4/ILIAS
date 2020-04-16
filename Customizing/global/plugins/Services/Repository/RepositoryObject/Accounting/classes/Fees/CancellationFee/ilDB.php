<?php
/* Copyright (c) 2018 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Accounting\Fees\CancellationFee;

class ilDB implements DB
{
    const TABLE_NAME = "xacc_cancellation_fee";

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
    public function create(int $obj_id, float $fee_value = null) : CancellationFee
    {
        $cancellation_fee = new CancellationFee($obj_id, $fee_value);

        $values = array(
            "obj_id" => array("integer", $cancellation_fee->getObjId()),
            "cancellation_fee" => array("float", $cancellation_fee->getCancellationFee())
        );

        $this->getDB()->insert(self::TABLE_NAME, $values);

        return $cancellation_fee;
    }

    /**
     * @inheritdoc
     */
    public function update(CancellationFee $cancellation_fee)
    {
        $where = array("obj_id" => array("integer", $cancellation_fee->getObjId()));
        $values = array("cancellation_fee" => array("float", $cancellation_fee->getCancellationFee()));

        $this->getDB()->replace(self::TABLE_NAME, $where, $values);
    }

    /**
     * @inheritdoc
     */
    public function select(int $obj_id) : CancellationFee
    {
        $query =
             "SELECT cancellation_fee" . PHP_EOL
            . " FROM " . self::TABLE_NAME . PHP_EOL
            . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer") . PHP_EOL
        ;

        $res = $this->getDB()->query($query);

        if ($this->getDB()->numRows($res) == 0) {
            return new CancellationFee($obj_id);
        }

        $row = $this->getDB()->fetchAssoc($res);

        $cancellation_fee = $row["cancellation_fee"];
        if (!is_null($cancellation_fee)) {
            $cancellation_fee = (float) $cancellation_fee;
        }

        return new CancellationFee($obj_id, $cancellation_fee);
    }

    /**
     * @param int $crs_id
     * @return float|null
     */
    public function selectForCourse(int $crs_id)
    {
        $crs_id = $this->db->quote($crs_id, "integer");
        $query = <<<SQL
SELECT max_cancellation_fee FROM hhd_crs WHERE crs_id = $crs_id;
SQL;
        $res = $this->db->query($query);

        if ($this->db->numRows($res) == 0) {
            return null;
        }
        $row = $this->db->fetchAssoc($res);

        $max_cancellation_fee = $row["max_cancellation_fee"];
        if (!is_null($max_cancellation_fee)) {
            $max_cancellation_fee = (float) $max_cancellation_fee;
        }

        return $max_cancellation_fee;
    }

    /**
     * @inheritdoc
     */
    public function delete(int $obj_id)
    {
        $query =
             "DELETE FROM " . self::TABLE_NAME . PHP_EOL
            . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer") . PHP_EOL
        ;

        $this->getDB()->manipulate($query);
    }

    public function createTable()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $fields = array(
                'obj_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'cancellation_fee' => array(
                    'type' => 'float'
                )
            );
            $this->db->createTable(self::TABLE_NAME, $fields);
        }
    }

    public function setPrimaryKey()
    {
        $this->db->addPrimaryKey(self::TABLE_NAME, array("obj_id"));
    }

    protected function getDB() : \ilDBInterface
    {
        if (!$this->db) {
            throw new \Exception("no Database");
        }
        return $this->db;
    }
}
