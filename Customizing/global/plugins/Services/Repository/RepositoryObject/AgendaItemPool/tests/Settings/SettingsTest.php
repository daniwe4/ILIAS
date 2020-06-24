<?php
namespace CaT\Plugins\AgendaItemPool\Settings;

use PHPUnit\Framework\TestCase;

/**
 * Class SettingsTest
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class SettingsTest extends TestCase
{
    protected $test_int;
    protected $test_string;
    protected $test_bool;
    protected $test_date;

    public function setUp() : void
    {
        $this->test_int = 33;
        $this->test_string = "testobject";
        $this->test_bool = false;
        $this->test_date = new \DateTime("2020-04-22T09:08:48.055138+0200");
    }

    public function testCreate()
    {
        $object = new Settings(
            $this->test_int,
            $this->test_bool,
            $this->test_date,
            $this->test_int
        );

        $this->assertEquals($object->getObjId(), $this->test_int);
        $this->assertEquals($object->getIsOnline(), $this->test_bool);
        $this->assertEquals($object->getLastChanged(), $this->test_date);
        $this->assertEquals($object->getLastChangedUsrId(), $this->test_int);

        return $object;
    }

    /**
     * @depends testCreate
     */
    public function testWithIsOnline($object)
    {
        $newObject = $object->withIsOnline(true);
        $this->assertEquals($object->getObjId(), $this->test_int);
        $this->assertEquals($object->getIsOnline(), $this->test_bool);
        $this->assertEquals($object->getLastChanged(), $this->test_date);
        $this->assertEquals($object->getLastChangedUsrId(), $this->test_int);

        $this->assertEquals($newObject->getObjId(), $this->test_int);
        $this->assertEquals($newObject->getIsOnline(), true);
        $this->assertEquals($newObject->getLastChanged(), $this->test_date);
        $this->assertEquals($newObject->getLastChangedUsrId(), $this->test_int);
    }

    /**
     * @depends testCreate
     */
    public function testWithLastChanged($object)
    {
        $date = new \DateTime("2017-01-05 15:42:00");
        $newObject = $object->withLastChanged($date);
        $this->assertEquals($object->getObjId(), $this->test_int);
        $this->assertEquals($object->getIsOnline(), $this->test_bool);
        $this->assertEquals($object->getLastChanged(), $this->test_date);
        $this->assertEquals($object->getLastChangedUsrId(), $this->test_int);

        $this->assertEquals($newObject->getObjId(), $this->test_int);
        $this->assertEquals($newObject->getIsOnline(), $this->test_bool);
        $this->assertEquals($newObject->getLastChanged(), $date);
        $this->assertEquals($newObject->getLastChangedUsrId(), $this->test_int);
    }

    /**
     * @depends testCreate
     */
    public function testWithLastChangedUsrId($object)
    {
        $newObject = $object->withLastChangedUsrId(22);
        $this->assertEquals($object->getObjId(), $this->test_int);
        $this->assertEquals($object->getIsOnline(), $this->test_bool);
        $this->assertEquals($object->getLastChanged(), $this->test_date);
        $this->assertEquals($object->getLastChangedUsrId(), $this->test_int);

        $this->assertEquals($newObject->getObjId(), $this->test_int);
        $this->assertEquals($newObject->getIsOnline(), $this->test_bool);
        $this->assertEquals($newObject->getLastChanged(), $this->test_date);
        $this->assertEquals($newObject->getLastChangedUsrId(), 22);
    }
}
