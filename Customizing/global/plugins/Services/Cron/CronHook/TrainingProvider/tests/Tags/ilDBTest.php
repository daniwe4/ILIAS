<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingProvider\Tags;

use PHPUnit\Framework\TestCase;
use CaT\Plugins\TrainingProvider\Mocks;

class ilDBTest extends TestCase
{
    const TABLE_NAME = 'tp_tags';
    const TABLE_ALLOCATION = 'tp_tags_provider';

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
        $db = new ilDB($this->mocks->getIliasDBMock());
        $this->assertInstanceOf(ilDB::class, $db);
    }

    public function testCreate() : void
    {
        $values = [
            'id' => ['integer', 22],
            'name' => ['text', 'test_name'],
            'color' => ['text', 'test_color']
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('nextId')
            ->willReturn(22)
        ;
        $db_mock
            ->expects($this->once())
            ->method('insert')
            ->with(self::TABLE_NAME, $values)
        ;

        $db = new ilDB($db_mock);

        $result = $db->create('test_name', 'test_color');

        $this->assertInstanceOf(Tag::class, $result);
        $this->assertEquals(22, $result->getId());
        $this->assertEquals('test_name', $result->getName());
        $this->assertEquals('test_color', $result->getColorCode());
    }

    public function testSelectWithEmptyResult() : void
    {
        $sql =
            'SELECT name, color' . PHP_EOL
            . 'FROM ' . self::TABLE_NAME . PHP_EOL
            . 'WHERE id = 22' . PHP_EOL
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

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No tag found for id: 22');
        $db->select(22);
    }

    public function testSelect() : void
    {
        $sql =
             'SELECT name, color' . PHP_EOL
            . 'FROM ' . self::TABLE_NAME . PHP_EOL
            . 'WHERE id = 22' . PHP_EOL
        ;

        $row = [
            'name' => 'test_name',
            'color' => 'test_color'
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

        $result = $db->select(22);

        $this->assertInstanceOf(Tag::class, $result);
        $this->assertEquals(22, $result->getId());
        $this->assertEquals('test_name', $result->getName());
        $this->assertEquals('test_color', $result->getColorCode());
    }

    public function testUpdate() : void
    {
        $tag = new Tag(22, 'test_name', 'test_color');

        $where = ['id' => ['integer', 22]];

        $values = [
            'name' => ['text', 'test_name'],
            'color' => ['text', 'test_color']
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('update')
            ->with(self::TABLE_NAME, $values, $where)
        ;

        $db = new ilDB($db_mock);

        try {
            $db->update($tag);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }

    public function testDelete() : void
    {
        $sql1 =
             "DELETE FROM " . self::TABLE_ALLOCATION . PHP_EOL
            . "WHERE id = 22" . PHP_EOL
        ;

        $sql2 =
             "DELETE FROM " . self::TABLE_NAME . PHP_EOL
            . "WHERE id = 22" . PHP_EOL
        ;

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->exactly(2))
            ->method('quote')
            ->with(22, 'integer')
            ->willReturn(22)
        ;
        $db_mock
            ->expects($this->exactly(2))
            ->method('manipulate')
            ->withConsecutive([$sql1], [$sql2])
        ;

        $db = new ilDB($db_mock);

        try {
            $db->delete(22);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }

    public function testGetTagsForWithEmptyResult() : void
    {
        $sql =
             "SELECT id, name, color" . PHP_EOL
            . "FROM " . self::TABLE_NAME . " tags" . PHP_EOL
            . "JOIN " . self::TABLE_ALLOCATION . " allo" . PHP_EOL
            . "    ON tags.id = allo.id" . PHP_EOL
            . "WHERE allo.provider_id = 22" . PHP_EOL
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

        $result = $db->getTagsFor(22);

        $this->assertIsArray($result);
        $this->assertTrue(count($result) === 0);
    }

    public function testGetTagsFor() : void
    {
        $sql =
            "SELECT id, name, color" . PHP_EOL
            . "FROM " . self::TABLE_NAME . " tags" . PHP_EOL
            . "JOIN " . self::TABLE_ALLOCATION . " allo" . PHP_EOL
            . "    ON tags.id = allo.id" . PHP_EOL
            . "WHERE allo.provider_id = 22" . PHP_EOL
        ;

        $row = [
            'id' => 33,
            'name' => 'test_name',
            'color' => 'test_color'
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
            ->willReturn([$row])
        ;
        $db_mock
            ->expects($this->once())
            ->method('numRows')
            ->with([$row])
            ->willReturn(1)
        ;
        $db_mock
            ->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->with([$row])
            ->willReturn($row, null)
        ;

        $db = new ilDB($db_mock);

        $result = $db->getTagsFor(22);

        $this->assertIsArray($result);
        $this->assertTrue(count($result) === 1);

        $result = $result[0];

        $this->assertEquals(33, $result->getId());
        $this->assertEquals('test_name', $result->getName());
        $this->assertEquals('test_color', $result->getColorCode());
    }

    public function testGetTagsRaw() : void
    {
        $sql =
            "SELECT tag.id, tag.name, tag.color, COUNT(alloc.id) AS allocs" . PHP_EOL
            . "FROM " . self::TABLE_NAME . " tag" . PHP_EOL
            . "LEFT JOIN " . self::TABLE_ALLOCATION . " alloc" . PHP_EOL
            . "    ON tag.id = alloc.id" . PHP_EOL
            . "GROUP BY tag.id" . PHP_EOL
        ;

        $row = [
            'id' => 33,
            'name' => 'test_name',
            'color' => 'test_color',
            'allocs' => 45
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('query')
            ->with($sql)
            ->willReturn([$row])
        ;
        $db_mock
            ->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->with([$row])
            ->willReturn($row, null)
        ;

        $db = new ilDB($db_mock);

        $result = $db->getTagsRaw();

        $this->assertIsArray($result);
        $this->assertTrue(count($result) === 1);

        $result = $result[0];

        $this->assertEquals(33, $result['id']);
        $this->assertEquals('test_name', $result['name']);
        $this->assertEquals('test_color', $result['color']);
        $this->assertEquals(45, $result['allocs']);
    }

    public function testGetAssignedTagsRaw() : void
    {
        $sql =
            "SELECT tag.id, tag.name" . PHP_EOL
            . "FROM " . self::TABLE_NAME . " tag" . PHP_EOL
            . "JOIN " . self::TABLE_ALLOCATION . " alloc" . PHP_EOL
            . "    ON tag.id = alloc.id" . PHP_EOL
            . "GROUP BY tag.id" . PHP_EOL
        ;

        $row = [
            'id' => 33,
            'name' => 'test_name'
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('query')
            ->with($sql)
            ->willReturn([$row])
        ;
        $db_mock
            ->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->with([$row])
            ->willReturn($row, null)
        ;

        $db = new ilDB($db_mock);

        $result = $db->getAssignedTagsRaw();

        $this->assertIsArray($result);
        $this->assertTrue(count($result) === 1);

        $result = $result[0];

        $this->assertEquals(33, $result['id']);
        $this->assertEquals('test_name', $result['name']);
    }

    public function testAllocateTags() : void
    {
        $values = [
            "id" => ["integer", 22],
            "provider_id" => ["integer", 33]
        ];

        $tags = [
            'id' => 22
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('insert')
            ->with(self::TABLE_ALLOCATION, $values)
        ;

        $db = new ilDB($db_mock);

        try {
            $db->allocateTags(33, $tags);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }
}
