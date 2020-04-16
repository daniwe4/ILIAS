<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\EduTracking\Purposes\IDD;

use PHPUnit\Framework\TestCase;
use CaT\Plugins\EduTracking\Mocks;

class IDDTest extends TestCase
{
    /**
     * @var Mocks
     */
    protected $mocks;

    /**
     * @var IDD
     */
    protected $obj;

    public function setUp() : void
    {
        $this->mocks = new Mocks();
        $db_mock = new ilDB($this->mocks->getIliasDBMock(), $this->mocks->getIliasAppEventHandler());
        $this->obj = new IDD(
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
        $this->assertEquals(0, $this->obj->getMinutes());
    }

    public function testWithMinutes() : void
    {
        $new_obj = $this->obj->withMinutes(10);

        $this->assertEquals(22, $this->obj->getObjId());
        $this->assertEquals(0, $this->obj->getMinutes());

        $this->assertEquals(22, $new_obj->getObjId());
        $this->assertEquals(10, $new_obj->getMinutes());
    }

    public function testUpdate() : void
    {
        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('update')
        ;

        $db = new ilDB($db_mock, $this->mocks->getIliasAppEventHandler());

        $payload = [
            'xetr_obj_id' => 22,
            'minutes' => 10
        ];

        $evt_handler = $this->mocks->getIliasAppEventHandler();
        $evt_handler
            ->expects($this->once())
            ->method('raise')
            ->with(
                'Plugin/EduTracking',
                'updateIDD',
                $payload
            )
        ;

        $obj = new IDD(
            $db,
            $evt_handler,
            $this->mocks->getEduTrackingObjectMock(),
            10
        );

        try {
            $obj->update();
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }
}
