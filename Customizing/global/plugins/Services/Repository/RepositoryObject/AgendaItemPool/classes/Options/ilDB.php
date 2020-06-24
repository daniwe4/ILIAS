<?php

namespace CaT\Plugins\AgendaItemPool\Options;

/**
 * Abstract class ilDB.
 * Provides an ilDB template class for option dbs.
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
abstract class ilDB implements DB
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Install tables, view, or entries
     *
     * @return null
     */
    public function install()
    {
        $this->createTable();
    }

    /**
     * Configure primary keys on tables
     *
     * @return null
     */
    public function configurePrimaryKeys()
    {
        $this->getDB()->addPrimaryKey(static::TABLE_NAME, array("agenda_item_id", "caption_id"));
    }

    /**
     * @inheritdoc
     */
    public function create(int $agenda_item_id, string $caption_id) : Option
    {
        $option = new Option($agenda_item_id, $caption_id);

        $values = array(
            "agenda_item_id" => array("integer" . $option->getAgendaItemId()),
            "caption_id" => array("text", $option->getCaption())
        );

        $this->getDB()->insert(static::TABLE_NAME, $values);

        return $option;
    }

    /**
     * @inheritdoc
     */
    public function select(int $agenda_item_id) : array
    {
        $query = "SELECT agenda_item_id, caption_id\n"
                . " FROM " . static::TABLE_NAME . "\n"
                . " WHERE agenda_item_id = " . $this->getDB()->quote($agenda_item_id, "integer");

        $result = $this->getDB()->query($query);
        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($result)) {
            $option = new Option((int) $row["agenda_item_id"], (int) $row["caption_id"]);
            $ret[] = $option;
        }

        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function delete(int $agenda_item_id, int $caption_id) : void
    {
        $query = "DELETE FROM " . static::TABLE_NAME . PHP_EOL
                . "WHERE" . PHP_EOL
                . "    agenda_item_id = " . $this->getDB()->quote($agenda_item_id, "integer") . PHP_EOL
                . "AND" . PHP_EOL
                . "    caption_id = " . $this->getDB()->quote($caption_id, "integer") . PHP_EOL
                ;

        $this->getDB()->manipulate($query);
    }

    /**
     * Create table for options
     *
     * @return null
     */
    protected function createTable()
    {
        if (!$this->getDB()->tableExists(static::TABLE_NAME)) {
            $fields =
                array('agenda_item_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'caption_id' => array(
                        'type' => 'text',
                        'length' => 4,
                        'notnull' => true
                    )
                );

            $this->getDB()->createTable(static::TABLE_NAME, $fields);
        }
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
}
