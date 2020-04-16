<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Rating;

use \CaT\Plugins\Venues\ObjectFactory;

/**
 * Implementation of DB inteface for rating configuration
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    use ObjectFactory;

    const TABLE_NAME = "venues_rating";

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
        float $rating = 0.0,
        string $info = ""
    ) : Rating {
        $rating = new Rating($id, $rating, $info);

        $values = array("id" => array("integer", $rating->getId()),
                "rating" => array("float", $rating->getRating()),
                "info" => array("text", $rating->getInfo())
            );

        $this->db->insert(self::TABLE_NAME, $values);

        return $rating;
    }

    /**
     * @inheritdoc
     */
    public function update(Rating $rating)
    {
        $where = array("id" => array("integer", $rating->getId()));

        $values = array("rating" => array("float", $rating->getRating()),
                "info" => array("text", $rating->getInfo())
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
     * Create the table for rating configuration
     *
     * @return null
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
                    "rating" => array(
                        'type' => 'float',
                        'notnull' => false
                    ),
                    "info" => array(
                        'type' => 'clob',
                        'notnull' => false
                    )
                );


            $this->db->createTable(self::TABLE_NAME, $fields);
        }
    }

    /**
     * Create the primary key for table
     *
     * @return null
     */
    public function createPrimary()
    {
        $this->db->addPrimaryKey(self::TABLE_NAME, array("id"));
    }
}
