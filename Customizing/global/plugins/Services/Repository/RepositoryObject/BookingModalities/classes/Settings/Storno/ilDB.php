<?php

namespace CaT\Plugins\BookingModalities\Settings\Storno;

use CaT\Plugins\BookingModalities\Settings;

/**
 * Interface for DB handle of additional setting values
 */
class ilDB implements DB
{
    const TABLE_STORNO = "xbkm_storno";
    const TABLE_APPROVERS = "xbkm_approvers";

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
    public function create($obj_id)
    {
        assert('is_int($obj_id)');
        $storno = new Storno($obj_id);
        $values = array("obj_id" => array("integer", $storno->getObjId()));
        $this->getDB()->insert(self::TABLE_STORNO, $values);
        return $storno;
    }

    /**
     * @inheritdoc
     */
    public function update(Storno $storno_settings)
    {
        $where = array("obj_id" => array("integer", $storno_settings->getObjId()));

        $values = array("deadline" => array("integer", $storno_settings->getDeadline()),
            "hard_deadline" => array("integer", $storno_settings->getHardDeadline()),
            "modus" => array("text", $storno_settings->getModus()),
            "reason_type" => array("text", $storno_settings->getReasonType()),
            "reason_optional" => array("integer", $storno_settings->getReasonOptional())
        );

        $this->getDB()->update(self::TABLE_STORNO, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function selectFor($obj_id)
    {
        assert('is_int($obj_id)');
        $query = "SELECT storno.deadline, storno.hard_deadline, storno.modus, storno.reason_type, storno.reason_optional," . PHP_EOL
            . " GROUP_CONCAT(DISTINCT CONCAT_WS('&&', roles.parent, roles.position, roles.role) SEPARATOR '||') AS roles" . PHP_EOL
            . " FROM " . self::TABLE_STORNO . " storno" . PHP_EOL
            . " LEFT JOIN " . self::TABLE_APPROVERS . " roles" . PHP_EOL
            . "     ON storno.obj_id = roles.obj_id" . PHP_EOL
            . "         AND roles.parent = 'storno'" . PHP_EOL
            . " WHERE storno.obj_id = " . $this->getDB()->quote($obj_id, "integer") . PHP_EOL
            . " GROUP BY storno.obj_id" . PHP_EOL;

        $res = $this->getDB()->query($query);

        if ($this->getDB()->numRows($res) == 0) {
            return new Storno($obj_id);
        }

        $storno = null;
        $approve_roles = [];
        $reasons = [];
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $storno = new Storno($obj_id, (int) $row["deadline"], (int) $row["hard_deadline"], $row["modus"], $row["reason_type"], array(), (bool) $row["reason_optional"]);

            if ($row["roles"] !== null && trim($row["roles"]) != "") {
                $roles = explode("||", $row["roles"]);
                foreach ($roles as $role) {
                    $vals = explode('&&', $role);
                    $approve_roles[] = new Settings\ApproveRole\ApproveRole($obj_id, $vals[0], (int) $vals[1], $vals[2]);
                }

                usort($approve_roles, array($this, "compareRoleAccordingToPosition"));
            }
        }

        return $storno->withApproveRoles($approve_roles);
    }

    /**
     * @inheritdoc
     */
    public function deleteFor($obj_id)
    {
        assert('is_int($obj_id)');
        $query = "DELETE FROM " . self::TABLE_STORNO . PHP_EOL
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");
        $this->getDB()->manipulate($query);
    }

    /**
     * Creates tables for this plugin
     *
     * @return null
     */
    public function createTable1()
    {
        if (!$this->getDB()->tableExists(self::TABLE_STORNO)) {
            $fields =
                array('obj_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'deadline' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    ),
                    'hard_deadline' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    ),
                    'modus' => array(
                        'type' => 'text',
                        'length' => 32,
                        'notnull' => false
                    )
                );

            $this->getDB()->createTable(self::TABLE_STORNO, $fields);
        }
    }

    /**
     * Update table
     *
     * @return void
     */
    public function update1()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_STORNO, "reason_type")) {
            $field = array('type' => 'text',
                    'length' => 255,
                    'notnull' => false
                );
            $this->getDB()->addTableColumn(self::TABLE_STORNO, "reason_type", $field);
        }
    }

    /**
     * Update table
     *
     * @return void
     */
    public function update2()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_STORNO, "reason_optional")) {
            $field = array('type' => 'integer',
                    'length' => 1
                );
            $this->getDB()->addTableColumn(self::TABLE_STORNO, "reason_optional", $field);
        }
    }

    /**
     * Create primary key for storno
     *
     * @return null
     */
    public function createStornoPrimaryKey()
    {
        $this->getDB()->addPrimaryKey(self::TABLE_STORNO, array("obj_id"));
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

    /**
     * Compare roles and reasons according position
     *
     * @param Settings\ApproveRole\ApproveRole | Settings\Reasons\Reason 	$a
     * @param Settings\ApproveRole\ApproveRole | Settings\Reasons\Reason 	$b
     *
     * @return bool
     */
    protected function compareRoleAccordingToPosition($a, $b)
    {
        return strcmp((string) $a->getPosition(), (string) $b->getPosition());
    }
}
