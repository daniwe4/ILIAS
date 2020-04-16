<?php
namespace CaT\Plugins\Accounting\Config\VatRate;

/**
 * Databaseclass for VatRate
 *
 * @author Daniel Weise 	<daniel.weise@concepts-and-training.de>
 */
class ilDB implements DB
{
    const XACC_CONFIG_TABLE = "xacc_config_vat_rate";
    const XACC_DATA_TABLE = "xacc_data";

    /**
     * @var \ilDB
     */
    private $db;

    /**
     * @var \ilUser
     */
    private $user;

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
        $query = "SELECT id, val_float, label, active\n"
                 . "FROM " . self::XACC_CONFIG_TABLE;
        $result = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($result)) {
            $entries[] = new VatRate((int) $row['id'], (string) $row['val_float'], (string) $row['label'], (bool) $row['active']);
        }
        return $entries;
    }


    /**
     * @inheritdoc
     */
    public function insert($value, $label, $active)
    {
        $id = (int) $this->db->nextId(self::XACC_CONFIG_TABLE);
        $vr = new VatRate($id, $value, $label, $active);
        $values = ["id" => ["integer", $id],
                   "label" => ["text", $label],
                   "val_float" => ["float", $value],
                   "active" => ["integer", $active]];

        $this->db->insert(self::XACC_CONFIG_TABLE, $values);
        return $vr;
    }

    /**
     * @inheritdoc
     */
    public function update(VatRate $vatrate)
    {
        $id = $vatrate->getId();
        $label = $vatrate->getLabel();
        $value = (float) $vatrate->getValue();
        $active = $vatrate->getActive();

        $query = "SELECT val_float\n"
                 . "FROM " . self::XACC_CONFIG_TABLE . "\n"
                 . "WHERE id = " . $this->db->quote($id, "integer");

        $result = $this->db->query($query);
        $row = $this->db->fetchAssoc($result);

        $values = ["label" => ["text", $label],
                   "val_float" => ["text", $value],
                   "active" => ["integer", $active]];

        $where = ["id" => ["integer", $id]];
        $this->db->update(self::XACC_CONFIG_TABLE, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function getSelectionArray()
    {
        $entries = [];
        $query = "SELECT id, label FROM " . self::XACC_CONFIG_TABLE . "\n"
                 . "WHERE active = 1";
        $result = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($result)) {
            $entries[(int) $row['id']] = (string) $row['label'];
        }
        return $entries;
    }

    /**
     * @inheritdoc
     */
    public function deleteFor($obj_id)
    {
        $query = "DELETE FROM\n"
                . self::XACC_CONFIG_TABLE . "\n"
                . "WHERE id = " . $this->db->quote($obj_id, 'integer');
        $this->db->query($query);
    }

    /**
     * Check for existing relationships
     *
     * @param integer 	$id 	id of a costtype object
     * @return boolean
     */
    public function hasRelationships($id)
    {
        assert('is_int($id)');
        $query = "SELECT vatrate\n"
                 . "FROM " . self::XACC_DATA_TABLE . "\n"
                 . "WHERE vatrate = " . $this->db->quote($id, "string");

        $result = $this->db->query($query);

        if ($this->db->numRows($result)) {
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getVatRateValueById($id)
    {
        assert('is_int($id)');
        $query = "SELECT val_float\n"
                 . "FROM " . self::XACC_CONFIG_TABLE . "\n"
                 . "WHERE id = " . $this->db->quote($id, "integer");

        $result = $this->db->query($query);
        if ($this->db->numRows($result) == 0) {
            return 0;
        }

        $row = $this->db->fetchAssoc($result);
        return (float) $row["val_float"];
    }

    /**
     * Get label for vatrate type
     *
     * @param 	integer 		$id
     * @return 	string
     */
    public function getVRLabel($id)
    {
        assert('is_int($id)');
        $query = "SELECT label\n"
                . "FROM " . self::XACC_CONFIG_TABLE . "\n"
                . "WHERE id = " . $this->db->quote($id, "integer");

        $result = $this->db->query($query);
        return $this->db->fetchAssoc($result)['label'];
    }

    /**
     * Create table xacc_config_vat_rate
     */
    private function createTable()
    {
        if (!$this->db->tableExists(self::XACC_CONFIG_TABLE)) {
            $fields = array('id' => array('type' => 'integer',
                                                     'length' => 4,
                                                     'notnull' => true
                                                    ),
                            'label' => array('type' => 'text',
                                                     'length' => 500,
                                                     'notnull' => true
                                                     ),
                            'val_int' => array('type' => 'integer'
                                                     )
                            );
            $this->db->createTable(self::XACC_CONFIG_TABLE, $fields);
            $this->db->addPrimaryKey(self::XACC_CONFIG_TABLE, array('id'));
            $this->db->createSequence(self::XACC_CONFIG_TABLE);
        }
    }
}
