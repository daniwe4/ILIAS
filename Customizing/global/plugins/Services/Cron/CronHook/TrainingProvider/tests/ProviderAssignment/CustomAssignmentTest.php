<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingProvider\ProviderAssignment;

use PHPUnit\Framework\TestCase;

class CustomAssignmentTest extends TestCase
{
    public function testCreate() : CustomAssignment
    {
        $obj = new CustomAssignment(22, 'test_text');

        $this->assertInstanceOf(ProviderAssignment::class, $obj);
        $this->assertEquals(22, $obj->getCrsId());
        $this->assertEquals('test_text', $obj->getProviderText());

        return $obj;
    }

    /**
     * @depends testCreate
     */
    public function testIsListAssignment(CustomAssignment $obj) : void
    {
        $this->assertFalse($obj->isListAssignment());
    }

    /**
     * @depends testCreate
     */
    public function testIsCustomAssignment(CustomAssignment $obj) : void
    {
        $this->assertTrue($obj->isCustomAssignment());
    }

    /**
     * @depends testCreate
     */
    public function testGetProviderId(CustomAssignment $obj) : void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('This is a CustomAssignment. No provider-id in here.');
        $obj->getProviderId();
    }

    /**
     * @depends testCreate
     */
    public function testWithProviderId(CustomAssignment $obj) : void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('This is a CustomAssignment. No provider-id in here.');
        $obj->withProviderId(22);
    }

    /**
     * @depends testCreate
     */
    public function testWithProviderText(CustomAssignment $obj) : void
    {
        $new_obj = $obj->withProviderText('new_text');

        $this->assertEquals(22, $obj->getCrsId());
        $this->assertEquals('test_text', $obj->getProviderText());

        $this->assertEquals(22, $new_obj->getCrsId());
        $this->assertEquals('new_text', $new_obj->getProviderText());
    }
}
