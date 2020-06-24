<?php
namespace CaT\Plugins\Accounting\Data;

/**
 * Databaseclas for Data
 *
 * @author Daniel Weise 	<daniel.weise@concepts-and-training.de>
 */
class ilDB implements DB
{
    const XACC_DATA_TABLE = "xacc_data";
    const XACC_CONFIG_COSTTYPE = "xacc_config_costtype";
    const XACC_CONFIG_VATRATE = "xacc_config_vat_rate";

    /**
     * @var \ilDB
     */
    private $db;

    /**
     * Constructor of the class ilDB
     *
     * @param \ilDBInterface 		$db 		database object
     * @param \ilObjUser 			$user 		user object
     */
    public function __construct(\ilDBInterface $db, \ilObjUser $user)
    {
        $this->db = $db;
        $this->user = $user;
    }

    /**
     * @inheritdoc
     */
    public function install()
    {
        $this->createTable();
    }

    /**
     * @inheritdoc
     */
    public function read()
    {
        $entries = [];
        $query = "SELECT dt.id,
						  dt.obj_id,
						  dt.costtype,
						  dt.bill_date,
						  dt.nr,
						  dt.date_relay,
						  dt.issuer,
						  dt.bill_comment,
						  dt.amount,
						  dt.vatrate,
						  vr.label AS 'vr_label',
						  cc.label AS 'cc_label',
						  vr.val_float AS 'vr_value',
						  cc.val_str AS 'cc_value'\n"
                 . "FROM " . self::XACC_DATA_TABLE . " dt\n"
                 . "JOIN " . self::XACC_CONFIG_COSTTYPE . " cc\n"
                 . "ON dt.costtype = cc.id\n"
                 . "JOIN " . self::XACC_CONFIG_VATRATE . " vr\n"
                 . "ON dt.vatrate = vr.id\n"
                 . "WHERE dt.obj_id = " . $this->getDB()->quote($obj_id, 'integer');

        $result = $this->getDB()->query($query);

        $pos = 0;
        while ($row = $this->getDB()->fetchAssoc($result)) {
            $entries[] = new Data(
                (int) $row['id'],
                $pos,
                (int) $row['obj_id'],
                (int) $row['costtype'],
                new \ilDate($row['bill_date'], IL_CAL_DATE),
                $row['nr'],
                new \ilDate($row['date_relay'], IL_CAL_DATE),
                $row['issuer'],
                $row['bill_comment'],
                (float) $row['amount'],
                (int) $row['vatrate'],
                $row['cc_label'],
                $row['vr_label'],
                $row['cc_value'],
                $row['vr_value']
            );

            $pos++;
        }
        return $entries;
    }

    /**
     * @inheritdoc
     */
    public function insert(Data $data)
    {
        $id = $this->getDB()->nextId(self::XACC_DATA_TABLE);
        $obj_id = $data->getObjId();
        $costtype = $data->getCostType();
        $bill_date = $data->getBillDate();
        $nr = $data->getNr();
        $date_relay = $data->getDateRelay();
        $issuer = $data->getIssuer();
        $bill_comment = $data->getBillComment();
        $amount = $data->getAmount();
        $vatrate = $data->getvatrate();

        $values = ["id" => ["integer", $id],
                   "obj_id" => ["integer", $obj_id],
                   "costtype" => ["integer", $costtype],
                   "bill_date" => ["date", $bill_date],
                   "nr" => ["text", $nr],
                   "date_relay" => ["date", $date_relay],
                   "issuer" => ["text", $issuer],
                   "bill_comment" => ["text", $bill_comment],
                   "amount" => ["float", $amount],
                   "vatrate" => ["integer", $vatrate]];

        $this->getDB()->insert(self::XACC_DATA_TABLE, $values);
    }

