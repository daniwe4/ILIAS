<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingProvider\Trainer;

use PHPUnit\Framework\TestCase;
use CaT\Plugins\TrainingProvider\Mocks;

class ilDBTest extends TestCase
{
    const TABLE_NAME = 'tp_trainer';
    const TABLE_PROVIDER = 'tp_provider';

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
            'title' => ['text', 'test_title'],
            'salutation' => ['text', 'test_salutation'],
            'firstname' => ['text', 'test_firstname'],
            'lastname' => ['text', 'test_lastname'],
            'provider_id' => ['integer', 45],
            'email' => ['text', 'test_email'],
            'phone' => ['text', 'test_phone'],
            'mobile_number' => ['text', 'test_mobile_number'],
            'fee' => ['float', 0.2],
            'extra_infos' => ['text', 'test_extra_infos'],
            'active' => ['integer', 0]
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

        $result = $db->create(
            'test_title',
            'test_salutation',
            'test_firstname',
            'test_lastname',
            45,
            'test_email',
            'test_phone',
            'test_mobile_number',
            0.2,
            'test_extra_infos',
            false
        );

        $this->assertEquals(22, $result->getId());
        $this->assertEquals('test_title', $result->getTitle());
        $this->assertEquals('test_salutation', $result->getSalutation());
        $this->assertEquals('test_firstname', $result->getFirstname());
        $this->assertEquals('test_lastname', $result->getLastname());
        $this->assertEquals(45, $result->getProviderId());
        $this->assertEquals('test_email', $result->getEmail());
        $this->assertEquals('test_phone', $result->getPhone());
        $this->assertEquals('test_mobile_number', $result->getMobileNumber());
        $this->assertEquals(0.2, $result->getFee());
        $this->assertEquals('test_extra_infos', $result->getExtraInfos());
        $this->assertFalse($result->getActive());
    }

    public function testSelectWithEmptyResult() : void
    {
        $sql =
            'SELECT title, salutation, firstname, lastname, provider_id, email, phone, mobile_number, fee, extra_infos, active' . PHP_EOL
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
        $this->expectExceptionMessage('No trainer found for id: 22');
        $db->select(22);
    }

    public function testSelect() : void
    {
        $sql =
            'SELECT title, salutation, firstname, lastname, provider_id, email, phone, mobile_number, fee, extra_infos, active' . PHP_EOL
            . 'FROM ' . self::TABLE_NAME . PHP_EOL
            . 'WHERE id = 22' . PHP_EOL
        ;

        $row = [
            'id' => 22,
            'title' => 'test_title',
            'salutation' => 'test_salutation',
            'firstname' => 'test_firstname',
            'lastname' => 'test_lastname',
            'provider_id' => 45,
            'email' => 'test_email',
            'phone' => 'test_phone',
            'mobile_number' => 'test_mobile_number',
            'fee' => 0.2,
            'extra_infos' => 'test_extra_infos',
            'active' => false
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

        $this->assertEquals(22, $result->getId());
        $this->assertEquals('test_title', $result->getTitle());
        $this->assertEquals('test_salutation', $result->getSalutation());
        $this->assertEquals('test_firstname', $result->getFirstname());
        $this->assertEquals('test_lastname', $result->getLastname());
        $this->assertEquals(45, $result->getProviderId());
        $this->assertEquals('test_email', $result->getEmail());
        $this->assertEquals('test_phone', $result->getPhone());
        $this->assertEquals('test_mobile_number', $result->getMobileNumber());
        $this->assertEquals(0.2, $result->getFee());
        $this->assertEquals('test_extra_infos', $result->getExtraInfos());
        $this->assertFalse($result->getActive());
    }

    public function testUpdate() : void
    {
        $trainer = new Trainer(
            22,
            'test_title',
            'test_salutation',
            'test_firstname',
            'test_lastname',
            45,
            'test_email',
            'test_phone',
            'test_mobile_number',
            0.2,
            'test_extra_infos',
            false
        );

        $where = ['id' => ['integer', $trainer->getId()]];

        $values = [
            'title' => ['text', 'test_title'],
            'salutation' => ['text', 'test_salutation'],
            'firstname' => ['text', 'test_firstname'],
            'lastname' => ['text', 'test_lastname'],
            'provider_id' => ['integer', 45],
            'email' => ['text', 'test_email'],
            'phone' => ['text', 'test_phone'],
            'mobile_number' => ['text', 'test_mobile_number'],
            'fee' => ['float', 0.2],
            'extra_infos' => ['text', 'test_extra_infos'],
            'active' => ['integer', 0]
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('update')
            ->with(self::TABLE_NAME, $values, $where)
        ;

        $db = new ilDB($db_mock);

        try {
            $db->update($trainer);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            echo $e;
            $this->assertTrue(false);
        }
    }

    public function testDelete() : void
    {
        $sql =
             'DELETE FROM ' . self::TABLE_NAME . PHP_EOL
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

    public function testGetTrainerOfWithEmptyResult()
    {
        $sql =
            'SELECT id, title, salutation, firstname, lastname, email, phone, mobile_number, fee, extra_infos, active' . PHP_EOL
            . 'FROM ' . self::TABLE_NAME . PHP_EOL
            . 'WHERE provider_id = 22' . PHP_EOL
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

        $result = $db->getTrainerOf(22);
        $this->assertIsArray($result);
        $this->assertTrue(count($result) == 0);
    }

    public function testGetTrainerOf()
    {
        $sql =
            'SELECT id, title, salutation, firstname, lastname, email, phone, mobile_number, fee, extra_infos, active' . PHP_EOL
            . 'FROM ' . self::TABLE_NAME . PHP_EOL
            . 'WHERE provider_id = 45' . PHP_EOL
        ;

        $row = [
            'id' => 22,
            'title' => 'test_title',
            'salutation' => 'test_salutation',
            'firstname' => 'test_firstname',
            'lastname' => 'test_lastname',
            'provider_id' => 45,
            'email' => 'test_email',
            'phone' => 'test_phone',
            'mobile_number' => 'test_mobile_number',
            'fee' => 0.2,
            'extra_infos' => 'test_extra_infos',
            'active' => false
        ];


        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('quote')
            ->with(45, 'integer')
            ->willReturn(45)
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

        $result = $db->getTrainerOf(45);

        $this->assertIsArray($result);
        $this->assertTrue(count($result) === 1);

        $result = $result[22];

        $this->assertEquals(22, $result->getId());
        $this->assertEquals('test_title', $result->getTitle());
        $this->assertEquals('test_salutation', $result->getSalutation());
        $this->assertEquals('test_firstname', $result->getFirstname());
        $this->assertEquals('test_lastname', $result->getLastname());
        $this->assertEquals(45, $result->getProviderId());
        $this->assertEquals('test_email', $result->getEmail());
        $this->assertEquals('test_phone', $result->getPhone());
        $this->assertEquals('test_mobile_number', $result->getMobileNumber());
        $this->assertEquals(0.2, $result->getFee());
        $this->assertEquals('test_extra_infos', $result->getExtraInfos());
        $this->assertFalse($result->getActive());
    }

    public function testGetTrainersRawWithoutProviderId() : void
    {
        $where = null;
        $sql =
            'SELECT train.id, train.lastname, train.firstname, train.title,' . PHP_EOL
            . 'train.email, train.phone, train.mobile_number, train.fee, train.extra_infos, train.active,' . PHP_EOL
            . 'prov.name AS provider' . PHP_EOL
            . 'FROM ' . self::TABLE_NAME . ' train' . PHP_EOL
            . 'JOIN ' . self::TABLE_PROVIDER . ' prov' . PHP_EOL
            . '    ON train.provider_id = prov.id' . PHP_EOL
            . $where . PHP_EOL
        ;

        $row = [
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

        $result = $db->getTrainersRaw(null);

        $this->assertIsArray($result);
    }

    public function testGetTrainersRawWithProviderId() : void
    {
        $where = 'WHERE train.provider_id = 22' . PHP_EOL;

        $sql =
            'SELECT train.id, train.lastname, train.firstname, train.title,' . PHP_EOL
            . 'train.email, train.phone, train.mobile_number, train.fee, train.extra_infos, train.active,' . PHP_EOL
            . 'prov.name AS provider' . PHP_EOL
            . 'FROM ' . self::TABLE_NAME . ' train' . PHP_EOL
            . 'JOIN ' . self::TABLE_PROVIDER . ' prov' . PHP_EOL
            . '    ON train.provider_id = prov.id' . PHP_EOL
            . $where . PHP_EOL
        ;

        $row = [
            'name' => 'test_name'
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

        $result = $db->getTrainersRaw(22);

        $this->assertIsArray($result);
        $this->assertEquals('test_name', $result[0]['name']);
    }

    public function testGetTrainersRawWithProviderIdAndActiveFilter() : void
    {
        $where = 'WHERE train.provider_id = 22' . PHP_EOL;
        $where .= '     AND ';
        $where .= 'train.active IN (1)' . PHP_EOL;

        $sql =
            'SELECT train.id, train.lastname, train.firstname, train.title,' . PHP_EOL
            . 'train.email, train.phone, train.mobile_number, train.fee, train.extra_infos, train.active,' . PHP_EOL
            . 'prov.name AS provider' . PHP_EOL
            . 'FROM ' . self::TABLE_NAME . ' train' . PHP_EOL
            . 'JOIN ' . self::TABLE_PROVIDER . ' prov' . PHP_EOL
            . '    ON train.provider_id = prov.id' . PHP_EOL
            . $where . PHP_EOL
        ;

        $row = [
            'name' => 'test_name'
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
            ->method('in')
            ->with('train.active', [1], false, 'integer')
            ->willReturn('train.active IN (1)')
        ;
        $db_mock
            ->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->with([$row])
            ->willReturn($row, null)
        ;

        $db = new ilDB($db_mock);

        $result = $db->getTrainersRaw(22, [1]);

        $this->assertIsArray($result);
        $this->assertEquals('test_name', $result[0]['name']);
    }

    public function testDeleteByProvider() : void
    {
        $sql =
            'DELETE FROM ' . self::TABLE_NAME . PHP_EOL
            . 'WHERE provider_id = 22' . PHP_EOL
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
            $db->deleteByProvider(22);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }
}
