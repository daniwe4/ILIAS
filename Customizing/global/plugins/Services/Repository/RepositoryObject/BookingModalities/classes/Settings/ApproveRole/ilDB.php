<?php

namespace CaT\Plugins\BookingModalities\Settings\ApproveRole;

/**
 * Implementation of db interface of approve role
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_APPROVERS = "xbkm_approvers";
    const TABLE_ROLES = "xbkm_roles";

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
    public function deleteApproveRoles($obj_id, $parent)
    {
        $query = "DELETE FROM " . self::TABLE_APPROVERS . "\n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer") . "\n"
                . "     AND parent = " . $this->getDB()->quote($parent, "text");

        $this->getDB()->manipulate($query);
    }

    /**
     * @inheritdoc
     */
    public function createApproveRole($obj_id, $parent, $position, $role_id)
    {
        assert('is_int($obj_id)');
        assert('is_string($parent)');
        assert('is_int($position)');
        assert('is_int($role_id)');
        return new ApproveRole($obj_id, $parent, $position, $role_id);
    }

    /**
     * @inheritdoc
     */
    public function createApproveRoles(array $approve_roles = null)
    {
        if ($approve_roles === null) {
            return;
        }

        foreach ($approve_roles as $key => $approve_role) {
            $values = array("obj_id" => array("int", $approve_role->getObjId()),
                "parent" => array("text", $approve_role->getParent()),
                "position" => array("integer", $approve_role->getPosition()),
                "role" => array("integer", $approve_role->getRoleId()),
            );
            $this->getDB()->insert(self::TABLE_APPROVERS, $values);
        }
    }

    /**
     * Creates tables for this plugin
     *
     * @return null
     */
    public function createTable1()
    {
        if (!$this->getDB()->tableExists(self::TABLE_APPROVERS)) {
            $fields =
                array('obj_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'role_name' => array(
                        'type' => 'text',
                        'length' => 255,
                        'notnull' => true
                    ),
                    'position' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    )
                );

            $this->getDB()->createTable(self::TABLE_APPROVERS, $fields);
        }
    }

    /**
     * Add column to table
     *
     * @return null
     */
    public function updateTabe1()
    {
        $field = array('type' => 'text',
                        'length' => 16,
                        'notnull' => true
                    );

        if (!$this->getDB()->tableColumnExists(self::TABLE_APPROVERS, "parent")) {
            $this->getDB()->addTableColumn(self::TABLE_APPROVERS, "parent", $field);
        }
    }

    /**
     * Create primary key for member
     *
     * @return null
     */
    public function createApproversPrimaryKey()
    {
        $this->getDB()->addPrimaryKey(self::TABLE_APPROVERS, array("obj_id"));
    }

    /**
     * Modify approvers primary key
     */
    public function modifyApproversPrimaryKey()
    {
        $query = "ALTER TABLE " . self::TABLE_APPROVERS . " DROP PRIMARY KEY";
        $this->getDB()->manipulate($query);
        $this->getDB()->addPrimaryKey(self::TABLE_APPROVERS, array("obj_id", "role_name", "parent"));
    }

    /**
     * Get options for role selections
     *
     * @return int[]
     */
    public function getRoleOptions()
    {
        $query = "SELECT role\n"
                . " FROM " . self::TABLE_ROLES . "\n";

        $res = $this->getDB()->query($query);
        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[$row["role"]] = $row["role"];
        }

        return $ret;
    }

    public function getSortedPositionsAscending(int $obj_id)
    {
        $query =
             "SELECT role, position" . PHP_EOL
            . "FROM " . self::TABLE_APPROVERS . PHP_EOL
            . "WHERE obj_id = " . $obj_id . PHP_EOL
            . "ORDER BY position ASC" . PHP_EOL
        ;

        $res = $this->getDB()->query($query);
        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[$row["position"]] = (int) $row["role"];
        }

        return $ret;
    }

    /**
     * Get intance of db
     *
     * @throws \Exception
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

    public function updateTable2()
    {
        if ($this->getDB()->tableColumnExists(self::TABLE_APPROVERS, "role_name")) {
            $this->getDB()->renameTableColumn(self::TABLE_APPROVERS, "role_name", "role");
            $this->getDB()->modifyTableColumn(self::TABLE_APPROVERS, "role", array(
                "type" => "integer",
                "length" => 4
            ));
        }
    }
}
