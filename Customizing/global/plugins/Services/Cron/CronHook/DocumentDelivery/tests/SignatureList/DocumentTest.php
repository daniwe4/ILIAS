<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types=1);

namespace CaT\Plugins\DocumentDelivery\SignatureList;

use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase
{
	public function test_create_object()
	{
		$obj = new Document(1,2,3, 'hash');
		$this->assertInstanceOf(Document::class, $obj);
	}

	public function test_object_getter()
	{
		$obj = new Document(1,2,3, 'hash');

		$this->assertEquals(1, $obj->getId());
		$this->assertEquals(2, $obj->getCrsId());
		$this->assertEquals(3, $obj->getTemplateId());
		$this->assertEquals('hash', $obj->getHash());
	}
}