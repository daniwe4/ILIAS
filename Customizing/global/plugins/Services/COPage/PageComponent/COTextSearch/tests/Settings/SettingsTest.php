<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\COTextSearch\Settings;

use PHPUnit\Framework\TestCase;

class SettingsTest extends TestCase
{
	public function test_create_object()
	{
		$settings = new Settings(1,2);
		$this->assertInstanceOf(Settings::class, $settings);
	}

	public function test_class_properties()
	{
		$settings = new Settings(1,2);

		$this->assertEquals(1, $settings->getId());
		$this->assertEquals(2, $settings->getParentId());
	}
}