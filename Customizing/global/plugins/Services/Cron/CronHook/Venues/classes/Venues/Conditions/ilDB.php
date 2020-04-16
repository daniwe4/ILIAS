<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Conditions;

use \CaT\Plugins\Venues\ObjectFactory;

/**
 * Implementation of DB inteface for rating configuration
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    use ObjectFactory;

    const TABLE_NAME = "venues_conditions";

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
        bool $general_agreement = false,
        string $terms = "",
        string $valuta = ""
    ) : Conditions {
        $conditions = new Conditions($id, $general_agreement, $terms, $valuta);

        $values = array("id" => array("integer", $conditions->getId()),
                "general_agreement" => array("integer", $conditions->getGeneralAgreement()),
                "terms" => array("text", $conditions->getTerms()),
                "valuta" => array("text", $conditions->getValuta())
            );

        $this->db->insert(self::TABLE_NAME, $values);

        return $conditions;
    }

    /**
     * @inheritdoc
     */
    public function update(Conditions $conditions)
    {
        $where = array("id" => array("integer", $conditions->getId()));

        $values = array("general_agreement" => array("integer", $conditions->getGeneralAgreement()),
                "terms" => array("text", $conditions->getTerms()),
                "valuta" => array("text", $conditions->getValuta())
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
     * Create the table for conditions configuration
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
                    "general_agreement" => array(
                        'type' => 'integer',
                        'length' => 1,
                        'notnull' => false
                    ),
                    "terms" => array(
                        'type' => 'clob',
                        'notnull' => false
                    ),
                    "valuta" => array(
                        'type' => 'text',
                        'length' => 32,
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
