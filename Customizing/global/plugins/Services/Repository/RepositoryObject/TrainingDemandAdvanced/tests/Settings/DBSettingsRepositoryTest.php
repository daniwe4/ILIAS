<?php

namespace CaT\Plugins\TrainingDemandAdvanced\Settings;

use PHPUnit\Framework\TestCase;

class DBSettingsRepositoryTest extends TestCase
{
    public function test_create_instance()
    {
        $db = $this->createMock(\ilDBInterface::class);

        $db = new DBSettingsRepository($db);
        $this->assertInstanceOf(DBSettingsRepository::class, $db);
    }

    public function test_create_existing()
    {
        $obj_id = 14;
        $q = 'SELECT id' . PHP_EOL
            . ' FROM xtda_settings' . PHP_EOL
            . ' WHERE id = ' . $obj_id
        ;
        $db_result = [
            [
                "id" => $obj_id
            ]
        ];

        $db = $this->createMock(\ilDBInterface::class);
        $db->expects($this->once())
            ->method("quote")
            ->with($obj_id)
            ->willReturn($obj_id)
        ;
        $db->expects($this->once())
            ->method("query")
            ->with($q)
            ->willReturn($db_result)
        ;
        $db->expects($this->once())
            ->method("numRows")
            ->with($db_result)
            ->willReturn(count($db_result))
        ;

        $db = new DBSettingsRepository($db);
        try {
            $db->create($obj_id);
            $this->assertTrue(false);
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_create()
    {
        $obj_id = 11;
        $q = 'SELECT id' . PHP_EOL
            . ' FROM xtda_settings' . PHP_EOL
            . ' WHERE id = ' . $obj_id
        ;
        $db_result = [];
        $values = [
            "id" => ['integer', $obj_id],
            "is_online" => ['integer', false],
            "is_global" => ['integer', false]
        ];

        $db = $this->createMock(\ilDBInterface::class);
        $db->expects($this->once())
            ->method("quote")
            ->with($obj_id)
            ->willReturn($obj_id)
        ;
        $db->expects($this->once())
            ->method("query")
            ->with($q)
            ->willReturn($db_result)
        ;
        $db->expects($this->once())
            ->method("numRows")
            ->with($db_result)
            ->willReturn(count($db_result))
        ;
        $db->expects($this->once())
            ->method("insert")
            ->with("xtda_settings", $values)
        ;

        $db = new DBSettingsRepository($db);
        $settings = $db->create($obj_id);
        $this->assertEquals($obj_id, $settings->id());
        $this->assertFalse($settings->online());
        $this->assertFalse($settings->isGlobal());
        $this->assertEquals([], $settings->getLocalRoles());
    }

    /**
     * @depends test_create
     */
    public function test_load()
    {
        $obj_id = 32;
        $online = "1";
        $global = "0";
        $roles = "A;B;C";
        $db_row = [
            "id" => $obj_id,
            "is_online" => $online,
            "is_global" => $global,
            "local_role" => $roles
        ];
        $db_result = [$db_row];
        $q = "SELECT base.is_online, base.is_global," . PHP_EOL
            . " GROUP_CONCAT(local_roles.local_role SEPARATOR ';') AS local_role" . PHP_EOL
            . " FROM xtda_settings base" . PHP_EOL
            . " LEFT JOIN xtda_local_roles local_roles" . PHP_EOL
            . "     ON local_roles.obj_id = base.id" . PHP_EOL
            . " WHERE base.id = " . $obj_id . PHP_EOL
            . " GROUP BY base.id"
        ;

        $db = $this->createMock(\ilDBInterface::class);
        $db->expects($this->once())
            ->method("quote")
            ->with($obj_id)
            ->willReturn($obj_id)
        ;
        $db->expects($this->once())
            ->method("query")
            ->with($q)
            ->willReturn($db_result)
        ;
        $db->expects($this->once())
            ->method("numRows")
            ->with($db_result)
            ->willReturn(count($db_result))
        ;
        $db->expects($this->once())
            ->method("fetchAssoc")
            ->with($db_result)
            ->willReturn($db_row)
        ;

        $db = new DBSettingsRepository($db);
        $settings = $db->load($obj_id);
        $this->assertEquals($obj_id, $settings->id());
        $this->assertTrue($settings->online());
        $this->assertFalse($settings->isGlobal());
        $this->assertEquals(["A", "B", "C"], $settings->getLocalRoles());
    }

    public function test_update_non_existing()
    {
        $obj_id = 45;
        $online = false;
        $global = true;
        $roles = ["Rolle"];
        $settings = new Settings($obj_id, $online, $global, $roles);
        $q = 'SELECT id' . PHP_EOL
            . ' FROM xtda_settings' . PHP_EOL
            . ' WHERE id = ' . $obj_id
        ;
        $db_result = [
        ];

        $db = $this->createMock(\ilDBInterface::class);
        $db->expects($this->once())
            ->method("quote")
            ->with($obj_id)
            ->willReturn($obj_id)
        ;
        $db->expects($this->once())
            ->method("query")
            ->with($q)
            ->willReturn($db_result)
        ;
        $db->expects($this->once())
            ->method("numRows")
            ->with($db_result)
            ->willReturn(count($db_result))
        ;

        $db = new DBSettingsRepository($db);
        try {
            $db->update($settings);
            $this->assertTrue(false);
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_update_no_roles()
    {
        $obj_id = 45;
        $online = false;
        $global = true;
        $roles = [];
        $settings = new Settings($obj_id, $online, $global, $roles);
        $values = [
            "is_online" => ['integer', $settings->online()]
            ,"is_global" => ['integer', $settings->isGlobal()]
        ];
        $where = [
            "id" => ['integer',$obj_id]
        ];
        $q_exists = 'SELECT id' . PHP_EOL
            . ' FROM xtda_settings' . PHP_EOL
            . ' WHERE id = ' . $obj_id
        ;
        $db_result = [
            [
                "id" => $obj_id
            ]
        ];
        $q_clear_roles = "DELETE FROM xtda_local_roles" . PHP_EOL
            . " WHERE obj_id = " . $obj_id
        ;

        $db = $this->createMock(\ilDBInterface::class);
        $db->expects($this->atLeastOnce())
            ->method("quote")
            ->with($obj_id)
            ->willReturn($obj_id)
        ;
        $db->expects($this->once())
            ->method("query")
            ->with($q_exists)
            ->willReturn($db_result)
        ;
        $db->expects($this->once())
            ->method("numRows")
            ->with($db_result)
            ->willReturn(count($db_result))
        ;
        $db->expects($this->once())
            ->method("update")
            ->with("xtda_settings", $values, $where)
        ;

        $db->expects($this->once())
            ->method("manipulate")
            ->with($q_clear_roles)
        ;

        $db = new DBSettingsRepository($db);
        $db->update($settings);
    }

    public function test_update_with_roles()
    {
        $obj_id = 45;
        $online = false;
        $global = true;
        $roles = ["Bernd" , "hans"];
        $settings = new Settings($obj_id, $online, $global, $roles);
        $values = [
            "is_online" => ['integer', $settings->online()]
            ,"is_global" => ['integer', $settings->isGlobal()]
        ];
        $where = [
            "id" => ['integer',$obj_id]
        ];
        $q_exists = 'SELECT id' . PHP_EOL
            . ' FROM xtda_settings' . PHP_EOL
            . ' WHERE id = ' . $obj_id
        ;
        $q_insert_role = "INSERT INTO xtda_local_roles" . PHP_EOL
            . " (id, obj_id, local_role)" . PHP_EOL
            . " VALUES (?, ?, ?)"
        ;
        $db_result = [
            [
                "id" => $obj_id
            ]
        ];
        $q_clear_roles = "DELETE FROM xtda_local_roles" . PHP_EOL
            . " WHERE obj_id = " . $obj_id
        ;
        $insert_role1 = [
            4,
            $obj_id,
            "Bernd"
        ];
        $insert_role2 = [
            5,
            $obj_id,
            "hans"
        ];
        $prepared = $this->createMock(\ilPDOStatement::class);

        $db = $this->createMock(\ilDBInterface::class);
        $db->expects($this->atLeastOnce())
            ->method("quote")
            ->with($obj_id)
            ->willReturn($obj_id)
        ;
        $db->expects($this->once())
            ->method("query")
            ->with($q_exists)
            ->willReturn($db_result)
        ;
        $db->expects($this->once())
            ->method("numRows")
            ->with($db_result)
            ->willReturn(count($db_result))
        ;
        $db->expects($this->once())
            ->method("update")
            ->with("xtda_settings", $values, $where)
        ;
        $db->expects($this->once())
            ->method("manipulate")
            ->with($q_clear_roles)
        ;
        $db->expects($this->once())
            ->method("prepare")
            ->with($q_insert_role)
            ->willReturn($prepared)
        ;
        $db->expects($this->atLeastOnce())
            ->method("nextId")
            ->with("xtda_local_roles")
            ->will($this->onConsecutiveCalls(4, 5))
        ;
        $db->expects($this->atLeastOnce())
            ->method("execute")
            ->withConsecutive([$prepared, $insert_role1], [$prepared, $insert_role2])
        ;

        $db = new DBSettingsRepository($db);
        $db->update($settings);
    }

    public function test_delete_non_existing()
    {
        $obj_id = 654;
        $online = false;
        $global = false;
        $roles = ["Rolle"];
        $settings = new Settings($obj_id, $online, $global, $roles);
        $q = 'SELECT id' . PHP_EOL
            . ' FROM xtda_settings' . PHP_EOL
            . ' WHERE id = ' . $obj_id
        ;
        $db_result = [
        ];

        $db = $this->createMock(\ilDBInterface::class);
        $db->expects($this->once())
            ->method("quote")
            ->with($obj_id)
            ->willReturn($obj_id)
        ;
        $db->expects($this->once())
            ->method("query")
            ->with($q)
            ->willReturn($db_result)
        ;
        $db->expects($this->once())
            ->method("numRows")
            ->with($db_result)
            ->willReturn(count($db_result))
        ;

        $db = new DBSettingsRepository($db);
        try {
            $db->delete($settings);
            $this->assertTrue(false);
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_delete_existing()
    {
        $obj_id = 654;
        $online = false;
        $global = false;
        $roles = ["Rolle"];
        $settings = new Settings($obj_id, $online, $global, $roles);
        $q = 'SELECT id' . PHP_EOL
            . ' FROM xtda_settings' . PHP_EOL
            . ' WHERE id = ' . $obj_id
        ;
        $db_result = [
            [
                "id" => $obj_id
            ]
        ];
        $q_delete = 'DELETE FROM xtda_settings'
            . '	WHERE id = ' . $obj_id
        ;
        $q_clear_roles = "DELETE FROM xtda_local_roles" . PHP_EOL
            . " WHERE obj_id = " . $obj_id
        ;

        $db = $this->createMock(\ilDBInterface::class);
        $db->expects($this->atLeastOnce())
            ->method("quote")
            ->with($obj_id)
            ->willReturn($obj_id)
        ;
        $db->expects($this->once())
            ->method("query")
            ->with($q)
            ->willReturn($db_result)
        ;
        $db->expects($this->once())
            ->method("numRows")
            ->with($db_result)
            ->willReturn(count($db_result))
        ;
        $db->expects($this->exactly(2))
            ->method("manipulate")
            ->withConsecutive([$q_delete], [$q_clear_roles])
            ->willReturn(count($db_result))
        ;

        $db = new DBSettingsRepository($db);
        $db->delete($settings);
    }
}
