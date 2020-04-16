<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingProvider\Tags;

use PHPUnit\Framework\TestCase;

class TagTest extends TestCase
{
    /**
     * @var Tag
     */
    protected $obj;

    public function setUp() : void
    {
        $this->obj = new Tag(22, 'test_name', 'test_color');
    }

    public function testCreate() : void
    {
        $this->assertInstanceOf(Tag::class, $this->obj);
        $this->assertEquals(22, $this->obj->getId());
        $this->assertEquals('test_name', $this->obj->getName());
        $this->assertEquals('test_color', $this->obj->getColorCode());
    }

    public function testWithName() : void
    {
        $new_obj = $this->obj->withName('test_new_name');

        $this->assertEquals(22, $this->obj->getId());
        $this->assertEquals('test_name', $this->obj->getName());
        $this->assertEquals('test_color', $this->obj->getColorCode());

        $this->assertEquals(22, $new_obj->getId());
        $this->assertEquals('test_new_name', $new_obj->getName());
        $this->assertEquals('test_color', $new_obj->getColorCode());
    }

    public function testWithColorCode() : void
    {
        $new_obj = $this->obj->withColorCode('test_new_color');

        $this->assertEquals(22, $this->obj->getId());
        $this->assertEquals('test_name', $this->obj->getName());
        $this->assertEquals('test_color', $this->obj->getColorCode());

        $this->assertEquals(22, $new_obj->getId());
        $this->assertEquals('test_name', $new_obj->getName());
        $this->assertEquals('test_new_color', $new_obj->getColorCode());
    }
}
