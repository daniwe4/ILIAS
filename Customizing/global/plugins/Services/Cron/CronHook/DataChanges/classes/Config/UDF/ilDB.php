<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types = 1);

namespace CaT\Plugins\DataChanges\Config\UDF;

class ilDB implements DB
{
	const TABLE_NAME = "datachanges_udf_config";

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
	public function getUDFFieldIdForBWVID()
	{
		return $this->getUDFDefinitionFor(self::KEY_GUTBERATEN_ID);
	}

	/**
	 * @inheritDoc
	 */
	public function saveUDFFieldIdForBWVID(int $field_id)
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
	public function getUDFDefinitions() : array
	{
		$table = self::TABLE_NAME;

		$query = <<<SQL
SELECT field, field_id
FROM $table
SQL;
		$ret = [];
		$res = $this->db->query($query);
		while($row = $this->db->fetchAssoc($res)) {
			$ret[] = new UDFDefinition(
				$row["field"],
				(int)$row["field_id"]
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
		if($this->db->numRows($res) == 0) {
			return null;
		}

		$row = $this->db->fetchAssoc($res);
		return new UDFDefinition(
			$row["field"],
			(int)$row["field_id"]
		);
	}

	public function createTable()
	{
		if (! $this->db->tableExists(self::TABLE_NAME)) {
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

			$this->db->createTable(self::TABLE_NAME, $fields);
		}
	}

	public function createPrimaryKey()
	{
		try {
			$this->db->addPrimaryKey(self::TABLE_NAME, array("field"));
		} catch(\PDOException $e) {
			$this->db->dropPrimaryKey(self::TABLE_NAME);
			$this->db->addPrimaryKey(self::TABLE_NAME, array("field"));
		}
	}
}