<?php
namespace CaT\Plugins\AgendaItemPool\AgendaItem;

use PHPUnit\Framework\TestCase;

/**
 * Wrapper class for testing protected and private methods.
 */
class _ilDB extends ilDB
{
    public function _createAgendaItemObject(array $obj_values)
    {
        return $this->createAgendaItemObject($obj_values);
    }
}

/**
 * Class ilDB
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class ilDBTest extends TestCase
{
    const OBJ_ID = 1;
    const TITLE = "test_title";
    const DESCRIPTION = "test_description";
    const IS_ACTIVE = true;
    const IDD_RELEVANT = false;
    const IS_DELETED = false;
    const LAST_CHANGE = "2015-01-01 00:00:00";
    const CHANGE_USR_ID = 6;
    const POOL_ID = 33;
    const IS_BLANK = false;
    const GOALS = "goals";
    const GDV_LEARNING_CONTENT = "gdv_learning_content";
    const IDD_LEARNING_CONTENT = "idd_learning_content";
    const AGENDA_ITEM_CONTENT = "agenda_item_content";
    const TEST_STR = "1,2,3";


    /**
     * @var array
     */
    protected $sql_values;

    public function setUp() : void
    {
        if (!interface_exists("ilDBInterface")) {
            require_once(__DIR__ . "/../ilDBInterface.php");
        }
        $this->db = $this->createMock("\ilDBInterface");
        $this->db
            ->expects($this->any())
            ->method('quote')
            ->with(self::OBJ_ID, "integer")
            ->willReturn(self::OBJ_ID);

        $datetime = new \DateTime(self::LAST_CHANGE, new \DateTimeZone("Europe/Berlin"));
        $this->sql_values = array(
            'obj_id' => ['integer', self::OBJ_ID],
            'title' => ['text', self::TITLE],
            'description' => ['text', ""],
            'is_active' => ['integer', self::IS_ACTIVE],
            'idd_relevant' => ['integer', self::IDD_RELEVANT],
            'is_deleted' => ['integer', self::IS_DELETED],
            'last_change' => ['text', $datetime->format("Y-m-d H:i:s")],
            'change_usr_id' => ['integer', self::CHANGE_USR_ID],
            'pool_id' => ['integer', self::POOL_ID],
            'is_blank' => ['integer', 0],
            'goals' => ['text', ""],
            'gdv_learning_content' => ['text', ""],
            'idd_learning_content' => ['text', ""],
            'agenda_item_content' => ['text', ""]
        );
        $this->obj_values = array(
            'obj_id' => self::OBJ_ID,
            'title' => self::TITLE,
            'description' => self::DESCRIPTION,
            'is_active' => self::IS_ACTIVE,
            'idd_relevant' => self::IDD_RELEVANT,
            'is_deleted' => self::IS_DELETED,
            'last_change' => $datetime->format("Y-m-d H:i:s"),
            'change_usr_id' => self::CHANGE_USR_ID,
            'pool_id' => self::POOL_ID,
            'is_blank' => self::IS_BLANK,
            'topics' => self::TEST_STR,
            'goals' => self::GOALS,
            'gdv_learning_content' => self::GDV_LEARNING_CONTENT,
            "idd_learning_content" => self::IDD_LEARNING_CONTENT,
            "agenda_item_content" => self::AGENDA_ITEM_CONTENT
            );
    }

    public function testCreate()
    {
        $datetime = new \DateTime(self::LAST_CHANGE, new \DateTimeZone("Europe/Berlin"));
        $test_arr = $this->sql_values;
        $this->db
            ->expects($this->once())
            ->method("nextId")
            ->with(_ilDB::TABLE_AGENDA_ITEMS)
            ->will($this->returnCallback(function ($i) {
                return is_string($i);
            }));
        $this->db
            ->expects($this->once())
            ->method("insert")
            ->with(_ilDB::TABLE_AGENDA_ITEMS, $test_arr);

        $ilDB = new _ilDB($this->db);
        $agenda_item = $ilDB->create(
            self::TITLE,
            $datetime,
            self::CHANGE_USR_ID,
            self::POOL_ID,
            "",
            true,
            false,
            false,
            false,
            array(),
            "",
            "",
            "",
            ""
        );
        $this->assertEquals(self::TITLE, $agenda_item->getTitle());
        $this->assertEquals(self::LAST_CHANGE, $agenda_item->getLastChange()->format('Y-m-d H:i:s'));

        return $agenda_item;
    }

    /**
     * @depends testCreate
     */
    public function testUpdate($agenda_item)
    {
        $test_arr = $this->sql_values;
        array_shift($test_arr);
        $where = ['obj_id' => ['integer', self::OBJ_ID]];
        $this->db
            ->expects($this->any())
            ->method('quote')
            ->with(self::OBJ_ID, "integer")
            ->willReturn(self::OBJ_ID);
        $this->db
            ->expects($this->once())
            ->method('update')
            ->with(_ilDB::TABLE_AGENDA_ITEMS, $test_arr, $where);

        $ilDB = new _ilDB($this->db);
        $ilDB->update($agenda_item);
    }

    public function testSelectFor()
    {
        $query = "SELECT" . PHP_EOL
                . "    A.obj_id," . PHP_EOL
                . "    A.title," . PHP_EOL
                . "    A.description," . PHP_EOL
                . "    A.is_active," . PHP_EOL
                . "    A.idd_relevant," . PHP_EOL
                . "    A.is_deleted," . PHP_EOL
                . "    A.last_change," . PHP_EOL
                . "    A.change_usr_id," . PHP_EOL
                . "    A.pool_id," . PHP_EOL
                . "    A.is_blank," . PHP_EOL
                . "    GROUP_CONCAT(DISTINCT B.caption_id SEPARATOR ',') AS topics," . PHP_EOL
                . "    A.goals," . PHP_EOL
                . "    A.gdv_learning_content," . PHP_EOL
                . "    A.idd_learning_content," . PHP_EOL
                . "    A.agenda_item_content" . PHP_EOL
                . "FROM " . _ilDB::TABLE_AGENDA_ITEMS . " A" . PHP_EOL
                . "LEFT JOIN " . _ilDB::TABLE_TOPICS . " B" . PHP_EOL
                . "    ON A.obj_id = B.agenda_item_id" . PHP_EOL
                . "WHERE" . PHP_EOL
                . "    A.obj_id = " . $this->db->quote(self::OBJ_ID, 'integer') . PHP_EOL
                . "GROUP BY" . PHP_EOL
                . "    A.obj_id" . PHP_EOL
                ;
        $this->db
            ->expects($this->once())
            ->method("query")
            ->with($query);
        $this->db
            ->expects($this->once())
            ->method("numRows")
            ->willReturn(true);
        $this->db
            ->expects($this->once())
            ->method("fetchAssoc")
            ->willReturn($this->obj_values);

        $ilDB = new _ilDB($this->db);
        $ilDB->selectFor(self::OBJ_ID);
    }

    public function testDeleteFor()
    {
        $query_arr = array();
        $multi_select_tables = array(
            _ilDB::TABLE_TOPICS
        );

        foreach ($multi_select_tables as $table) {
            $query = "DELETE FROM " . $table . PHP_EOL
                    . "WHERE agenda_item_id = " . $this->db->quote(self::OBJ_ID, "integer");
            $query_arr[] = [$query];
        }
        $query = "DELETE FROM " . _ilDB::TABLE_AGENDA_ITEMS . PHP_EOL
                . "WHERE" . PHP_EOL
                . "obj_id in(1)";
        $query_arr[] = [$query];

        $this->db
            ->expects($this->once())
            ->method("in")
            ->with("obj_id", array(1), false, "integer")
            ->willReturn("obj_id in(1)");
        $temp = $this->db
            ->expects($this->any())
            ->method("manipulate");
        call_user_func_array([$temp, "withConsecutive"], $query_arr);

        $ilDB = new _ilDB($this->db);
        $ilDB->deleteFor(array(1));
    }

    public function testCreateAgendaItemObject()
    {
        $ilDB = new _ilDB($this->db);
        $agenda_item = $ilDB->_createAgendaItemObject($this->obj_values);
        $datetime = new \DateTime(self::LAST_CHANGE, new \DateTimeZone("Europe/Berlin"));

        $this->assertEquals($agenda_item->getObjId(), self::OBJ_ID);
        $this->assertEquals($agenda_item->getTitle(), self::TITLE);
        $this->assertEquals($agenda_item->getDescription(), self::DESCRIPTION);
        $this->assertEquals($agenda_item->getIsActive(), self::IS_ACTIVE);
        $this->assertEquals($agenda_item->getIddRelevant(), self::IDD_RELEVANT);
        $this->assertEquals($agenda_item->getIsDeleted(), self::IS_DELETED);
        $this->assertEquals($agenda_item->getLastChange(), $datetime);
        $this->assertEquals($agenda_item->getChangeUsrId(), self::CHANGE_USR_ID);
        $this->assertEquals($agenda_item->getPoolId(), self::POOL_ID);
        $this->assertEquals($agenda_item->getIsBlank(), self::IS_BLANK);
        $this->assertEquals($agenda_item->getAgendaItemContent(), self::AGENDA_ITEM_CONTENT);
    }
}
