<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\DataChanges\Config\Log;

use DateTime;

class LogEntry
{
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
	 * @var DateTime
	 */
	protected $change_date_time;

	public function __construct(
		int $log_id,
		string $action,
		int $target_id,
		int $editor_id,
		string $reason,
		DateTime $change_date_time
	) {
		$this->log_id = $log_id;
		$this->action = $action;
		$this->target_id = $target_id;
		$this->editor_id = $editor_id;
		$this->reason = $reason;
		$this->change_date_time = $change_date_time;
	}

	public function getLogId() : int
	{
		return $this->log_id;
	}

	public function getAction() : string
	{
		return $this->action;
	}

	public function getTargetId() : int
	{
		return $this->target_id;
	}

	public function getEditorId() : int
	{
		return $this->editor_id;
	}

	public function getReason() : string
	{
		return $this->reason;
	}

	public function getChangeDateTime() : DateTime
	{
		return $this->change_date_time;
	}
}
