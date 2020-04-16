<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types = 1);

namespace CaT\Plugins\WBDCommunicator\Config\UDF;

class ilDB implements DB
{
    const TABLE_NAME = "wbd_udf_config";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var \ilObjUser
     */
    protected $usr;

    public function __construct(\ilDBInterface $db, \ilObjUser $usr)
    {
        $this->db = $db;
        $this->usr = $usr;
    }

    /**
     * @inheritDoc
     */
    public function getUDFFieldIdForWBDID()
    {
        return $this->getUDFDefinitionFor(self::KEY_GUTBERATEN_ID);
    }

    /**
     * @inheritDoc
     */
    public function saveUDFFieldIdForWBDID(int $field_id)
    {
        $def = new UDFDefinition(
            self::KEY_GUTBERATEN_ID,
            $field_id
        );

        $this->saveUDFDefinition($def);
    }

    /**
     * @inheritDoc
     */
    public function getUDFFieldIdForStatus()
    {
        return $this->getUDFDefinitionFor(self::KEY_ANNOUNCE_ID);
    }

    /**
     * @inheritDoc
     */
    public function saveUDFFieldIdForStatus(int $field_id)
    {
        $def = new UDFDefinition(
            self::KEY_ANNOUNCE_ID,
            $field_id
        );

        $this->saveUDFDefinition($def);
    }

    /**
     * @inheritDoc
     */
    public function getUDFDefinitions() : array
    {
        $table = self::TABLE_NAME;

        $query = <<<SQL
SELECT field, field_id
FROM $table
SQL;
        $ret = [];
        $res = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($res)) {
            $ret[] = new UDFDefinition(
                $row["field"],
                (int) $row["field_id"]
            );
        }

        return $ret;
    }

    protected function saveUDFDefinition(UDFDefinition $definition)
    {
        $this->db->replace(
            self::TABLE_NAME,
            [
                "field" => ["text", $definition->getField()]
            ],
            [
                "field_id" => ["integer", $definition->getFieldId()],
                "changed_by" => ["integer", $this->usr->getId()],
                "changed_at" => ["text", date("Y-m-d H:i:s")]
            ]
        );
    }

    protected function getUDFDefinitionFor(string $field)
    {
        $table = self::TABLE_NAME;
        $field = $this->db->quote($field, "text");

        $query = <<<SQL
SELECT field, field_id
FROM $table
WHERE field = $field
SQL;

        $res = $this->db->query($query);
        if ($this->db->numRows($res) == 0) {
            return null;
        }

        $row = $this->db->fetchAssoc($res);
        return new UDFDefinition(
            $row["field"],
            (int) $row["field_id"]
        );
    }

    public function createTable()
    {
        if (!$this->db->tableExists("wbd_udf_config")) {
            $fields =
                array(
                    "field" => array(
                        "type" => "text",
                        "length" => 50,
                        "notnull" => true
                    ),
                    "field_id" => array(
                        "type" => "integer",
                        "length" => 4,
                        "notnull" => true
                    ),
                    "changed_by" => array(
                        "type" => "integer",
                        "length" => 4,
                        "notnull" => true
                    ),
                    "changed_at" => array(
                        "type" => "text",
                        "length" => 21,
                        "notnull" => true
                    )
                );

            $this->db->createTable("wbd_udf_config", $fields);
        }
    }

    public function createPrimaryKey()
    {
        try {
            $this->db->addPrimaryKey("wbd_udf_config", array("field"));
        } catch (\PDOException $e) {
            $this->db->dropPrimaryKey("wbd_udf_config");
            $this->db->addPrimaryKey("wbd_udf_config", array("field"));
        }
    }
}
