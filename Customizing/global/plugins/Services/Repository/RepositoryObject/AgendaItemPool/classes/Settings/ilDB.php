<?php
namespace CaT\Plugins\AgendaItemPool\Settings;

/**
 * Class ilDB
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class ilDB implements DB
{
    const TABLE_SETTINGS = "xaip_settings";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * Constructor of the class ilDB
     *
     * @return 	void
     */
    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function create(int $obj_id)
    {
        $settings = new Settings($obj_id, false, null, null);

        $values = array(
            'obj_id' => ['integer', $settings->getObjId()],
            'is_online' => ['integer', $settings->getIsOnline()],
            'last_changed' => ['text', $settings->getLastChanged()],
            'last_changed_usr_id' => ['integer', $settings->getLastChangedUsrId()]
        );
        $this->getDB()->insert(self::TABLE_SETTINGS, $values);

        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function update(Settings $settings)
    {
        $last_changed = null;
        if ($settings->getLastChanged() !== null) {
            $last_changed = $settings->getLastChanged()->format("Y-m-d H:i:s");
        }
        $obj_id = $settings->getObjId();
        $where = ['obj_id' => ['integer', $obj_id]];
        $values = array(
            'is_online' => ['integer', $settings->getIsOnline()],
            'last_changed' => ['text', $last_changed],
            'last_changed_usr_id' => ['integer', $settings->getLastChangedUsrId()]
        );
        $this->getDB()->update(self::TABLE_SETTINGS, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function selectFor(int $obj_id)
    {
        $query = "SELECT" . PHP_EOL
                . "    obj_id," . PHP_EOL
                . "    is_online," . PHP_EOL
                . "    last_changed," . PHP_EOL
                . "    last_changed_usr_id" . PHP_EOL
                . "FROM " . self::TABLE_SETTINGS . PHP_EOL
                . "WHERE" . PHP_EOL
                . "    obj_id = " . $this->getDB()->quote($obj_id, 'integer') . PHP_EOL
                ;
        $result = $this->getDB()->query($query);

        if ($this->getDB()->numRows($result) == 0) {
            throw new \LogicException("No Settings found for obj_id " . $obj_id);
        }

        return $this->createSettingsObject($this->getDB()->fetchAssoc($result));
    }

    /**
     * @inheritdoc
     */
    public function deleteFor(int $obj_id)
    {
        $query = "DELETE FROM " . self::TABLE_SETTINGS . PHP_EOL
                . "WHERE" . PHP_EOL
                . "    obj_id = " . $this->getDB()->quote($obj_id, 'integer') . PHP_EOL
                ;
        $this->getDB()->manipulate($query);
    }

    /**
     * Get obj_ids.
     *
     * @return 	int[]
     */
    public function getObjIds()
    {
        $ids = array();
        $query = "SELECT" . PHP_EOL
                . "    obj_id" . PHP_EOL
                . "FROM " . self::TABLE_SETTINGS . PHP_EOL
                ;
        $result = $this->getDB()->query($query);

        while ($row = $this->getDB()->fetchAssoc($result)) {
            $ids[] = (int) $row['obj_id'];
        }
        return $ids;
    }

    /**
     * Create an Settings object from an assoziative array.
     *
     * @param 	array 	$row
     * @return 	Settings
     */
    public function createSettingsObject(array $row)
    {
        return new Settings(
            (int) $row['obj_id'],
            (bool) $row['is_online'],
            new \DateTime($row['last_changed'], new \DateTimeZone("Europe/Berlin")),
            (int) $row['last_changed_usr_id']
        );
    }

    /**
     * Check whether the AgendaItemPool is active.
     *
     * @param 	int 	$id
     * @return 	bool
     */
    public function isActive(int $id)
    {
        $ai = $this->selectFor($id);
        return $ai->getIsOnline();
    }

    /**
     * Create the table for settings.
     *
     * @return 	void
     */
    public function createSettingsTable()
    {
        if (!$this->getDB()->tableExists(self::TABLE_SETTINGS)) {
            $fields = array(
                'obj_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'is_online' => array(
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true
                ),
                'last_changed' => array(
                    'type' => 'text',
                    'length' => 19,
                    'notnull' => false
                ),
                'last_changed_usr_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => false
                )
            );
            $this->getDB()->createTable(self::TABLE_SETTINGS, $fields);
        }
    }

    /**
     * Create a primary key for table
     *
     * @return 	void
     */
    public function createPrimaryKey()
    {
        $this->getDB()->addPrimaryKey(self::TABLE_SETTINGS, array('obj_id'));
    }

    /**
     * Create sequence for table.
     *
     * @return 	void
     */
    public function createSequence()
    {
        $this->getDB()->createSequence(self::TABLE_SETTINGS);
    }

    /**
     * Get instance of db
     *
     * @return 	\ilDBInterface
     */
    private function getDB()
    {
        if (!$this->db) {
            throw new \Exception("no database");
        }
        return $this->db;
    }
}
