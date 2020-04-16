<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\Settings;

/**
 * Implementation of the db interface.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_NAME = "xtep_settings";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function getDB() : \ilDBInterface
    {
        return $this->db;
    }

    public function createTable()
    {
        if (!$this->getDB()->tableExists(static::TABLE_NAME)) {
            $fields = [
                'obj_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                'role_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ]

            ];
            $this->getDB()->createTable(static::TABLE_NAME, $fields);
        }
    }

    public function createPrimaryKeys()
    {
        $this->getDB()->addPrimaryKey(self::TABLE_NAME, ['obj_id', 'role_id']);
    }

    public function update(Settings $settings)
    {
        $obj_id = $settings->getObjId();
        $roles = $settings->getGlobalRoles();

        $atom_query = $this->db->buildAtomQuery();
        $atom_query->addTableLock(self::TABLE_NAME);
        $atom_query->addQueryCallable(
            function (\ilDBInterface $db) use ($obj_id, $roles) {
                $this->deleteFor($obj_id, $db);
                foreach ($roles as $role_id) {
                    $this->addRole($obj_id, $role_id, $db);
                }
            }
        );
        $atom_query->run();
    }

    protected function addRole(int $obj_id, int $role_id, \ilDBInterface $db)
    {
        $query = "INSERT INTO " . self::TABLE_NAME . PHP_EOL
            . "(obj_id, role_id)" . PHP_EOL
            . "VALUES ("
            . $db->quote($obj_id, "integer") . ","
            . $db->quote($role_id, "integer")
            . ")";
        $db->manipulate($query);
    }

    public function createEmpty(int $obj_id) : Settings
    {
        return new Settings($obj_id, []);
    }

    public function selectFor(int $obj_id) : Settings
    {
        $query = "SELECT role_id"
                . " FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $res = $this->getDB()->query($query);
        $roles = [];
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $roles[] = (int) $row['role_id'];
        }

        return new Settings($obj_id, $roles);
    }

    public function deleteFor(int $obj_id, \ilDBInterface $db = null)
    {
        if (is_null($db)) {
            $db = $this->getDB();
        }
        $query = "DELETE FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE obj_id = " . $db->quote($obj_id, "integer");
        $db->manipulate($query);
    }
}
