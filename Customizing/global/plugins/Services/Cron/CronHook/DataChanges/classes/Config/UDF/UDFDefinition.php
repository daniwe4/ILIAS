<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types = 1);

namespace CaT\Plugins\DataChanges\Config\UDF;

class UDFDefinition
{
	/**
	 * @var string
	 */
	protected $field;

	/**
	 * @var int
	 */
	protected $field_id;

	public function __construct(string $field, int $field_id)
	{
		$this->field = $field;
		$this->field_id = $field_id;
	}

	public function getField(): string
	{
		return $this->field;
	}

	public function getFieldId(): int
	{
		return $this->field_id;
	}
}