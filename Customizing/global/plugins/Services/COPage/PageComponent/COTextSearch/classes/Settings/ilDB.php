<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\COTextSearch\Settings;

class ilDB implements DB
{
	const TABLE_NAME = "cots_objects";

	/**
	 * @var \ilDBInterface
	 */
	protected $db;

	public function __construct(\ilDBInterface $db)
	{
		$this->db = $db;
	}

	public function create(int $parent_id) : Settings
	{
		$id = $this->getNextId();
		$settings = new Settings(
			$id,
			$parent_id
		);

		$values = [
			"id" => ["integer", $settings->getId()],
			"parent_id" => ["integer", $settings->getParentId()]
		];

		$this->db->insert(self::TABLE_NAME, $values);

		return $settings;
	}

	public function deleteFor(int $id)
	{
		$query = "DELETE FROM ".self::TABLE_NAME.PHP_EOL
			."WHERE id = ".$this->db->quote($id, "integer");

		$this->db->manipulate($query);
	}

	protected function getNextId() : int
	{
		return (int)$this->db->nextId(self::TABLE_NAME);
	}
}