<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\DataChanges\Config\Log;

interface DB
{
	public function create(
		string $action,
		int $target_id,
		int $editor_id,
		string $reason
	) : LogEntry;

	public function selectAll(string $order_column, string $direction) : array;
}