<?php
namespace CaT\Plugins\Accounting\Config\CostType;

/**
 * Databaseclass for CostTypes
 *
 * @author Daniel Weise 	<daniel.weise@concepts-and-training.de>
 */
class ilDB implements DB
{
    const XACC_CONFIG_TABLE = "xacc_config_costtype";
    const XACC_DATA_TABLE = "xacc_data";

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
        $query = "SELECT id, val_str, label, active\n"
                 . "FROM " . self::XACC_CONFIG_TABLE;
        $result = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($result)) {
            $entries[] = new CostType((int) $row['id'], (string) $row['val_str'], (string) $row['label'], (bool) $row['active']);
        }
        return $entries;
    }

    /**
     * @inheritdoc
     */
    public function insert($value, $label, $active)
    {
        $id = (int) $this->db->nextId(self::XACC_CONFIG_TABLE);

        $ct = new CostType($id, $value, $label, $active);
        $values = ["id" => ["integer", $id],
                   "label" => ["text", $label],
                   "val_str" => ["text", $value],
                   "active" => ["integer", $active]];

        $this->db->insert(self::XACC_CONFIG_TABLE, $values);
        return $ct;
    }

    /**
     * @inheritdoc
     */
    public function update(CostType $costtype)
    {
        $id = $costtype->getId();
        $label = $costtype->getLabel();
        $value = $costtype->getValue();
        $active = $costtype->getActive();

        $query = "SELECT val_str\n"
                 . "FROM " . self::XACC_CONFIG_TABLE . "\n"
                 . "WHERE id = " . $this->db->quote($id, "integer");

        $result = $this->db->query($query);
        $row = $this->db->fetchAssoc($result);

        $values = ["label" => ["text", $label],
                   "val_str" => ["text", $value],
                   "active" => ["integer", $active]];

        $where = ["id" => ["integer", $id]];
        $this->db->update(self::XACC_CONFIG_TABLE, $values, $where);

        $values = ["costtype" => ["text", $value]];
        $where = ["costtype" => ["text", $row["val_str"]]];
        $this->db->update(self::XACC_DATA_TABLE, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function getSelectionArray()
    {
        $entries = [];
        $query = "SELECT id, label\n"
                 . "FROM " . self::XACC_CONFIG_TABLE . "\n"
                 . "WHERE active = 1";
        $result = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($result)) {
            $entries[$row['id']] = (string) $row['label'];
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
        $query = "SELECT costtype\n"
                 . "FROM " . self::XACC_DATA_TABLE . "\n"
                 . "WHERE costtype = " . $this->db->quote($id, "integer");

        $result = $this->db->query($query);

        if ($this->db->numRows($result)) {
            return true;
        }
        return false;
    }

    /**
     * Get ctLabel for given costtype
     *
     * @param 	integer 	$costtype
     * @return 	string
     */
    public function getCTLabel($costtype)
    {
        $query = "SELECT label\n"
                . "FROM " . self::XACC_CONFIG_TABLE . "\n"
                . "WHERE id = " . $this->db->quote($costtype, "integer");

        $result = $this->db->query($query);
        return $this->db->fetchAssoc($result)['label'];
    }

    /**
     * Get value for cost type
     *
     * @param 	integer 		$costtype
     * @return 	string
     */
    public function getCTValue($costtype)
    {
        $query = "SELECT val_str" . PHP_EOL
            . "FROM " . self::XACC_CONFIG_TABLE . PHP_EOL
            . "WHERE id = " . $this->db->quote($costtype, "integer");

        $result = $this->db->query($query);
        return $this->db->fetchAssoc($result)['val_str'];
    }

    /**
     * Create table xacc_config_costtype
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
                            'val_str' => array('type' => 'text',
                                                     'length' => 500
                                                     )
                            );
            $this->db->createTable(self::XACC_CONFIG_TABLE, $fields);
            $this->db->addPrimaryKey(self::XACC_CONFIG_TABLE, array('id'));
            $this->db->createSequence(self::XACC_CONFIG_TABLE);
        }
    }
}
