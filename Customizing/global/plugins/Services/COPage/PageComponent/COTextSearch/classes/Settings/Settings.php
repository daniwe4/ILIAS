<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\COTextSearch\Settings;

class Settings
{
	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var int
	 */
	protected $parent_id;

	public function __construct(int $id, int $parent_id)
	{
		$this->id = $id;
		$this->parent_id = $parent_id;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getParentId(): int
	{
		return $this->parent_id;
	}
}