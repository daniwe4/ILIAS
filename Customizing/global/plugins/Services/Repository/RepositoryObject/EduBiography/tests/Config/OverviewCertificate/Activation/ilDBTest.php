<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\Config\OverviewCertificate\Activation;

use PHPUnit\Framework\TestCase;

class ilDBTest extends TestCase
{
    public function test_create_instance()
    {
        $il_db = $this->createMock(\ilDBInterface::class);
        $db = new ilDB($il_db);
        $this->assertInstanceOf(ilDB::class, $db);
    }

    public function test_save()
    {
        $id = 20;
        $active = true;
        $usr_id = 24;
        $date = new \DateTime();
        $table = "xebr_acc_document";

        $values = [
                "id" => [
                    "integer",
                    $id
                ],
                "active" => [
                    "integer",
                    $active
                ],
                "changed_by" => [
                    "integer",
                    $usr_id
                ],
                "changed_at" => [
                    "text",
                    $date->format("Y-m-d")
                ]
            ];

        $il_db = $this->createMock(\ilDBInterface::class);

        $il_db->expects($this->once())
                ->method("nextId")
                ->with($table)
                ->willReturn($id)
            ;

        $il_db->expects($this->once())
                ->method("insert")
                ->with($table, $values)
            ;

        $db = new ilDB($il_db);
        $db->insert($active, $usr_id, $date);
    }

    public function test_select_no_entry()
    {
        $table = "xebr_acc_document";
        $db_result = [];
        $rows = count($db_result);

        $query = "SELECT active" . PHP_EOL
                . " FROM $table" . PHP_EOL
                . " ORDER BY id DESC" . PHP_EOL
                . " LIMIT 1"
            ;

        $il_db = $this->createMock(\ilDBInterface::class);

        $il_db->expects($this->once())
                ->method("query")
                ->with($query)
                ->willReturn($db_result)
            ;

        $il_db->expects($this->once())
                ->method("numRows")
                ->with($db_result)
                ->willReturn($rows)
            ;

        $db = new ilDB($il_db);
        $result = $db->select();
        $this->assertInstanceOf(Active::class, $result);
        $this->assertFalse($result->isActive());
    }

    public function test_select_entry()
    {
        $table = "xebr_acc_document";
        $row = ["active" => "1"];
        $db_result = [
            $row
        ];
        $rows = count($db_result);

        $query = "SELECT active" . PHP_EOL
            . " FROM $table" . PHP_EOL
            . " ORDER BY id DESC" . PHP_EOL
            . " LIMIT 1"
        ;

        $il_db = $this->createMock(\ilDBInterface::class);

        $il_db->expects($this->once())
            ->method("query")
            ->with($query)
            ->willReturn($db_result)
        ;

        $il_db->expects($this->once())
            ->method("numRows")
            ->with($db_result)
            ->willReturn($rows)
        ;

        $il_db->expects($this->once())
            ->method("fetchAssoc")
            ->with($db_result)
            ->willReturn($row)
        ;

        $db = new ilDB($il_db);
        $result = $db->select();
        $this->assertInstanceOf(Active::class, $result);
        $this->assertTrue($result->isActive());
    }
}
