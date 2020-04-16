<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Costs;

use \CaT\Plugins\Venues\ObjectFactory;

/**
 * Inteface for costs configuration DB
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    use ObjectFactory;

    const TABLE_NAME = "venues_costs";
    const TABLE_GENERAL = "venues_general";

    /**
     * @var \ilDBInterface
     */
    protected $db = null;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function create(
        int $id,
        float $fixed_rate_day = null,
        float $fixed_rate_all_inclusive = null,
        float $bed_and_breakfast = null,
        float $bed = null,
        float $fixed_rate_conference = null,
        float $room_usage = null,
        float $other = null,
        string $terms = ""
    ) : Costs {
        $costs = new Costs(
            $id,
            $fixed_rate_day,
            $fixed_rate_all_inclusive,
            $bed_and_breakfast,
            $bed,
            $fixed_rate_conference,
            $room_usage,
            $other,
            $terms
            );

        $values = array("id" => array("integer", $costs->getId()),
                "fixed_rate_day" => array("float", $costs->getFixedRateDay()),
                "fixed_rate_all_inclusive" => array("float", $costs->getFixedRateAllInclusiv()),
                "bed_and_breakfast" => array("float", $costs->getBedAndBreakfast()),
                "bed" => array("float", $costs->getBed()),
                "fixed_rate_conference" => array("float", $costs->getFixedRateConference()),
                "room_usage" => array("float", $costs->getRoomUsage()),
                "other" => array("float", $costs->getOther()),
                "terms" => array("text", $costs->getTerms())
            );

        $this->db->insert(self::TABLE_NAME, $values);

        return $costs;
    }

    public function update(Costs $costs)
    {
        $where = array("id" => array("integer", $costs->getId()));

        $values = array("fixed_rate_day" => array("float", $costs->getFixedRateDay()),
                "fixed_rate_all_inclusive" => array("float", $costs->getFixedRateAllInclusiv()),
                "bed_and_breakfast" => array("float", $costs->getBedAndBreakfast()),
                "bed" => array("float", $costs->getBed()),
                "fixed_rate_conference" => array("float", $costs->getFixedRateConference()),
                "room_usage" => array("float", $costs->getRoomUsage()),
                "other" => array("float", $costs->getOther()),
                "terms" => array("text", $costs->getTerms())
            );

        $this->db->update(self::TABLE_NAME, $values, $where);
    }

    public function delete(int $id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . "\n"
                . " WHERE id = " . $this->db->quote($id, "integer");

        $this->db->manipulate($query);
    }

    /**
     * Create the table for contact configuration
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
                    "fixed_rate_day" => array(
                        'type' => 'float',
                        'notnull' => false
                    ),
                    "fixed_rate_all_inclusive" => array(
                        'type' => 'float',
                        'notnull' => false
                    ),
                    "bed_and_breakfast" => array(
                        'type' => 'float',
                        'notnull' => false
                    ),
                    "other" => array(
                        'type' => 'float',
                        'notnull' => false
                    ),
                    "terms" => array(
                        'type' => 'text',
                        'length' => 256,
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

    /**
     * Update step 1
     *
     * @return null
     */
    public function update1()
    {
        if ($this->db->tableColumnExists(self::TABLE_NAME, "terms")) {
            $this->db->modifyTableColumn(
                self::TABLE_NAME,
                "terms",
                array(
                    'type' => 'text',
                    'length' => 500,
                    'notnull' => false
                )
            );
        }
    }

    /**
     * Migration Step 1
     *
     * @return null
     */
    public function migrate1()
    {
        $query = "SELECT A.id AS general, B.id AS topic\n"
                . " FROM " . self::TABLE_GENERAL . " A\n"
                . " LEFT JOIN " . self::TABLE_NAME . " B\n"
                . "     ON A.id = B.id\n"
                . " HAVING topic IS NULL";

        $res = $this->db->query($query);
        $ret = array();
        while ($row = $this->db->fetchAssoc($res)) {
            $this->create((int) $row["general"]);
        }
    }

    /**
     * Update step 2
     */
    public function update2()
    {
        if (!$this->db->tableColumnExists(self::TABLE_NAME, "bed")) {
            $this->db->addTableColumn(
                self::TABLE_NAME,
                "bed",
                array(
                    'type' => 'float',
                    'notnull' => false
                    )
                );
        }
        if (!$this->db->tableColumnExists(self::TABLE_NAME, "fixed_rate_conference")) {
            $this->db->addTableColumn(
                self::TABLE_NAME,
                "fixed_rate_conference",
                array(
                    'type' => 'float',
                    'notnull' => false
                    )
                );
        }
        if (!$this->db->tableColumnExists(self::TABLE_NAME, "room_usage")) {
            $this->db->addTableColumn(
                self::TABLE_NAME,
                "room_usage",
                array(
                    'type' => 'float',
                    'notnull' => false
                    )
                );
        }
    }
}
