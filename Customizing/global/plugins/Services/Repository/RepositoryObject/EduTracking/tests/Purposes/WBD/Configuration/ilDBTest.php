<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\EduTracking\Purposes\WBD\Configuration;

use PHPUnit\Framework\TestCase;
use CaT\Plugins\EduTracking\Mocks;

class ilDBTest extends TestCase
{
    const TABLE_NAME = "xetr_wbd_config";

    /**
     * @var Mocks
     */
    protected $mocks;

    public function setUp() : void
    {
        $this->mocks = new Mocks();
    }

    public function testCreate() : void
    {
        $db = new ilDB($this->mocks->getIliasDBMock());
        $this->assertInstanceOf(ilDB::class, $db);
    }

    public function testSelectWithoutRow() : void
    {
        $sql =
            'SELECT id, available, contact, user_id' . PHP_EOL
            . 'FROM ' . self::TABLE_NAME . PHP_EOL
            . 'ORDER BY id DESC' . PHP_EOL
            . 'LIMIT 1' . PHP_EOL
        ;

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('query')
            ->with($sql)
            ->willReturn([])
        ;
        $db_mock
            ->expects($this->once())
            ->method('numRows')
            ->with([])
            ->willReturn(0)
        ;

        $db = new ilDB($db_mock);

        $result = $db->select();

        $this->assertNull($result);
    }

    public function testSelectWithRow() : void
    {
        $sql =
            'SELECT id, available, contact, user_id' . PHP_EOL
            . 'FROM ' . self::TABLE_NAME . PHP_EOL
            . 'ORDER BY id DESC' . PHP_EOL
            . 'LIMIT 1' . PHP_EOL
        ;

        $row = [
            'id' => 22,
            'available' => 1,
            'contact' => 'test_contact',
            'user_id' => 10
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('query')
            ->with($sql)
            ->willReturn([$row])
        ;
        $db_mock
            ->expects($this->once())
            ->method('numRows')
            ->with([$row])
            ->willReturn(1)
        ;
        $db_mock
            ->expects($this->once())
            ->method('fetchAssoc')
            ->with([$row])
            ->willReturn($row)
        ;

        $db = new ilDB($db_mock);

        $result = $db->select();

        $this->assertEquals(22, $result->getId());
        $this->assertTrue($result->getAvailable());
        $this->assertEquals('test_contact', $result->getContact());
        $this->assertEquals(10, $result->getUserId());
    }

    public function testInsert() : void
    {
        $values = [
            'id' => ['integer', 22],
            'available' => ['integer', true],
            'contact' => ['text', 'test_contact'],
            'user_id' => ['integer', 10],
            'changed_by' => ['text', 'root'],
            'changed_at' => ['text', date("Y-m-d H:i:s")]
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('nextId')
            ->with(self::TABLE_NAME)
            ->willReturn(22)
        ;
        $db_mock
            ->expects($this->once())
            ->method('insert')
            ->with(self::TABLE_NAME, $values)
        ;

        $db = new ilDB($db_mock);

        try {
            $db->insert(true, 'test_contact', 10, 'root');
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }

    public function testCreateTable() : void
    {
        $fields = [
            'id' => [
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
            ],
            'available' => [
                'type' => 'integer',
                'length' => 1,
                'notnull' => true
            ],
            'contact' => [
                'type' => 'text',
                'length' => 50,
                'notnull' => true
            ],
            'user_id' => [
                'type' => 'integer',
                'length' => 4,
                'notnull' => false
            ],
            'changed_by' => [
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
            ],
            'changed_at' => [
                'type' => 'text',
                'length' => 25,
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

        $db = new ilDB($db_mock);

        try {
            $db->createTable();
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }
}
