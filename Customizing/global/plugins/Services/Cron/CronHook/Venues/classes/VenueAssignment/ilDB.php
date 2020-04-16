<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace CaT\Plugins\Venues\VenueAssignment;

/**
 * Implementation of VenueAssingment database interface
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_NAME = "venues_assignment";

    /**
     * @var \ilDB
     */
    protected $db = null;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function createListVenueAssignment(
        int $crs_id,
        int $venue_id,
        string $venue_additional = null
    ) : ListAssignment {
        $va = new ListAssignment($crs_id, $venue_id, $venue_additional);
        $values = array(
            "crs_id" => array("integer", $va->getCrsId()),
            "venue_id" => array("integer", $va->getVenueId()),
            "venue_text" => array('text', null),
            "venue_additional" => array('text', $va->getAdditionalInfo())
        );
        $this->db->insert(self::TABLE_NAME, $values);
        return $va;
    }

    /**
     * @inheritdoc
     */
    public function createCustomVenueAssignment(int $crs_id, string $text) : CustomAssignment
    {
        $va = new CustomAssignment($crs_id, $text);
        $values = array(
            "crs_id" => array("integer", $va->getCrsId()),
            "venue_id" => array('integer', null),
            "venue_text" => array("text", $text)
        );
        $this->db->insert(self::TABLE_NAME, $values);
        return $va;
    }

    /**
     * @inheritdoc
     */
    public function update(VenueAssignment $venue_assignment)
    {
        $where = array(
            "crs_id" => array("integer", $venue_assignment->getCrsId())
        );
        if ($venue_assignment->isListAssignment()) {
            $values = array(
                "venue_id" => array("integer", $venue_assignment->getVenueId()),
                "venue_text" => array('text', null),
                "venue_additional" => array('text', $venue_assignment->getAdditionalInfo())
            );
        }
        if ($venue_assignment->isCustomAssignment()) {
            $values = array(
                "venue_id" => array('integer', null),
                "venue_text" => array("text", $venue_assignment->getVenueText()),
            );
        }
        $this->db->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function select(int $crs_id)
    {
        $query = "SELECT crs_id, venue_id, venue_text, venue_additional" . PHP_EOL
            . " FROM " . self::TABLE_NAME . PHP_EOL
            . " WHERE crs_id=" . $this->db->quote($crs_id, "integer");

        $res = $this->db->query($query);

        if ($this->db->numRows($res) == 0) {
            return false;
        }

        $row = $this->db->fetchAssoc($res);
        $venue_text = $row["venue_text"];
        if (trim($venue_text) == "") {
            $venue_text = null;
        }

        $venue_id = $row["venue_id"];
        if (trim($venue_id) == "") {
            $venue_id = null;
        }

        if (!is_null($venue_id) && is_null($venue_text)) {
            return new ListAssignment(
                (int) $crs_id,
                (int) $venue_id,
                $row["venue_additional"]
            );
        }

        if (is_null($venue_id) && !is_null($venue_text)) {
            return new CustomAssignment(
                (int) $crs_id,
                $venue_text
            );
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function delete(int $crs_id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . "\n"
                . " WHERE crs_id = " . $this->db->quote($crs_id, "integer");
        $this->db->manipulate($query);
    }

    /**
     * @return int[]
     */
    public function getAffectedCrsObjIds(int $id) : array
    {
        $query = "SELECT crs_id" . PHP_EOL
                . " FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE venue_id = " . $this->db->quote($id, "integer");

        $res = $this->db->query($query);
        $ret = array();
        while ($row = $this->db->fetchAssoc($res)) {
            $ret[] = (int) $row["crs_id"];
        }

        return $ret;
    }

    public function isAssigned(int $id) : bool
    {
        $crs_ids = $this->getAffectedCrsObjIds($id);
        return count($crs_ids) > 0;
    }

    public function install()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $fields = array(
                "crs_id" => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                "venue_id" => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => false
                ),
                "venue_text" => array(
                    'type' => 'text',
                    'length' => 2048,
                    'notnull' => false
                )
            );
            $this->db->createTable(self::TABLE_NAME, $fields);
            $this->db->addPrimaryKey(self::TABLE_NAME, array("crs_id"));
        }
    }

    public function update1()
    {
        if (!$this->db->tableColumnExists(self::TABLE_NAME, "venue_additional")) {
            $fields = array(
                        'type' => 'clob',
                        'notnull' => false
                    );
            $this->db->addTableColumn(self::TABLE_NAME, "venue_additional", $fields);
        }
    }
}
