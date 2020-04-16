<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace CaT\Plugins\TrainingProvider\ProviderAssignment;

/**
 * Implementation of ProviderAssingment database interface
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_NAME = "providers_assignment";

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
    public function createListProviderAssignment($crs_id, $provider_id)
    {
        assert('is_int($crs_id)');
        assert('is_int($provider_id)');
        $va = new ListAssignment($crs_id, $provider_id);
        $values = array(
            "crs_id" => array("integer", $va->getCrsId()),
            "provider_id" => array("integer", $va->getProviderId()),
            "provider_text" => array('text', null)
        );
        $this->getDB()->insert(self::TABLE_NAME, $values);
        return $va;
    }

    /**
     * @inheritdoc
     */
    public function createCustomProviderAssignment($crs_id, $text)
    {
        assert('is_int($crs_id)');
        assert('is_string($text)');
        $va = new CustomAssignment($crs_id, $text);
        $values = array(
            "crs_id" => array("integer", $va->getCrsId()),
            "provider_id" => array('integer', null),
            "provider_text" => array("text", $text)
        );
        $this->getDB()->insert(self::TABLE_NAME, $values);
        return $va;
    }

    /**
     * @inheritdoc
     */
    public function update(ProviderAssignment $provider_assignment)
    {
        $where = array(
            "crs_id" => array("integer", $provider_assignment->getCrsId())
        );
        if ($provider_assignment->isListAssignment()) {
            $values = array(
                "provider_id" => array("integer", $provider_assignment->getProviderId()),
                "provider_text" => array('text', null)
            );
        }
        if ($provider_assignment->isCustomAssignment()) {
            $values = array(
                "provider_id" => array('integer', null),
                "provider_text" => array("text", $provider_assignment->getProviderText()),
            );
        }
        $this->getDB()->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function select($crs_id)
    {
        assert('is_int($crs_id)');
        $query = "SELECT crs_id, provider_id, provider_text FROM " . self::TABLE_NAME
            . " WHERE crs_id=" . $this->getDB()->quote($crs_id, "integer");

        $res = $this->getDB()->query($query);

        if ($this->getDB()->numRows($res) == 0) {
            return false;
        }

        $row = $this->getDB()->fetchAssoc($res);
        $provider_text = $row["provider_text"];
        if (trim($provider_text) == "") {
            $provider_text = null;
        }

        $provider_id = $row["provider_id"];
        if (trim($provider_id) == "") {
            $provider_id = null;
        }

        if (!is_null($provider_id) && is_null($provider_text)) {
            return new ListAssignment(
                (int) $crs_id,
                (int) $provider_id
            );
        }

        if (is_null($venue_id) && !is_null($provider_text)) {
            return new CustomAssignment(
                (int) $crs_id,
                $provider_text
            );
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function delete($crs_id)
    {
        assert('is_int($crs_id)');
        $query = "DELETE FROM " . self::TABLE_NAME . "\n"
                . " WHERE crs_id = " . $this->getDB()->quote($crs_id, "integer");
        $this->getDB()->manipulate($query);
    }

    /**
     * Return all crs obj ids where venue is used
     *
     * @param int 	$id
     *
     * @return int[]
     */
    public function getAffectedCrsObjIds($id)
    {
        $query = "SELECT crs_id" . PHP_EOL
                . " FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE provider_id = " . $this->getDB()->quote($id, "integer");

        $res = $this->getDB()->query($query);
        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = (int) $row["crs_id"];
        }

        return $ret;
    }

    /**
     * Install tables for provider assignment
     *
     * @return null
     */
    public function install()
    {
        if (!$this->getDB()->tableExists(self::TABLE_NAME)) {
            $fields = array(
                "crs_id" => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                "provider_id" => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => false
                ),
                "provider_text" => array(
                    'type' => 'text',
                    'length' => 2048,
                    'notnull' => false
                )
            );
            $this->getDB()->createTable(self::TABLE_NAME, $fields);
            $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("crs_id"));
        }
    }

    /**
     * Get the DB handler
     *
     * @return \ilDB
     */
    protected function getDB()
    {
        if ($this->db === null) {
            throw new \Exception("no db handler");
        }
        return $this->db;
    }
}
