<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\Options\Type;

use PHPUnit\Framework\TestCase;

class ilDBTest extends TestCase
{
    public function test_create_instance()
    {
        $db = new ilDB($this->getDBInterface());
        $this->assertInstanceOf(ilDB::class, $db);
    }

    public function test_get_affected_cc_object_obj_ids()
    {
        $id = 20;
        $cc_id_1 = 15;
        $cc_id_2 = 28;

        $query = 'SELECT obj_id' . PHP_EOL
            . ' FROM xccl_data' . PHP_EOL
            . ' WHERE type = ' . $id
        ;

        $row_1 = [
            'obj_id' => $cc_id_1
        ];

        $row_2 = [
            'obj_id' => $cc_id_2
        ];
        $db_result = [
            $row_1,
            $row_2
        ];
        $fnc_result = [
            $cc_id_1,
            $cc_id_2
        ];

        $il_db = $this->getDBInterface();
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
            ->willReturnOnConsecutiveCalls($row_1, $row_2, null)
        ;

        $db = new ilDB($il_db);
        $result = $db->getAffectedCCObjectObjIds($id);
        $this->assertSame($fnc_result, $result);
    }

    protected function getDBInterface()
    {
        return $this->createMock(\ilDBInterface::class);
    }
}
