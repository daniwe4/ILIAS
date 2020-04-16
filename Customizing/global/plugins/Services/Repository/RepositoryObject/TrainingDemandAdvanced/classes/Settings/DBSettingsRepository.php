<?php

namespace CaT\Plugins\TrainingDemandAdvanced\Settings;

class DBSettingsRepository implements SettingsRepository
{
    const TABLE = 'xtda_settings';
    const TABLE_LOCAl_ROLES = 'xtda_local_roles';
    const ROW_ID = 'id';
    const ROW_ONLINE = 'is_online';
    const ROW_GLOBAL = 'is_global';
    const ROW_OBJ_ID = 'obj_id';
    const ROW_LOCAL_ROLE = 'local_role';

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
    public function update(Settings $settings)
    {
        $obj_id = $settings->id();
        if (!$this->exists($obj_id)) {
            throw new \InvalidArgumentException(
                'A dataset with id '
                . $obj_id
                . ' does not exist'
            );
        }

        $this->db->update(
            self::TABLE,
            [
                self::ROW_ONLINE => ['integer', $settings->online()]
                ,self::ROW_GLOBAL => ['integer', $settings->isGlobal()]
            ],
            [
                self::ROW_ID => ['integer',$obj_id]
            ]
        );

        $this->clearLocalRoles($obj_id);
        $local_roles = $settings->getLocalRoles();
        if (count($local_roles) > 0) {
            $this->addLocalRoles($obj_id, $local_roles);
        }
    }

    /**
     * @inheritdoc
     */
    public function create(int $obj_id) : Settings
    {
        if ($this->exists($obj_id)) {
            throw new \InvalidArgumentException(
                'A dataset with id '
                . $obj_id
                . ' allready exists'
            );
        }

        $settings = new Settings($obj_id, false, false, []);
        $this->db->insert(
            self::TABLE,
            [
                self::ROW_ID => ['integer', $settings->id()],
                self::ROW_ONLINE => ['integer', $settings->online()],
                self::ROW_GLOBAL => ['integer', $settings->isGlobal()]
            ]
        );

        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function load(int $obj_id) : Settings
    {
        $q = "SELECT base." . self::ROW_ONLINE . ", base." . self::ROW_GLOBAL . "," . PHP_EOL
            . " GROUP_CONCAT(local_roles." . self::ROW_LOCAL_ROLE . " SEPARATOR ';') AS " . self::ROW_LOCAL_ROLE . PHP_EOL
            . " FROM " . self::TABLE . " base" . PHP_EOL
            . " LEFT JOIN " . self::TABLE_LOCAl_ROLES . " local_roles" . PHP_EOL
            . "     ON local_roles." . self::ROW_OBJ_ID . " = base." . self::ROW_ID . PHP_EOL
            . " WHERE base." . self::ROW_ID . " = " . $this->db->quote($obj_id, "integer") . PHP_EOL
            . " GROUP BY base." . self::ROW_ID
        ;

        $res = $this->db->query($q);

        if ($this->db->numRows($res) == 0) {
            return $this->create($obj_id);
        }

        $rec = $this->db->fetchAssoc($res);
        $roles = [];
        $local_roles = $rec[self::ROW_LOCAL_ROLE];
        if (!is_null($local_roles)) {
            $roles = explode(";", $local_roles);
        }
        return new Settings(
            $obj_id,
            (bool) $rec[self::ROW_ONLINE],
            (bool) $rec[self::ROW_GLOBAL],
            $roles
        );
    }

    /**
     * @inheritdoc
     */
    public function delete(Settings $settings)
    {
        $obj_id = $settings->id();
        if (!$this->exists($obj_id)) {
            throw new \InvalidArgumentException('A dataset with id ' . $obj_id . ' doesn\'t exist');
        }
        $this->db->manipulate(
            'DELETE FROM ' . self::TABLE
            . '	WHERE ' . self::ROW_ID . ' = ' . $this->db->quote($obj_id, 'integer')
        );
        $this->clearLocalRoles($obj_id);
    }

    protected function clearLocalRoles(int $obj_id)
    {
        $q = "DELETE FROM " . self::TABLE_LOCAl_ROLES . PHP_EOL
            . " WHERE " . self::ROW_OBJ_ID . " = " . $this->db->quote($obj_id, "integer")
        ;

        $this->db->manipulate($q);
    }

    protected function addLocalRoles(
        int $obj_id,
        array $local_roles
    ) {
        $q = "INSERT INTO " . self::TABLE_LOCAl_ROLES . PHP_EOL
            . " (" . self::ROW_ID . ", " . self::ROW_OBJ_ID . ", " . self::ROW_LOCAL_ROLE . ")" . PHP_EOL
            . " VALUES (?, ?, ?)"
        ;

        $prepared_statement = $this->db->prepare($q, ['integer', 'text']);
        foreach ($local_roles as $local_role) {
            $values = [
                $this->db->nextId(self::TABLE_LOCAl_ROLES),
                $obj_id,
                $local_role
            ];
            $this->db->execute($prepared_statement, $values);
        }
    }

    /**
     * @inheritdoc
     */
    protected function exists(int $obj_id) : bool
    {
        $q = 'SELECT ' . self::ROW_ID . PHP_EOL
            . ' FROM ' . self::TABLE . PHP_EOL
            . ' WHERE ' . self::ROW_ID . ' = ' . $this->db->quote($obj_id, 'integer')
        ;

        $res = $this->db->query($q);
        return $this->db->numRows($res) > 0;
    }
}
