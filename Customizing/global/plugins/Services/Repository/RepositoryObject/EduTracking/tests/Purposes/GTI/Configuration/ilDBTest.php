<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\EduTracking\Purposes\GTI\Configuration;

use PHPUnit\Framework\TestCase;
use CaT\Plugins\EduTracking\Mocks;

class ilDBTest extends TestCase
{
    const TABLE_NAME = "xetr_gti_config";
    const TABLE_NAME_CATEGORIES = "xetr_gti_categories";

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
            'SELECT id, available' . PHP_EOL
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
             'SELECT id, available' . PHP_EOL
            . 'FROM ' . self::TABLE_NAME . PHP_EOL
            . 'ORDER BY id DESC' . PHP_EOL
            . 'LIMIT 1' . PHP_EOL
        ;

        $row = [
            'id' => 22,
            'available' => 1
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
    }

    public function testInsert() : void
    {
        $values = [
            'id' => ['integer', 22],
            'available' => ['integer', true],
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
            $db->insert(true, 'root');
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }

    public function testSelectCategories() : void
    {
        $sql =
             'SELECT id, name' . PHP_EOL
            . 'FROM ' . self::TABLE_NAME_CATEGORIES . PHP_EOL
        ;

        $row1 = [
            'id' => 22,
            'name' => 'test1'
        ];

        $row2 = [
            'id' => 33,
            'name' => 'test2'
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('query')
            ->with($sql)
            ->willReturn([$row1, $row2])
        ;
        $db_mock
            ->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->with([$row1, $row2])
            ->willReturnOnConsecutiveCalls($row1, $row2)
        ;

        $db = new ilDB($db_mock);

        $result = $db->selectCategories();

        foreach ($result as $item) {
            $this->assertInstanceOf(CategoryGTI::class, $item);
        }

        $this->assertEquals(22, $result[0]->getId());
        $this->assertEquals('test1', $result[0]->getName());
        $this->assertEquals(33, $result[1]->getId());
        $this->assertEquals('test2', $result[1]->getName());
    }

    public function testGetTitleById() : void
    {
        $sql =
             'SELECT name' . PHP_EOL
            . 'FROM ' . self::TABLE_NAME_CATEGORIES . PHP_EOL
            . 'WHERE id = 22' . PHP_EOL
        ;

        $row = [
            'name' => 'test1'
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
            ->method('quote')
            ->with(22)
            ->willReturn(22)
        ;
        $db_mock
            ->expects($this->once())
            ->method('fetchAssoc')
            ->with([$row])
            ->willReturn($row)
        ;

        $db = new ilDB($db_mock);

        $result = $db->getTitleById(22);

        $this->assertEquals('test1', $result);
    }

    public function testInsertCategoriesWithNegativeId() : void
    {
        $cat = new CategoryGTI(-1, 'test_with_negative_id');

        $values = [
            'id' => ['integer', 22],
            'name' => ['text', 'test_with_negative_id'],
            'changed_by' => ['integer', 6],
            'changed_at' => ['text', date('Y-m-d H:i:s')]
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('nextId')
            ->with(self::TABLE_NAME_CATEGORIES)
            ->willReturn(22)
        ;
        $db_mock
            ->expects($this->once())
            ->method('insert')
            ->with(self::TABLE_NAME_CATEGORIES, $values)
        ;

        $db = new ilDB($db_mock);

        try {
            $db->insertCategories([$cat], 6);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }

    public function testInsertCategoriesWithPositiveId() : void
    {
        $cat = new CategoryGTI(33, 'test_with_positive_id');

        $values = [
            'name' => ['text', 'test_with_positive_id'],
            'changed_by' => ['integer', 6],
            'changed_at' => ['text', date('Y-m-d H:i:s')]
        ];

        $where = [
            'id' => ['integer', 33]
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('update')
            ->with(self::TABLE_NAME_CATEGORIES, $values, $where)
        ;

        $db = new ilDB($db_mock);

        try {
            $db->insertCategories([$cat], 6);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }

    public function testDeleteCategories() : void
    {
        $sql =
             'DELETE FROM ' . self::TABLE_NAME_CATEGORIES . PHP_EOL
            . 'WHERE id in (22,33)' . PHP_EOL
        ;

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('query')
            ->with($sql)
        ;

        $db = new ilDB($db_mock);

        try {
            $db->deleteCategories([22, 33]);
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