    /**
     * @inheritdoc
     */
    public function update(Data $data)
    {
        $id = $data->getId();
        $obj_id = $data->getObjId();
        $costtype = $data->getCostType();
        $bill_date = $data->getBillDate();
        $nr = $data->getNr();
        $date_relay = $data->getDateRelay();
        $issuer = $data->getIssuer();
        $bill_comment = $data->getBillComment();
        $amount = $data->getAmount();
        $vatrate = $data->getvatrate();

        $values = ["obj_id" => ["integer", $obj_id],
                   "costtype" => ["integer", $costtype],
                   "bill_date" => ["date", $bill_date],
                   "nr" => ["text", $nr],
                   "date_relay" => ["date", $date_relay],
                   "issuer" => ["text", $issuer],
                   "bill_comment" => ["text", $bill_comment],
                   "amount" => ["float", $amount],
                   "vatrate" => ["integer", $vatrate]];

        $where = ["id" => ["integer", $id]];

        $this->getDB()->update(self::XACC_DATA_TABLE, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function deleteFor($obj_id)
    {
        $query = "DELETE FROM\n"
                . self::XACC_DATA_TABLE . "\n"
                . "WHERE id = " . $this->getDB()->quote($obj_id, 'integer');
        $this->getDB()->query($query);
    }

    /**
     * @inheritdoc
     */
    public function selectFor($obj_id)
    {
        $entries = [];
        $query = "SELECT dt.id,
						  dt.obj_id,
						  dt.costtype,
						  dt.bill_date,
						  dt.nr,
						  dt.date_relay,
						  dt.issuer,
						  dt.bill_comment,
						  dt.amount,
						  dt.vatrate,
						  vr.label AS 'vr_label',
						  cc.label AS 'cc_label',
						  vr.val_float AS 'vr_value',
						  cc.val_str AS 'cc_value'\n"
                 . "FROM " . self::XACC_DATA_TABLE . " dt\n"
                 . "JOIN " . self::XACC_CONFIG_COSTTYPE . " cc\n"
                 . "ON dt.costtype = cc.id\n"
                 . "JOIN " . self::XACC_CONFIG_VATRATE . " vr\n"
                 . "ON dt.vatrate = vr.id\n"
                 . "WHERE dt.obj_id = " . $this->getDB()->quote($obj_id, 'integer') . PHP_EOL
                 . "ORDER BY cc_label";

        $result = $this->getDB()->query($query);

        $pos = 0;
        while ($row = $this->getDB()->fetchAssoc($result)) {
            $entries[] = new Data(
                (int) $row['id'],
                $pos,
                (int) $row['obj_id'],
                (int) $row['costtype'],
                new \ilDate($row['bill_date'], IL_CAL_DATE),
                $row['nr'],
                new \ilDate($row['date_relay'], IL_CAL_DATE),
                $row['issuer'],
                $row['bill_comment'],
                (float) $row['amount'],
                (int) $row['vatrate'],
                $row['cc_label'],
                $row['vr_label'],
                $row['cc_value'],
                (float) $row['vr_value']
            );
            $pos++;
        }
        return $entries;
    }

    /**
     * Get numeber of DB entries
     *
     * @return int
     */
    public function getNumDBEntries()
    {
        $query = "SELECT * FROM " . self::XACC_DATA_TABLE;
        return $this->getDB()->numRows($this->getDB()->query($query));
    }

    /**
     * Get the net sum of data amount
     *
     * @param int 	$obj_id
     *
     * @return float
     */
    public function getNetSum(int $obj_id)
    {
        $query = "SELECT SUM(amount) AS net" . PHP_EOL
                . " FROM " . self::XACC_DATA_TABLE . PHP_EOL
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id . "integer");

        $res = $this->getDB()->query($query);
        $row = $this->getDB()->fetchAssoc($res);

        return (float) $row["net"];
    }

    /**
     * Get the gross sum of data amount
     *
     * @param int 	$obj_id
     *
     * @return float
     */
    public function getGrossSum(int $obj_id)
    {
        $query = "SELECT ROUND(SUM(data.amount * ((100 + vatrate.val_float) / 100)), 2) AS gross" . PHP_EOL
                . " FROM " . self::XACC_DATA_TABLE . " AS data" . PHP_EOL
                . " JOIN " . self::XACC_CONFIG_VATRATE . " as vatrate" . PHP_EOL
                . "     ON data.vatrate = vatrate.id" . PHP_EOL
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id . "integer");

        $res = $this->getDB()->query($query);
        $row = $this->getDB()->fetchAssoc($res);

        return (float) $row["gross"];
    }

    /**
     * Create table xacc_config_vat_rate
     */
    private function createTable()
    {
        // create table xacc_data
        if (!$this->getDB()->tableExists(self::XACC_DATA_TABLE)) {
            $fields = array('id' => array('type' => 'integer',
                                                     'length' => 4,
                                                     'notnull' => true
                                                    ),
                            'obj_id' => array('type' => 'integer',
                                                     'length' => 4,
                                                     'notnull' => true
                                                     ),
                            'costtype' => array('type' => 'text',
                                                     'length' => 500,
                                                     'notnull' => true
                                                     ),
                            'bill_date' => array('type' => 'date',
                                                     'notnull' => true
                                                     ),
                            'nr' => array('type' => 'text',
                                                     'length' => 100,
                                                     'notnull' => true
                                                     ),
                            'date_relay' => array('type' => 'date'
                                                     ),
                            'issuer' => array('type' => 'text',
                                                     'length' => 500,
                                                     'notnull' => true
                                                     ),
                            'bill_comment' => array('type' => 'text',
                                                     'length' => 800
                                                     ),
                            'amount' => array('type' => 'float',
                                                     'notnull' => true
                                                     ),
                            'tax' => array('type' => 'integer',
                                                     'length' => 4,
                                                     'notnull' => true
                                                     )
                            );
            $this->getDB()->createTable(self::XACC_DATA_TABLE, $fields);
            $this->getDB()->addPrimaryKey(self::XACC_DATA_TABLE, array('id'));
            $this->getDB()->createSequence(self::XACC_DATA_TABLE);
        }
    }

    public function update1()
    {
        $this->getDB()->renameTableColumn(self::XACC_DATA_TABLE, 'tax', 'vatrate');
    }

    public function update2()
    {
        $this->getDB()->modifyTableColumn('xacc_data', 'vatrate', array("type" => "integer", "length" => 4));
        $this->getDB()->modifyTableColumn('xacc_data', 'costtype', array("type" => "integer", "length" => 4));
    }

    public function update3()
    {
        $this->getDB()->modifyTableColumn('xacc_data', 'bill_date', array("type" => "date", "notnull" => false));
    }

    /**
     * Get the db handler
     *
     * @throws \Exception
     *
     * @return \ilDB
     */
    protected function getDB()
    {
        if (!$this->db) {
            throw new \Exception("no Database");
        }
        return $this->db;
    }
}
