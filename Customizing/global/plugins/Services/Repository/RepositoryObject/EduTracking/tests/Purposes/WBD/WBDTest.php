<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\EduTracking\Purposes\WBD;

use PHPUnit\Framework\TestCase;
use CaT\Plugins\EduTracking\Mocks;

class WBDTest extends TestCase
{
    /**
     * @var Mocks
     */
    protected $mocks;

    /**
     * @var WBD
     */
    protected $obj;

    public function setUp() : void
    {
        $this->mocks = new Mocks();
        $db_mock = new ilDB($this->mocks->getIliasDBMock(), $this->mocks->getIliasAppEventHandler());
        $this->obj = new WBD(
            $db_mock,
            $this->mocks->getIliasAppEventHandler(),
            $this->mocks->getEduTrackingObjectMock()
        );
    }

    public function testGetObject() : void
    {
        $this->assertInstanceOf(\ilObjEduTracking::class, $this->obj->getObject());
    }

    public function testCreate() : void
    {
        $this->assertEquals(22, $this->obj->getObjId());
        $this->assertNull($this->obj->getEducationType());
        $this->assertNull($this->obj->getEducationContent());
    }

    public function testWithEducationTypeWithNull() : void
    {
        $new_obj = $this->obj->withEducationType(null);

        $this->assertEquals(22, $this->obj->getObjId());
        $this->assertNull($this->obj->getEducationType());
        $this->assertNull($this->obj->getEducationContent());

        $this->assertEquals(22, $new_obj->getObjId());
        $this->assertNull($new_obj->getEducationType());
        $this->assertNull($new_obj->getEducationContent());
    }

    public function testWithEducationTypeWithValue() : void
    {
        $new_obj = $this->obj->withEducationType('test_type');

        $this->assertEquals(22, $this->obj->getObjId());
        $this->assertNull($this->obj->getEducationType());
        $this->assertNull($this->obj->getEducationContent());

        $this->assertEquals(22, $new_obj->getObjId());
        $this->assertEquals('test_type', $new_obj->getEducationType());
        $this->assertNull($new_obj->getEducationContent());
    }

    public function testWithEducationContentWithNull() : void
    {
        $new_obj = $this->obj->withEducationContent(null);

        $this->assertEquals(22, $this->obj->getObjId());
        $this->assertNull($this->obj->getEducationType());
        $this->assertNull($this->obj->getEducationContent());

        $this->assertEquals(22, $new_obj->getObjId());
        $this->assertNull($new_obj->getEducationType());
        $this->assertNull($new_obj->getEducationContent());
    }

    public function testWithEducationContentWithValue() : void
    {
        $new_obj = $this->obj->withEducationContent('test_content');

        $this->assertEquals(22, $this->obj->getObjId());
        $this->assertNull($this->obj->getEducationType());
        $this->assertNull($this->obj->getEducationContent());

        $this->assertEquals(22, $new_obj->getObjId());
        $this->assertNull($new_obj->getEducationType());
        $this->assertEquals('test_content', $new_obj->getEducationContent());
    }
}
