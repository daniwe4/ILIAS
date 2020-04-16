<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\EduTracking\Purposes\GTI;

use PHPUnit\Framework\TestCase;
use CaT\Plugins\EduTracking\Mocks;

class GTITest extends TestCase
{
    /**
     * @var Mocks
     */
    protected $mocks;

    /**
     * @var GTI
     */
    protected $obj;

    public function setUp() : void
    {
        $this->mocks = new Mocks();
        $db_mock = new ilDB($this->mocks->getIliasDBMock(), $this->mocks->getIliasAppEventHandler());
        $this->obj = new GTI(
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
        $this->assertNull($this->obj->getCategoryId());
        $this->assertFalse($this->obj->getSetTrainingTimeManually());
        $this->assertEquals(0, $this->obj->getMinutes());
    }

    public function testWithCategoryId() : void
    {
        $new_obj = $this->obj->withCategoryId(99);

        $this->assertEquals(22, $this->obj->getObjId());
        $this->assertNull($this->obj->getCategoryId());
        $this->assertFalse($this->obj->getSetTrainingTimeManually());
        $this->assertEquals(0, $this->obj->getMinutes());

        $this->assertEquals(22, $new_obj->getObjId());
        $this->assertEquals(99, $new_obj->getCategoryId());
        $this->assertFalse($new_obj->getSetTrainingTimeManually());
        $this->assertEquals(0, $new_obj->getMinutes());
    }

    public function testWithSetTrainingTimeManually() : void
    {
        $new_obj = $this->obj->withSetTrainingTimeManually(true);

        $this->assertEquals(22, $this->obj->getObjId());
        $this->assertNull($this->obj->getCategoryId());
        $this->assertFalse($this->obj->getSetTrainingTimeManually());
        $this->assertEquals(0, $this->obj->getMinutes());

        $this->assertEquals(22, $new_obj->getObjId());
        $this->assertNull($new_obj->getCategoryId());
        $this->assertTrue($new_obj->getSetTrainingTimeManually());
        $this->assertEquals(0, $new_obj->getMinutes());
    }

    public function testWithMinutes() : void
    {
        $new_obj = $this->obj->withMinutes(99);

        $this->assertEquals(22, $this->obj->getObjId());
        $this->assertNull($this->obj->getCategoryId());
        $this->assertFalse($this->obj->getSetTrainingTimeManually());
        $this->assertEquals(0, $this->obj->getMinutes());

        $this->assertEquals(22, $new_obj->getObjId());
        $this->assertNull($new_obj->getCategoryId());
        $this->assertFalse($new_obj->getSetTrainingTimeManually());
        $this->assertEquals(99, $new_obj->getMinutes());
    }

    public function testUpdateWithoutCategoryId() : void
    {
        $edu_obj = $this->mocks->getEduTrackingObjectMock();
        $edu_obj
            ->expects($this->once())
            ->method('getParentCourse')
            ->willReturn($this->mocks->getCrsMock())
        ;

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('update')
        ;

        $db = new ilDB($db_mock, $this->mocks->getIliasAppEventHandler());

        $payload = [
            "xetr_obj_id" => 22,
            "gti_learning_time" => 10,
            "crs_id" => 22,
            'gti_category' => ''
        ];

        $evt_handler = $this->mocks->getIliasAppEventHandler();
        $evt_handler
            ->expects($this->once())
            ->method('raise')
            ->with(
                'Plugin/EduTracking',
                'updateGTI',
                $payload
            )
        ;

        $obj = new GTI($db, $evt_handler, $edu_obj, 0, false, 10);

        try {
            $obj->update();
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }

    public function testUpdateWitCategoryId() : void
    {
        $config_actions = $this->createMock(Configuration\ilActions::class);
        $config_actions
            ->expects($this->once())
            ->method('getTitleById')
            ->with(33)
            ->willReturn('test_title')
        ;

        $gti_actions = $this->createMock(ilActions::class);
        $gti_actions
            ->expects($this->once())
            ->method('getConfigActions')
            ->willReturn($config_actions)
        ;

        $edu_obj = $this->mocks->getEduTrackingObjectMock();
        $edu_obj
            ->expects($this->once())
            ->method('getParentCourse')
            ->willReturn($this->mocks->getCrsMock())
        ;
        $edu_obj
            ->expects($this->once())
            ->method('getActionsFor')
            ->with('GTI')
            ->willReturn($gti_actions)
        ;

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('update')
        ;

        $db = new ilDB($db_mock, $this->mocks->getIliasAppEventHandler());

        $payload = [
            "xetr_obj_id" => 22,
            "gti_learning_time" => 10,
            "crs_id" => 22,
            'gti_category' => 'test_title'
        ];

        $evt_handler = $this->mocks->getIliasAppEventHandler();
        $evt_handler
            ->expects($this->once())
            ->method('raise')
            ->with(
                'Plugin/EduTracking',
                'updateGTI',
                $payload
            )
        ;

        $obj = new GTI($db, $evt_handler, $edu_obj, 33, false, 10);

        try {
            $obj->update();
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }
}
