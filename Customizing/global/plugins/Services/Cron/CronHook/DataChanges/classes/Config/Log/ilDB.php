<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\DataChanges\Config\Log;

use DateTime;
use ilDBInterface;

class ilDB implements DB
{
	const TABLE_NAME = 'datachanges_log';

	/**
	 * @var ilDBInterface
	 */
	protected $db;

	public function __construct(ilDBInterface $db)
	{
		$this->db = $db;
	}

	public function create(
		string $action,
		int $target_id,
		int $editor_id,
		string $reason
	) : LogEntry {
		$next_id = (int)$this->db->nextId(self::TABLE_NAME);
		$date_time = new DateTime();

		$log_entry = new LogEntry(
			$next_id,
			$action,
			$target_id,
			$editor_id,
			$reason,
			$date_time
		);

		$values = [
			'log_id' => ['integer', $log_entry->getLogId()],
			'action' => ['text', $log_entry->getAction()],
			'target_id' => ['integer', $log_entry->getTargetId()],
			'editor_id' => ['integer', $log_entry->getEditorId()],
			'reason' => ['text', $log_entry->getReason()],
			'change_date_time' => ['text', $log_entry->getChangeDateTime()->format('Y-m-d H:i:s')]
		];

		$this->db->insert(self::TABLE_NAME, $values);

		return $log_entry;
	}

	public function selectAll(string $order_column, string $direction) : array
	{
		$sql =
			 "SELECT log_id, action, target_id, editor_id, reason, change_date_time" . PHP_EOL
			."FROM " . self::TABLE_NAME . PHP_EOL
			."ORDER BY " . $order_column . " " . $direction .  PHP_EOL
		;

		$result = $this->db->query($sql);

		$log_entries = [];
		while ($row = $this->db->fetchAssoc($result)) {
			$log_entries[] = $this->createLogEntry($row);
		}

		return $log_entries;
	}

	public function createLogEntry(array $row) : LogEntry
	{
		$date_time = new DateTime($row['change_date_time']);

		return new LogEntry(
			(int)$row['log_id'],
			(string)$row['action'],
			(int)$row['target_id'],
			(int)$row['editor_id'],
			(string)$row['reason'],
			$date_time
		);
	}

	public function createTable()
	{
		if (! $this->db->tableExists(self::TABLE_NAME)) {
			$fields = [
				'log_id' => [
					'type' => 'integer',
					'length' => 4,
					'notnull' => true
				],
				'action' => [
					'type' => 'text',
					'length' => 128,
					'notnull' => false
				],
				'target_id' => [
					'type' => 'integer',
					'length' => 4,
					'notnull' => true
				],
				'editor_id' => [
					'type' => 'integer',
					'length' => 4,
					'notnull' => true
				],
				'reason' => [
					'type' => 'text',
					'length' => 128,
					'notnull' => false
				],
				'change_date_time' => [
					'type' => 'text',
					'length' => 20,
					'notnull' => true
				]
			];
		}

		$this->db->createTable(self::TABLE_NAME, $fields);
	}

	public function createPrimaryKey()
	{
		try {
			$this->db->addPrimaryKey(self::TABLE_NAME, ['log_id']);
		} catch (\PDOException $e) {
			$this->db->dropPrimaryKey(self::TABLE_NAME);
			$this->db->addPrimaryKey(self::TABLE_NAME, ['log_id']);
		}
	}

	public function createSequence()
	{
		if (! $this->db->sequenceExists(self::TABLE_NAME)) {
			$this->db->createSequence(self::TABLE_NAME);
		}
	}
}