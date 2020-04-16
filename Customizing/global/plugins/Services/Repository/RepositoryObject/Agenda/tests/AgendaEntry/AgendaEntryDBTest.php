<?php

use PHPUnit\Framework\TestCase;
use CaT\Plugins\Agenda\AgendaEntry\ilDB;
use CaT\Plugins\Agenda\AgendaEntry\AgendaEntry;

class AgendaEntryDBTest extends TestCase
{
    public function setUp() : void
    {
        if (!interface_exists("ilDBInterface")) {
            require_once(__DIR__ . "/ilDBInterface.php");
        }
    }

    public function test_create()
    {
        $db = $this->createMock("\ilDBInterface");
        $db
            ->expects($this->once())
            ->method('nextId')
            ->willReturn(5);

        $values = array(
            "id" => array("integer", 5),
            "obj_id" => array("integer", 2),
            "pool_item_id" => array("integer", 3),
            "duration" => array("integer", 20),
            "position" => array("integer", 50),
            "is_blank" => array("integer", false),
            "agenda_item_content" => array("text", "content"),
            "goals" => array("text", "goals")
        );

        $db
            ->expects($this->once())
            ->method('insert')
            ->with(ilDB::TABLE_NAME, $values);

        $item_db = new ilDB($db);
        $item = $item_db->create(2, 3, 20, 50, false, "content", "goals");

        $this->assertInstanceOf("CaT\\Plugins\\Agenda\\AgendaEntry\\AgendaEntry", $item);
        $this->assertEquals(5, $item->getId());
        $this->assertEquals(2, $item->getObjId());
        $this->assertEquals(3, $item->getPoolItemId());
        $this->assertEquals(20, $item->getDuration());
        $this->assertEquals(50, $item->getPosition());
        $this->assertEquals(false, $item->getIsBlank());
        $this->assertEquals("content", $item->getAgendaItemContent());
        $this->assertEquals("goals", $item->getGoals());
    }

    public function test_update()
    {
        $db = $this->createMock("\ilDBInterface");

        $item = new AgendaEntry(5, 2, 3, 20, 50, 0.0, false, "content", "goals");
        $where = array("id" => array("integer", 5));
        $values = array(
            "pool_item_id" => array("integer", 3),
            "duration" => array("integer", 20),
            "position" => array("integer", 50),
            "is_blank" => array("integer", false),
            "agenda_item_content" => array("text", "content"),
            "goals" => array("text", "goals")
        );

        $db
            ->expects($this->once())
            ->method('update')
            ->with(ilDB::TABLE_NAME, $values, $where);

        $item_db = new ilDB($db);
        $item_db->update($item);
    }

    public function test_delete()
    {
        $db = $this->createMock("\ilDBInterface");

        $db
            ->expects($this->once())
            ->method('quote')
            ->with(5, "integer")
            ->willReturn(5);

        $sql = "DELETE FROM " . ilDB::TABLE_NAME . PHP_EOL
                . " WHERE id = 5";
        $db
            ->expects($this->once())
            ->method('manipulate')
            ->with($sql);

        $item_db = new ilDB($db);
        $item_db->delete(5);
    }

    public function test_deleteFor()
    {
        $db = $this->createMock("\ilDBInterface");

        $db
            ->expects($this->once())
            ->method('quote')
            ->with(2, "integer")
            ->willReturn(2);

        $sql = "DELETE FROM " . ilDB::TABLE_NAME . PHP_EOL
                . " WHERE obj_id = 2";
        $db
            ->expects($this->once())
            ->method('manipulate')
            ->with($sql);

        $item_db = new ilDB($db);
        $item_db->deleteFor(2);
    }

    public function test_selectFor()
    {
        $db = $this->createMock("\ilDBInterface");

        $query = "SELECT id, obj_id, pool_item_id, duration, position," . PHP_EOL
                . "     is_blank, agenda_item_content, goals" . PHP_EOL
                . " FROM xage_entries" . PHP_EOL
                . " WHERE obj_id = 2" . PHP_EOL
                . "ORDER BY position" . PHP_EOL
        ;

        $row = array(
            "id" => 5,
            "obj_id" => 2,
            "pool_item_id" => 3,
            "start_time" => 20,
            "end_time" => 50,
            "is_blank" => false,
            "agenda_item_content" => "content",
            "goals" => "goals"

        );

        $db
            ->expects($this->once())
            ->method("quote")
            ->with(2, "integer")
            ->willReturn(2);

        $db
            ->expects($this->once())
            ->method("query")
            ->with($query);

        $db
            ->expects($this->exactly(2))
            ->method("fetchAssoc")
            ->will($this->onConsecutiveCalls($row, false));

        $item_db = new ilDB($db);
        $items = $item_db->selectFor(2);

        $this->assertEquals(1, count($items));
        $this->assertContainsOnlyInstancesOf(
            "CaT\\Plugins\\Agenda\\AgendaEntry\\AgendaEntry",
            $items
        );
    }
}
