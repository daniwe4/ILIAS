<?php

namespace CaT\Plugins\BookingModalities\Settings\SelectableRoles;

/**
 * Implementation of the db interface
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_NAME = "xbkm_roles";
    const START_EMPLOYEE_ROLE = "il_orgu_employee";
    const START_SUPERIOR_ROLE = "il_orgu_superior";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        global $DIC;
        $this->g_rbacreview = $DIC->rbac()->review();
        $this->db = $db;
    }

    /**
     * Create assign table
     *
     * @return null
     */
    public function createTable()
    {
        if (!$this->getDB()->tableExists(static::TABLE_NAME)) {
            $fields =
                array('role_name' => array(
                        'type' => 'text',
                        'length' => 255,
                        'notnull' => true
                    )
                );
            $this->getDB()->createTable(static::TABLE_NAME, $fields);
        }
    }

    /**
     * @inheritdoc
     */
    public function unassignRoles()
    {
        $query = "DELETE FROM " . self::TABLE_NAME . "\n";
        $this->getDB()->manipulate($query);
    }

    /**
     * @inheritdoc
     */
    public function assignRoles(array $roles)
    {
        foreach ($roles as $role) {
            $values = array("role" => array("integer", $role));
            $this->getDB()->insert(self::TABLE_NAME, $values);
        }
    }

    /**
     * @inheritdoc
     */
    public function select()
    {
        $query = "SELECT role\n"
                . " FROM " . self::TABLE_NAME . "\n";

        $res = $this->getDB()->query($query);
        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = $row["role"];
        }

        return $ret;
    }

    /**
     * Get options of roles for multiselect input
     *
     * @return string[]
     */
    public function getRoleOptions()
    {
        require_once("Modules/OrgUnit/classes/Positions/class.ilOrgUnitPosition.php");
        $ret = array();
        foreach (\ilOrgUnitPosition::get() as $pos) {
            $pos_id = $pos->getId();
            $pos_name = $pos->getTitle();
            $ret[$pos_id] = $pos_name;
        }

        return $ret;
    }

    /**
     * Get intance of db
     *
     * @throws \Exceptio
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

    public function update1()
    {
        if ($this->getDB()->tableColumnExists(self::TABLE_NAME, "role_name")) {
            $this->getDB()->renameTableColumn(self::TABLE_NAME, "role_name", "role");
            $this->getDB()->modifyTableColumn(self::TABLE_NAME, "role", array(
                "type" => "integer",
                "length" => 4
            ));
        }
    }
}
