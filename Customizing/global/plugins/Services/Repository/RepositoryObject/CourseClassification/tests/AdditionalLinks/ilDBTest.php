<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\AdditionalLinks;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ilDBTest extends TestCase
{
    public function create_instance()
    {
        $db = new ilDB($this->getIliasDB());
        $this->assertInstanceOf(ilDB::class, $db);
    }

    public function test_select_for()
    {
        $id = 20;

        $row = [
            'label' => 'Label Link 1',
            'url' => 'www.url.de'
        ];

        $row_2 = [
            'label' => 'Label Link 2',
            'url' => 'www.url.de'
        ];

        $db_result = [$row, $row_2];

        $query = 'SELECT label, url' . PHP_EOL
            . 'FROM xccl_data_links' . PHP_EOL
            . 'WHERE obj_id = ' . $id
        ;

        $il_db = $this->getIliasDB();
        $il_db->expects($this->once())
            ->method('quote')
            ->with($id)
            ->willReturn($id)
        ;
        $il_db->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn($db_result)
        ;
        $il_db->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->with($db_result)
            ->willReturnOnConsecutiveCalls($row, $row_2, null)
        ;

        $db = new ilDB($il_db);
        $result = $db->selectFor($id);
        $this->assertIsArray($result);
        $this->assertEquals(count($db_result), count($result));

        foreach ($result as $res) {
            $this->assertInstanceOf(AdditionalLink::class, $res);
        }
    }

    public function test_store_for()
    {
        $id = 45;

        $atom_query = $this->createMock(\ilAtomQuery::class);

        $atom_query->expects($this->atLeastOnce())
            ->method('addTableLock')
            ->withConsecutive(['xccl_data_links'], ['xccl_data_links_seq'])
        ;
        $atom_query->expects($this->once())
            ->method('run')
        ;

        $il_db = $this->getIliasDB();
        $il_db->expects($this->once())
            ->method('buildAtomQuery')
            ->willReturn($atom_query)
        ;

        $db = new ilDB($il_db);
        $db->storeFor($id, []);
    }

    public function test_delete_for()
    {
        $id = 34;

        $query = 'DELETE FROM xccl_data_links' . PHP_EOL
            . 'WHERE obj_id=' . $id
        ;

        $il_db = $this->getIliasDB();
        $il_db->expects($this->once())
            ->method('quote')
            ->with($id)
            ->willReturn($id)
        ;
        $il_db->expects($this->once())
            ->method('manipulate')
            ->with($query)
        ;

        $db = new ilDB($il_db);
        $db->deleteFor($id);
    }

    protected function getIliasDB() : MockObject
    {
        return $this->createMock(\ilDBInterface::class);
    }
}
