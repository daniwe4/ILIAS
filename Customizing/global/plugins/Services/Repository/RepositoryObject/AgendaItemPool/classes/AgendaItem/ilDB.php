<?php declare(strict_types=1);

namespace CaT\Plugins\AgendaItemPool\AgendaItem;

use CaT\Plugins\AgendaItemPool\Helper;

/**
 * Class ilDB.
 * Manage the database operations for AgendaItems.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class ilDB implements DB
{
    use Helper;

    const TABLE_AGENDA_ITEMS = "xaip_agenda_items";
    const TABLE_TOPICS = "xaip_topic";
    const TABLE_MEDIA = "xaip_media";
    const TABLE_METHODS = "xaip_method";
    const TABLE_TARGET_GROUPS = "xaip_target_group";

    /**
     * @var ilDBInterface
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
    public function create(
        string $title,
        \DateTime $last_change,
        int $change_usr_id,
        int $pool_id,
        string $description,
        bool $is_active,
        bool $idd_relevant,
        bool $is_deleted,
        bool $is_blank,
        array $training_topics,
        string $goals,
        string $gdv_learning_content,
        string $idd_learning_content,
        string $agenda_item_content
    ) {
        $next_id = (int) $this->getDB()->nextID(self::TABLE_AGENDA_ITEMS);

        $agenda_item = new AgendaItem(
            $next_id,
            $title,
            $description,
            $is_active,
            $idd_relevant,
            $is_deleted,
            $last_change,
            $change_usr_id,
            $pool_id,
            $is_blank,
            $training_topics,
            $goals,
            $gdv_learning_content,
            $idd_learning_content,
            $agenda_item_content
        );

        $title = $this->escapeQuestionMark($agenda_item->getTitle());

        $values = array(
            'obj_id' => ['integer', $agenda_item->getObjId()],
            'title' => ['text', $title],
            'description' => ['text', $agenda_item->getDescription()],
            'is_active' => ['integer', $agenda_item->getIsActive()],
            'idd_relevant' => ['integer', $agenda_item->getIddRelevant()],
            'is_deleted' => ['integer', $agenda_item->getIsDeleted()],
            'last_change' => ['text', $agenda_item->getLastChange()->format("Y-m-d H:i:s")],
            'change_usr_id' => ['integer', $agenda_item->getChangeUsrId()],
            'pool_id' => ['integer',$agenda_item->getPoolId()],
            'is_blank' => ['integer', $agenda_item->getIsBlank()],
            'goals' => ['text', $agenda_item->getGoals()],
            'gdv_learning_content' => ['text', $agenda_item->getGDVLearningContent()],
            'idd_learning_content' => ['text', $agenda_item->getIDDLearningContent()],
            'agenda_item_content' => ['text', $agenda_item->getAgendaItemContent()]
        );
        $this->getDB()->insert(self::TABLE_AGENDA_ITEMS, $values);
        $this->assignTopics($agenda_item->getObjId(), $agenda_item->getTrainingTopics());

        return $agenda_item;
    }

    /**
     * @inheritdoc
     */
    public function update(AgendaItem $agenda_item)
    {
        $obj_id = $agenda_item->getObjId();
        $where = ['obj_id' => ['integer', $obj_id]];

        $title = $this->escapeQuestionMark($agenda_item->getTitle());

        $values = array(
            'title' => ['text', $title],
            'description' => ['text', $agenda_item->getDescription()],
            'is_active' => ['integer', $agenda_item->getIsActive()],
            'idd_relevant' => ['integer', $agenda_item->getIddRelevant()],
            'is_deleted' => ['integer', $agenda_item->getIsDeleted()],
            'last_change' => ['text', $agenda_item->getLastChange()->format("Y-m-d H:i:s")],
            'change_usr_id' => ['integer', $agenda_item->getChangeUsrId()],
            'pool_id' => ['integer',$agenda_item->getPoolId()],
            'is_blank' => ['integer', $agenda_item->getIsBlank()],
            'goals' => ['text', $agenda_item->getGoals()],
            'gdv_learning_content' => ['text', $agenda_item->getGDVLearningContent()],
            'idd_learning_content' => ['text', $agenda_item->getIDDLearningContent()],
            'agenda_item_content' => ['text', $agenda_item->getAgendaItemContent()]
        );
        $this->getDB()->update(self::TABLE_AGENDA_ITEMS, $values, $where);

        $this->assignTopics($obj_id, $agenda_item->getTrainingTopics());
    }

    /**
     * @inheritdoc
     */
    public function selectFor(int $obj_id)
    {
        $query = "SELECT" . PHP_EOL
                . "    A.obj_id," . PHP_EOL
                . "    A.title," . PHP_EOL
                . "    A.description," . PHP_EOL
                . "    A.is_active," . PHP_EOL
                . "    A.idd_relevant," . PHP_EOL
                . "    A.is_deleted," . PHP_EOL
                . "    A.last_change," . PHP_EOL
                . "    A.change_usr_id," . PHP_EOL
                . "    A.pool_id," . PHP_EOL
                . "    A.is_blank," . PHP_EOL
                . "    GROUP_CONCAT(DISTINCT B.caption_id SEPARATOR ',') AS topics," . PHP_EOL
                . "    A.goals," . PHP_EOL
                . "    A.gdv_learning_content," . PHP_EOL
                . "    A.idd_learning_content," . PHP_EOL
                . "    A.agenda_item_content" . PHP_EOL
                . "FROM " . self::TABLE_AGENDA_ITEMS . " A" . PHP_EOL
                . "LEFT JOIN " . self::TABLE_TOPICS . " B" . PHP_EOL
                . "    ON A.obj_id = B.agenda_item_id" . PHP_EOL
                . "WHERE" . PHP_EOL
                . "    A.obj_id = " . $this->getDB()->quote($obj_id, 'integer') . PHP_EOL
                . "GROUP BY" . PHP_EOL
                . "    A.obj_id" . PHP_EOL
                ;
        $result = $this->getDB()->query($query);

        if ($this->getDB()->numRows($result) == 0) {
            throw new \LogicException("No AgendaItem found for obj_id " . $obj_id);
        }

        return $this->createAgendaItemObject($this->getDB()->fetchAssoc($result));
    }

    /**
     * @inheritdoc
     */
    public function deleteFor(array $obj_ids)
    {
        foreach ($obj_ids as $obj_id) {
            $this->assignTopics($obj_id, array());
        }

        $query = "DELETE FROM " . self::TABLE_AGENDA_ITEMS . PHP_EOL
                . "WHERE" . PHP_EOL
                . $this->getDB()->in("obj_id", $obj_ids, false, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * Delete AgendaItems for a pool_id.
     * @return 	void
     */
    public function deleteByPoolId(int $pool_id)
    {
        $query = "DELETE FROM " . self::TABLE_AGENDA_ITEMS . PHP_EOL
                . "WHERE" . PHP_EOL
                . "    pool_id = " . $this->getDB()->quote($pool_id, "integer") . PHP_EOL
                ;
        $this->getDB()->manipulate($query);
    }

    /**
     * Return all AgendaItems for pool id.
     * @return 	AgendaItem[]
     */
    public function getAllAgendaItemsByPoolId(int $pool_id, int $offset = null, int $limit = null)
    {
        $ret = array();

        $query = "SELECT" . PHP_EOL
                . "    A.obj_id," . PHP_EOL
                . "    A.title," . PHP_EOL
                . "    A.description," . PHP_EOL
                . "    A.is_active," . PHP_EOL
                . "    A.idd_relevant," . PHP_EOL
                . "    A.is_deleted," . PHP_EOL
                . "    A.last_change," . PHP_EOL
                . "    A.change_usr_id," . PHP_EOL
                . "    A.pool_id," . PHP_EOL
                . "    A.is_blank," . PHP_EOL
                . "    GROUP_CONCAT(DISTINCT B.caption_id SEPARATOR ',') AS topics," . PHP_EOL
                . "    A.goals," . PHP_EOL
                . "    A.gdv_learning_content," . PHP_EOL
                . "    A.idd_learning_content," . PHP_EOL
                . "    A.agenda_item_content" . PHP_EOL
                . "FROM " . self::TABLE_AGENDA_ITEMS . " A" . PHP_EOL
                . "LEFT JOIN " . self::TABLE_TOPICS . " B" . PHP_EOL
                . "    ON A.obj_id = B.agenda_item_id" . PHP_EOL
                . "WHERE" . PHP_EOL
                . "    A.pool_id = " . $this->getDB()->quote($pool_id, 'integer') . PHP_EOL
                . "GROUP BY" . PHP_EOL
                . "    A.obj_id" . PHP_EOL
                ;

        if (!is_null($offset) && !is_null($limit)) {
            $query .= "LIMIT " . $limit . " OFFSET " . $offset;
        }

        $result = $this->getDB()->query($query);

        while ($row = $this->getDB()->fetchAssoc($result)) {
            $ret[] = $this->createAgendaItemObject($row);
        }

        return $ret;
    }

    /**
     * Return all AgendaItems for pool id.
     * @return 	int
     */
    public function getAllAgendaItemsCountByPoolId(int $pool_id) : int
    {
        $ret = array();

        $query = "SELECT" . PHP_EOL
                . "    A.obj_id" . PHP_EOL
                . "FROM " . self::TABLE_AGENDA_ITEMS . " A" . PHP_EOL
                . "LEFT JOIN " . self::TABLE_TOPICS . " B" . PHP_EOL
                . "    ON A.obj_id = B.agenda_item_id" . PHP_EOL
                . "WHERE" . PHP_EOL
                . "    A.pool_id = " . $this->getDB()->quote($pool_id, 'integer') . PHP_EOL
                . "GROUP BY" . PHP_EOL
                . "    A.obj_id" . PHP_EOL
                ;

        $result = $this->getDB()->query($query);
        return (int) $this->getDB()->numRows($result);
    }

    /**
     * Return all AgendaItems.
     *
     * @return 	AgendaItem[]
     */
    public function getAllAgendaItems() : array
    {
        $ret = array();

        $query = "SELECT" . PHP_EOL
                . "    A.obj_id," . PHP_EOL
                . "    A.title," . PHP_EOL
                . "    A.description," . PHP_EOL
                . "    A.is_active," . PHP_EOL
                . "    A.idd_relevant," . PHP_EOL
                . "    A.is_deleted," . PHP_EOL
                . "    A.last_change," . PHP_EOL
                . "    A.change_usr_id," . PHP_EOL
                . "    A.pool_id," . PHP_EOL
                . "    A.is_blank," . PHP_EOL
                . "    GROUP_CONCAT(DISTINCT B.caption_id SEPARATOR ',') AS topics," . PHP_EOL
                . "    A.goals," . PHP_EOL
                . "    A.gdv_learning_content," . PHP_EOL
                . "    A.idd_learning_content," . PHP_EOL
                . "    A.agenda_item_content" . PHP_EOL
                . "FROM " . self::TABLE_AGENDA_ITEMS . " A" . PHP_EOL
                . "LEFT JOIN " . self::TABLE_TOPICS . " B" . PHP_EOL
                . "    ON A.obj_id = B.agenda_item_id" . PHP_EOL
                . "WHERE A.is_active = 1" . PHP_EOL
                . "GROUP BY A.obj_id"
                ;
        $result = $this->getDB()->query($query);

        while ($row = $this->getDB()->fetchAssoc($result)) {
            $ret[] = $this->createAgendaItemObject($row);
        }

        return $ret;
    }

    /**
     * Checks whether id is a obj_id.
     */
    public function isValidObjId(int $id) : bool
    {
        $query = "SELECT obj_id" . PHP_EOL
                . "FROM " . self::TABLE_AGENDA_ITEMS . PHP_EOL
                . "WHERE obj_id = " . $this->getDB()->quote($id, "integer");

        $result = $this->getDB()->query($query);
        return $this->getDB()->numRows($result) != 0;
    }

    /**
     * Create a AgendaItem object from an assoziative array.
     *
     * @return 	AgendaItem
     */
    protected function createAgendaItemObject(array $row)
    {
        return new AgendaItem(
            (int) $row['obj_id'],
            (string) stripslashes($row['title']),
            (string) $row['description'],
            (bool) $row['is_active'],
            (bool) $row['idd_relevant'],
            (bool) $row['is_deleted'],
            new \DateTime($row['last_change'], new \DateTimeZone("Europe/Berlin")),
            (int) $row['change_usr_id'],
            (int) $row['pool_id'],
            (bool) $row['is_blank'],
            $this->transformGroupConcatString((string) $row["topics"]),
            (string) $row['goals'],
            (string) $row['gdv_learning_content'],
            (string) $row['idd_learning_content'],
            (string) $row['agenda_item_content']
        );
    }

    /**
     * Transform a comma seperated string to an int array.
     *
     * @param 	string
     * @return 	int[] | null
     */
    protected function transformGroupConcatString(string $group_concat_string) : array
    {
        if ($group_concat_string == "") {
            return array();
        }

        return array_map(function ($part) {
            return (int) $part;
        }, explode(",", $group_concat_string));
    }

    /**
     * Save assignes topics
     *
     * @param int[] | null 	$topics
     *
     * @return void
     */
    protected function assignTopics(int $obj_id, array $topics = null)
    {
        $this->deassign(self::TABLE_TOPICS, $obj_id);
        if ($topics !== null) {
            $this->assign(self::TABLE_TOPICS, $obj_id, $topics);
        }
    }

    /**
     * Assign caption_ids
     * @param int[] 	$captions_ids
     *
     * @return void
     */
    protected function assign(string $table_name, int $agenda_item_id, array $caption_ids)
    {
        foreach ($caption_ids as $caption_id) {
            $values = array(
                "agenda_item_id" => array("integer", $agenda_item_id),
                "caption_id" => array("integer", $caption_id)
            );
            $this->getDB()->insert($table_name, $values);
        }
    }

    /**
     * Deassign caption_ids
     *
     * @return void
     */
    protected function deassign(string $table_name, int $agenda_item_id)
    {
        $query = "DELETE FROM " . $table_name . PHP_EOL
                . "WHERE agenda_item_id = " . $this->getDB()->quote($agenda_item_id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * Create the table for agenda item.
     *
     * @return 	void
     */
    public function createAgendaItemTable()
    {
        if (!$this->getDB()->tableExists(self::TABLE_AGENDA_ITEMS)) {
            $fields = array(
                'obj_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'title' => array(
                    'type' => 'text',
                    'length' => 100,
                    'notnull' => true
                ),
                'description' => array(
                    'type' => 'clob',
                    'notnull' => false
                ),
                'is_active' => array(
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true
                ),
                'idd_relevant' => array(
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true
                ),
                'is_deleted' => array(
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true
                ),
                'last_change' => array(
                    'type' => 'text',
                    'length' => 19,
                    'notnull' => true
                ),
                'change_usr_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => false
                ),
                'pool_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => false
                ),
                'is_blank' => array(
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true
                ),
                'goals' => array(
                    'type' => 'clob',
                    'notnull' => false
                ),
                'gdv_learning_content' => array(
                    'type' => 'text',
                    'length' => 255,
                    'notnull' => false
                ),
                'idd_learning_content' => array(
                    'type' => 'text',
                    'length' => 255,
                    'notnull' => false
                ),
                'use_training_content' => array(
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true
                )
            );
            $this->getDB()->createTable(self::TABLE_AGENDA_ITEMS, $fields);
        }
    }

    /**
     * Create data tables.
     *
     * @return 	void
     */
    public function createDataTables()
    {
        $multi_select_tables = array(
            self::TABLE_TOPICS
        );

        foreach ($multi_select_tables as $table) {
            if (!$this->getDB()->tableExists($table)) {
                $fields = array(
                    'obj_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'option_id' => array(
                        'type' => 'text',
                        'length' => 128,
                        'notnull' => true
                    )
                );

                $this->getDB()->createTable($table, $fields);
            }
        }
    }

    /**
     * Create a primary key for table
     *
     * @return 	void
     */
    public function createPrimaryKey()
    {
        $this->getDB()->addPrimaryKey(self::TABLE_AGENDA_ITEMS, array('obj_id'));
    }

    /**
     * Create primary keys for data tables.
     *
     * @return 	void
     */
    public function createPrimaryKeyDataTables()
    {
        $multi_select_tables = array(
            self::TABLE_TOPICS
        );

        foreach ($multi_select_tables as $table) {
            $this->getDB()->addPrimaryKey($table, array("obj_id", "option_id"));
        }
    }

    /**
     * Create sequence for table.
     *
     * @return 	void
     */
    public function createSequence()
    {
        $this->getDB()->createSequence(self::TABLE_AGENDA_ITEMS);
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

    /**
     * Update 1
     */
    public function update1()
    {
        if ($this->getDB()->tableColumnExists(self::TABLE_AGENDA_ITEMS, "use_training_content")) {
            $this->getDB()->dropTableColumn(self::TABLE_AGENDA_ITEMS, "use_training_content");
        }

        if (!$this->getDB()->tableColumnExists(self::TABLE_AGENDA_ITEMS, "agenda_item_content")) {
            $this->getDB()->addTableColumn(
                self::TABLE_AGENDA_ITEMS,
                "agenda_item_content",
                array('type' => 'clob', 'notnull' => false)
            );
        }
    }

    /**
     * Escapes questionmarks for db entry
     */
    protected function escapeQuestionMark(string $value) : string
    {
        return addslashes($value);
    }
}
