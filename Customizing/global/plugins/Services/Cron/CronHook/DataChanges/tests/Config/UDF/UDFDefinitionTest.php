<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\DataChanges\Config\UDF;

use PHPUnit\Framework\TestCase;

class UDFDefinitionTest extends TestCase
{
	/**
	 * @var string
	 */
	protected $field;

	/**
	 * @var int
	 */
	protected $field_id;

	/**
	 * @var UDFDefinition
	 */
	protected $obj;

	public function setUp()
	{
		$this->field = 'field';
		$this->field_id = 22;

		$this->obj = new UDFDefinition(
			$this->field,
			$this->field_id
		);
	}

	public function testCreate()
	{
		$this->assertEquals($this->field, $this->obj->getField());
		$this->assertEquals($this->field_id, $this->obj->getFieldId());
	}
}