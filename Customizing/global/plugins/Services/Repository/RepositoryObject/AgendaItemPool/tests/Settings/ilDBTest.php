<?php
namespace CaT\Plugins\AgendaItemPool\Settings;

use PHPUnit\Framework\TestCase;

/**
 * Wrapper class for testing protected and private methods.
 */
class _ilDB extends ilDB
{
    public function _createSettingsObject($obj_id)
    {
        return $this->createSettingsObject($obj_id);
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
    const IS_ONLINE = false;
    const LAST_CHANGED = "2017-11-11 12:33:00";
    const LAST_CHANGED_USR_ID = null;

    /**
     * @var array
     */
    protected $sql_values;

    /**
     * @var array
     */
    protected $obj_values;

    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function setUp() : void
    {
        if (!interface_exists("ilDBInterface")) {
            require_once(__DIR__ . "/../ilDBInterface.php");
        }

        $this->db = $this->createMock("\ilDBInterface");
        $datetime = new \DateTime(self::LAST_CHANGED, new \DateTimeZone("Europe/Berlin"));

        $this->sql_values = array(
            'obj_id' => ['integer', self::OBJ_ID],
            'is_online' => ['integer', self::IS_ONLINE],
            'last_changed' => ['text', null],
            'last_changed_usr_id' => ['integer', self::LAST_CHANGED_USR_ID]
        );

        $this->obj_values = array(
            'obj_id' => self::OBJ_ID,
            'is_online' => self::IS_ONLINE,
            'last_changed' => self::LAST_CHANGED,
            'last_changed_usr_id' => self::LAST_CHANGED_USR_ID
        );
    }

    public function testCreate()
    {
        $this->db
            ->expects($this->once())
            ->method("insert")
            ->with(_ilDB::TABLE_SETTINGS, $this->sql_values);
        $ilDB = new _ilDB($this->db);
        $aip = $ilDB->create(self::OBJ_ID);

        $this->assertEquals($aip->getObjId(), self::OBJ_ID);
        $this->assertEquals($aip->getIsOnline(), self::IS_ONLINE);
        $this->assertEquals($aip->getLastChanged(), null);
        $this->assertEquals($aip->getLastChangedUsrId(), self::LAST_CHANGED_USR_ID);

        return $aip;
    }

    /**
     * @depends testCreate
     */
    public function testUpdate($agenda_item_pool)
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
            ->with(_ilDB::TABLE_SETTINGS, $test_arr, $where);

        $ilDB = new _ilDB($this->db);
        $ilDB->update($agenda_item_pool);
    }

    public function testSelectFor()
    {
        $query = "SELECT" . PHP_EOL
                . "    obj_id," . PHP_EOL
                . "    is_online," . PHP_EOL
                . "    last_changed," . PHP_EOL
                . "    last_changed_usr_id" . PHP_EOL
                . "FROM " . _ilDB::TABLE_SETTINGS . PHP_EOL
                . "WHERE" . PHP_EOL
                . "    obj_id = " . $this->db->quote(self::OBJ_ID, "integer") . PHP_EOL
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
        $query = "DELETE FROM " . _ilDB::TABLE_SETTINGS . PHP_EOL
                . "WHERE" . PHP_EOL
                . "    obj_id = 1" . PHP_EOL
                ;
        $this->db
            ->expects($this->once())
            ->method("quote")
            ->with(self::OBJ_ID, "integer")
            ->willReturn(self::OBJ_ID);
        $this->db
            ->expects($this->once())
            ->method("manipulate")
            ->with($query);

        $ilDB = new _ilDB($this->db);
        $ilDB->deleteFor(self::OBJ_ID);
    }

    public function testSettingsObject()
    {
        $datetime = new \DateTime(self::LAST_CHANGED, new \DateTimeZone("Europe/Berlin"));
        $test_arr = [
            "obj_id" => self::OBJ_ID,
            "is_online" => self::IS_ONLINE,
            "last_changed" => self::LAST_CHANGED,
            "last_changed_usr_id" => self::LAST_CHANGED_USR_ID
        ];
        $ilDB = new _ilDB($this->db);
        $aip = $ilDB->_createSettingsObject($test_arr);
        $this->assertEquals($aip->getObjId(), self::OBJ_ID);
        $this->assertEquals($aip->getIsOnline(), self::IS_ONLINE);
        $this->assertEquals($aip->getLastChanged(), new \DateTime(self::LAST_CHANGED, new \DateTimeZone("Europe/Berlin")));
        $this->assertEquals($aip->getLastChangedUsrId(), self::LAST_CHANGED_USR_ID);
    }
}
