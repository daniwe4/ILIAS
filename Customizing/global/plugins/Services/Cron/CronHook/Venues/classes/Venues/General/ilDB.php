<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\General;

use \CaT\Plugins\Venues\ObjectFactory;

/**
 * Implementation of DB inteface for general configuration
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    use ObjectFactory;

    const TABLE_NAME = "venues_general";

    /**
     * @var \ilDBInterface
     */
    protected $db = null;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function create(
        int $id,
        string $name,
        string $homepage = "",
        array $tags = array(),
        array $search_tags = array()
    ) : General {
        $general = new General($id, $name, $homepage, $tags, $search_tags);
        $values = array("id" => array("integer", $general->getId()),
                "name" => array("text", $general->getName()),
                "homepage" => array("text", $general->getHomepage())
            );

        $this->db->insert(self::TABLE_NAME, $values);

        return $general;
    }

    /**
     * @inheritdoc
     */
    public function update(General $general)
    {
        $where = array("id" => array("integer", $general->getId()));

        $values = array("name" => array("text", $general->getName()),
                "homepage" => array("text", $general->getHomepage())
        );

        $this->db->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function delete(int $id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . "\n"
                . " WHERE id = " . $this->db->quote($id, "integer");

        $this->db->manipulate($query);
    }

    /**
     * Create the table for general configuration
     *
     * @return void
     */
    public function createTable()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $fields = array(
                    "id" => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    "name" => array(
                        'type' => 'text',
                        'length' => 64,
                        'notnull' => true
                    ),
                    "homepage" => array(
                        'type' => 'text',
                        'length' => 64,
                        'notnull' => false
                    )
                );


            $this->db->createTable(self::TABLE_NAME, $fields);
        }
    }

    /**
     * First db update step
     *
     * @return void
     */
    public function update1()
    {
        if ($this->db->tableColumnExists(self::TABLE_NAME, "homepage")) {
            $field = array(
                'type' => 'text',
                'length' => 255,
                'notnull' => false
            );

            $this->db->modifyTableColumn(self::TABLE_NAME, "homepage", $field);
        }
    }

    /**
     * Create the primary key for table
     *
     * @return void
     */
    public function createPrimary()
    {
        $this->db->addPrimaryKey(self::TABLE_NAME, array("id"));
    }
}
