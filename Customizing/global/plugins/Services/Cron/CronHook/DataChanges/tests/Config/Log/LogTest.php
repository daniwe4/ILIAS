<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\DataChanges\Config\Log;

use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
	const TEST_STRING = "test_string";
	const TEST_INT = 11;

	/**
	 * @var int
	 */
	protected $log_entry_id;

	/**
	 * @var string
	 */
	protected $action;

	/**
	 * @var int
	 */
	protected $target_id;

	/**
	 * @var int
	 */
	protected $editor_id;

	/**
	 * @var string
	 */
	protected $reason;

	/**
	 * @var \DateTime|false
	 */
	protected $date_time;

	/**
	 * @var LogEntry
	 */
	protected $log_entry;

	public function setUp()
	{
		$this->log_entry_id = 1;
		$this->action = "action";
		$this->target_id = 33;
		$this->editor_id = 44;
		$this->reason = 'reason';
		$this->date_time = \DateTime::createFromFormat('Y-m-d H:i:s', '2009-02-15 15:16:17');

		$this->log_entry = new LogEntry(
			$this->log_entry_id,
			$this->action,
			$this->target_id,
			$this->editor_id,
			$this->reason,
			$this->date_time
		);
	}

	public function testCreate()
	{
		$this->assertEquals($this->log_entry_id, $this->log_entry->getLogId());
		$this->assertEquals($this->action, $this->log_entry->getAction());
		$this->assertEquals($this->target_id, $this->log_entry->getTargetId());
		$this->assertEquals($this->editor_id, $this->log_entry->getEditorId());
		$this->assertEquals($this->reason, $this->log_entry->getReason());
		$this->assertEquals($this->date_time, $this->log_entry->getChangeDateTime());
	}
}