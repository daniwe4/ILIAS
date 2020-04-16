<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\EduTracking\Purposes\WBD;

use PHPUnit\Framework\TestCase;
use CaT\Plugins\EduTracking\Mocks;

class ilDBTest extends TestCase
{
    const TABLE_NAME = "xetr_wbd_data";

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
        $values = [
            'obj_id' => ['integer', 22]
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('insert')
            ->with(self::TABLE_NAME, $values)
        ;

        $db = new ilDB($db_mock, $this->mocks->getIliasAppEventHandler());
        $result = $db->create($this->mocks->getEduTrackingObjectMock());

        $this->assertInstanceOf(WBD::class, $result);
        $this->assertEquals(22, $result->getObjId());
        $this->assertInstanceOf(\ilObjEduTracking::class, $result->getObject());
        $this->assertNull($result->getEducationType());
        $this->assertNull($result->getEducationContent());
    }

    public function testSelectFor() : void
    {
        $sql =
             'SELECT obj_id, education_type, education_content' . PHP_EOL
            . 'FROM ' . self::TABLE_NAME . PHP_EOL
            . 'WHERE obj_id = 22' . PHP_EOL
        ;

        $row1 = [
            'obj_id' => 22,
            'education_type' => 'test_type',
            'education_content' => 'test_content'
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

        $this->assertInstanceOf(WBD::class, $result);
        $this->assertEquals(22, $result->getObjId());
        $this->assertInstanceOf(\ilObjEduTracking::class, $result->getObject());
        $this->assertEquals('test_type', $result->getEducationType());
        $this->assertEquals('test_content', $result->getEducationContent());
    }

    public function testUpdate() : void
    {
        $values = [
            'education_type' => ['text', 'test_type'],
            'education_content' => ['text', 'test_content']
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

        $idd = new WBD(
            $db,
            $this->mocks->getIliasAppEventHandler(),
            $this->mocks->getEduTrackingObjectMock(),
            'test_type',
            'test_content'
        );

        try {
            $db->update($idd);
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
            ],
            'education_type' => [
                'type' => 'text',
                'length' => 10,
                'notnull' => false
            ],
            'education_content' => [
                'type' => 'text',
                'length' => 50,
                'notnull' => false
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
