<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingProvider\ProviderAssignment;

use PHPUnit\Framework\TestCase;

class ListAssignmentTest extends TestCase
{
    public function testCreate() : ListAssignment
    {
        $obj = new ListAssignment(22, 33);

        $this->assertInstanceOf(ProviderAssignment::class, $obj);
        $this->assertEquals(22, $obj->getCrsId());
        $this->assertEquals(33, $obj->getProviderId());

        return $obj;
    }

    /**
     * @depends testCreate
     */
    public function testIsListAssignment(ListAssignment $obj) : void
    {
        $this->assertTrue($obj->isListAssignment());
    }

    /**
     * @depends testCreate
     */
    public function testIsCustomAssignment(ListAssignment $obj) : void
    {
        $this->assertFalse($obj->isCustomAssignment());
    }

    /**
     * @depends testCreate
     */
    public function testGetProviderText(ListAssignment $obj) : void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('This is a ListAssignment. No provider-text in here.');
        $obj->getProviderText();
    }

    /**
     * @depends testCreate
     */
    public function testWithProviderText(ListAssignment $obj) : void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('This is a ListAssignment. No provider-text in here.');
        $obj->withProviderText('test_text');
    }

    /**
     * @depends testCreate
     */
    public function testWithProviderId(ListAssignment $obj) : void
    {
        $new_obj = $obj->withProviderId(44);

        $this->assertEquals(22, $obj->getCrsId());
        $this->assertEquals(33, $obj->getProviderId());

        $this->assertEquals(22, $new_obj->getCrsId());
        $this->assertEquals(44, $new_obj->getProviderId());
    }
}
