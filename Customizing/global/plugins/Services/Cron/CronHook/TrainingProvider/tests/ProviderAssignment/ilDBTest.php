<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingProvider\ProviderAssignment;

use PHPUnit\Framework\TestCase;
use CaT\Plugins\TrainingProvider\Mocks;

class ilDBTest extends TestCase
{
    const TABLE_NAME = "providers_assignment";

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

    public function testCreateListProviderAssignment() : void
    {
        $list_assignment = new ListAssignment(22, 33);

        $values = [
            "crs_id" => ["integer", $list_assignment->getCrsId()],
            "provider_id" => ["integer", $list_assignment->getProviderId()],
            "provider_text" => ['text', null]
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('insert')
            ->with(self::TABLE_NAME, $values)
        ;

        $db = new ilDB($db_mock);

        $result = $db->createListProviderAssignment(22, 33);

        $this->assertInstanceOf(ListAssignment::class, $result);
        $this->assertEquals(22, $result->getCrsId());
        $this->assertEquals(33, $result->getProviderId());
    }

    public function testCreateCustomProviderAssignment() : void
    {
        $custom_assignment = new CustomAssignment(22, 'test_text');

        $values = [
            "crs_id" => ["integer", $custom_assignment->getCrsId()],
            "provider_id" => ['integer', null],
            "provider_text" => ["text", $custom_assignment->getProviderText()]
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('insert')
            ->with(self::TABLE_NAME, $values)
        ;

        $db = new ilDB($db_mock);

        $result = $db->createCustomProviderAssignment(22, 'test_text');

        $this->assertInstanceOf(CustomAssignment::class, $result);
        $this->assertEquals(22, $result->getCrsId());
        $this->assertEquals('test_text', $result->getProviderText());
    }

    public function testUpdateWithListAssignment() : void
    {
        $list_assignment = new ListAssignment(22, 33);

        $where = [
            'crs_id' => ['integer', 22]
        ];

        $values = [
            'provider_id' => ['integer', 33],
            'provider_text' => ['text', null]
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('update')
            ->with(self::TABLE_NAME, $values, $where)
        ;

        $db = new ilDB($db_mock);

        try {
            $db->update($list_assignment);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }

    public function testUpdateWithCustomAssignment() : void
    {
        $custom_assignment = new CustomAssignment(22, 'test_text');

        $where = [
            'crs_id' => ['integer', 22]
        ];

        $values = [
            'provider_id' => ['integer', null],
            'provider_text' => ['text', 'test_text']
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('update')
            ->with(self::TABLE_NAME, $values, $where)
        ;

        $db = new ilDB($db_mock);

        try {
            $db->update($custom_assignment);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }

    public function testSelectWithEmptyResult() : void
    {
        $sql =
            "SELECT crs_id, provider_id, provider_text" . PHP_EOL
            . "FROM " . self::TABLE_NAME . PHP_EOL
            . "WHERE crs_id = 22" . PHP_EOL
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

        $result = $db->select(22);

        $this->assertFalse($result);
    }

    public function testSelectWithListAssignment() : void
    {
        $sql =
            "SELECT crs_id, provider_id, provider_text" . PHP_EOL
            . "FROM " . self::TABLE_NAME . PHP_EOL
            . "WHERE crs_id = 22" . PHP_EOL
        ;

        $row = [
            'provider_id' => 33,
            'provider_text' => null
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

        $this->assertInstanceOf(ListAssignment::class, $result);
        $this->assertEquals(22, $result->getCrsId());
        $this->assertEquals(33, $result->getProviderId());
    }

    public function testSelectWithCustomAssignment() : void
    {
        $sql =
            "SELECT crs_id, provider_id, provider_text" . PHP_EOL
            . "FROM " . self::TABLE_NAME . PHP_EOL
            . "WHERE crs_id = 22" . PHP_EOL
        ;

        $row = [
            'provider_id' => null,
            'provider_text' => 'test_text'
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

        $this->assertInstanceOf(CustomAssignment::class, $result);
        $this->assertEquals(22, $result->getCrsId());
        $this->assertEquals('test_text', $result->getProviderText());
    }

    public function testDelete() : void
    {
        $sql =
             "DELETE FROM " . self::TABLE_NAME . PHP_EOL
            . "WHERE crs_id = 22" . PHP_EOL
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

        $db = new ilDB($db_mock);

        try {
            $db->delete(22);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }

    public function testGetAffectedCrsObjIds() : void
    {
        $sql =
            "SELECT crs_id" . PHP_EOL
            . "FROM " . self::TABLE_NAME . PHP_EOL
            . "WHERE provider_id = 22" . PHP_EOL
        ;

        $row = [
            'crs_id' => 33
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
            ->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->with([$row])
            ->willReturn($row, null)
        ;

        $db = new ilDB($db_mock);

        $result = $db->getAffectedCrsObjIds(22);

        $this->assertIsArray($result);
        $this->assertEquals(33, $result[0]);
    }
}
