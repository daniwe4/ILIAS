<?php

/* Copyright (c) 2018 Daniel Weise <daniel.weise@concepts-and-training.de> */
/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Accounting\Settings;

/**
 * Databaseclas for Settings
 */
class ilDB implements DB
{
    const XACC_OBJECTS_TABLE = "xacc_objects";

    /**
     * @var \ilDB
     */
    private $db;

    public function __construct(\ilDBInterface $db, \ilObjUser $user)
    {
        $this->db = $db;
        $this->user = $user;
    }

    /**
     * Installs all neccessary tables etc
     */
    public function install()
    {
        $this->createTable();
    }

    /**
     * @inheritdoc
     */
    public function insert(int $obj_id, bool $finalized, bool $edit_fee)
    {
        $values = [
            "obj_id" => ["integer", $obj_id],
            "finalized" => ["integer", $finalized],
            "edit_fee" => ["integer", $edit_fee]
        ];

        $this->db->insert(self::XACC_OBJECTS_TABLE, $values);
    }

    /**
     * @inheritdoc
     */
    public function update(Settings $settings)
    {
        $where = ["obj_id" => ["integer", $settings->getObjId()]];

        $values = [
            "finalized" => ["integer", $settings->getFinalized()],
            "edit_fee" => ["integer", $settings->getEditFee()]
        ];

        $this->db->update(self::XACC_OBJECTS_TABLE, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function selectFor(int $obj_id) : Settings
    {
        $query = "SELECT finalized, edit_fee\n"
                 . "FROM " . self::XACC_OBJECTS_TABLE . "\n"
                 . "WHERE obj_id = " . $this->db->quote($obj_id, "integer");
        $result = $this->db->query($query);

        if (empty($result)) {
            throw new LogicException(__METHOD__ . " no entry found!");
        }

        $row = $this->db->fetchAssoc($result);
        return new Settings(
            $obj_id,
            (bool) $row['finalized'],
            (bool) $row['edit_fee']
        );
    }

    /**
     * @inheritdoc
     */
    public function deleteFor(int $obj_id)
    {
        $query = "DELETE FROM " . self::XACC_OBJECTS_TABLE . "\n"
                . " WHERE obj_id = " . $this->db->quote($obj_id, "integer");

        $this->db->manipulate($query);
    }

    /**
     * Create table xacc_objects
     */
    private function createTable()
    {
        if (!$this->db->tableExists(self::XACC_OBJECTS_TABLE)) {
            $fields = array('obj_id' => array('type' => 'integer',
                                                     'length' => 4,
                                                     'notnull' => true
                                                     ),
                            'finalized' => array('type' => 'integer',
                                                     'length' => 1
                                                     )
                            );
            $this->db->createTable(self::XACC_OBJECTS_TABLE, $fields);
        }
    }

    /**
     * Set primary key on table
     *
     * @return null
     */
    public function setPrimaryKey()
    {
        $this->db->addPrimaryKey(self::XACC_OBJECTS_TABLE, array("obj_id"));
    }

    public function update1()
    {
        if (!$this->db->tableColumnExists(self::XACC_OBJECTS_TABLE, "edit_fee")) {
            $field = array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => false,
                'default' => 0
            );

            $this->db->addTableColumn(self::XACC_OBJECTS_TABLE, 'edit_fee', $field);
        }
    }
}
