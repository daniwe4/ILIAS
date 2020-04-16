<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\EduTracking\Purposes\GTI;

use PHPUnit\Framework\TestCase;
use CaT\Plugins\EduTracking\Mocks;

class ilDBTest extends TestCase
{
    const TABLE_NAME = "xetr_gti_data";

    /**
     * @var Mocks
     */
    protected $mocks;

    public function setUp() : void
    {
        $this->mocks = new Mocks();
    }

    public function testCreateInstance() : void
    {
        $db = new ilDB($this->mocks->getIliasDBMock(), $this->mocks->getIliasAppEventHandler());
        $this->assertInstanceOf(ilDB::class, $db);
    }

    public function testCreate() : void
    {
        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('insert')
            ->with(self::TABLE_NAME, ['obj_id' => ['integer', 22]])
        ;

        $db = new ilDB($db_mock, $this->mocks->getIliasAppEventHandler());
        $result = $db->create($this->mocks->getEduTrackingObjectMock());

        $this->assertInstanceOf(GTI::class, $result);
        $this->assertEquals(22, $result->getObjId());
        $this->assertInstanceOf(\ilObjEduTracking::class, $result->getObject());
        $this->assertNull($result->getCategoryId());
        $this->assertFalse($result->getSetTrainingTimeManually());
        $this->assertEquals(0, $result->getMinutes());
    }

    public function testSelectFor() : void
    {
        $sql =
             'SELECT obj_id, category_id, set_trainingtime_manually, minutes' . PHP_EOL
            . ' FROM ' . self::TABLE_NAME . PHP_EOL
            . ' WHERE obj_id = 22'
        ;

        $row1 = [
            'obj_id' => 22,
            'category_id' => 10,
            'set_trainingtime_manually' => 1,
            'minutes' => 20
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('quote')
            ->with(22, 'integer')
            ->willReturn(22)
        ;
        $db_mock
            ->expects($this->once())
            ->method('query')
            ->with($sql)
            ->willReturn([$row1])
        ;
        $db_mock
            ->expects($this->once())
            ->method('numRows')
            ->with([$row1])
            ->willReturn(1)
        ;
        $db_mock
            ->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->with([$row1])
            ->willReturnOnConsecutiveCalls($row1, null)
        ;

        $db = new ilDB($db_mock, $this->mocks->getIliasAppEventHandler());

        $result = $db->selectFor($this->mocks->getEduTrackingObjectMock());

        $this->assertInstanceOf(GTI::class, $result);
        $this->assertEquals(22, $result->getObjId());
        $this->assertInstanceOf(\ilObjEduTracking::class, $result->getObject());
        $this->assertEquals(10, $result->getCategoryId());
        $this->assertTrue($result->getSetTrainingTimeManually());
        $this->assertEquals(20, $result->getMinutes());
    }

    public function testUpdate() : void
    {
        $values = [
            'category_id' => ['integer', 10],
            'set_trainingtime_manually' => ['integer', 1],
            'minutes' => ['integer', 20]
        ];

        $where = [
            'obj_id' => ['integer', 22]
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('update')
            ->with(self::TABLE_NAME, $values, $where)
        ;

        $db = new ilDB($db_mock, $this->mocks->getIliasAppEventHandler());

        $gti = new GTI(
            $db,
            $this->mocks->getIliasAppEventHandler(),
            $this->mocks->getEduTrackingObjectMock(),
            10,
            true,
            20
        );

        try {
            $db->update($gti);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }

    public function testDeleteFor() : void
    {
        $sql =
             'DELETE FROM ' . self::TABLE_NAME . PHP_EOL
            . ' WHERE obj_id = 22'
        ;

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('quote')
            ->with(22, 'integer')
            ->willReturn(22)
        ;
        $db_mock
            ->expects($this->once())
            ->method('manipulate')
            ->with($sql)
        ;

        $db = new ilDB($db_mock, $this->mocks->getIliasAppEventHandler());

        try {
            $db->deleteFor($this->mocks->getEduTrackingObjectMock());
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }

    public function testCreateTable() : void
    {
        $fields = [
            'obj_id' => [
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
            ]
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('tableExists')
            ->with(self::TABLE_NAME)
            ->willReturn(false)
        ;
        $db_mock
            ->expects($this->once())
            ->method('createTable')
            ->with(self::TABLE_NAME, $fields)
        ;

        $db = new ilDB($db_mock, $this->mocks->getIliasAppEventHandler());

        try {
            $db->createTable();
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }
}
