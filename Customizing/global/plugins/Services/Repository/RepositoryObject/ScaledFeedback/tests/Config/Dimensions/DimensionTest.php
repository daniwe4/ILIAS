<?php
namespace CaT\Plugins\ScaledFeedback\Config\Dimensions;

use PHPUnit\Framework\TestCase;

/**
 * Class DimensionTest
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class DimensionTest extends TestCase
{
    public function setUp() : void
    {
    }

    public function testCreate()
    {
        $object = new Dimension(-1, "TESTOBJECT", "TESTTITLE");
        $this->assertEquals($object->getTitle(), "TESTOBJECT");
        $this->assertEquals($object->getDisplayedTitle(), "TESTTITLE");
        $this->assertEquals($object->getInfo(), "");
        $this->assertEquals($object->getLabel1(), "");
        $this->assertEquals($object->getLabel2(), "");
        $this->assertEquals($object->getLabel3(), "");
        $this->assertEquals($object->getLabel4(), "");
        $this->assertEquals($object->getLabel5(), "");
        $this->assertEquals($object->getIsLocked(), false);
        $this->assertEquals($object->getEnableComment(), false);
        $this->assertEquals($object->getOnlyTextualFeedback(), false);
        $this->assertEquals($object->getIsUsed(), false);
        $this->assertEquals($object->getOrdernumber(), 0);

        return $object;
    }

    /**
     * @depends testCreate
     */
    public function testWithInfo($object)
    {
        $newObject = $object->withInfo("TESTSTRING");
        $this->assertEquals($object->getTitle(), "TESTOBJECT");
        $this->assertEquals($object->getDisplayedTitle(), "TESTTITLE");
        $this->assertEquals($object->getInfo(), "");
        $this->assertEquals($object->getLabel1(), "");
        $this->assertEquals($object->getLabel2(), "");
        $this->assertEquals($object->getLabel3(), "");
        $this->assertEquals($object->getLabel4(), "");
        $this->assertEquals($object->getLabel5(), "");
        $this->assertEquals($object->getIsLocked(), false);
        $this->assertEquals($object->getEnableComment(), false);
        $this->assertEquals($object->getOnlyTextualFeedback(), false);
        $this->assertEquals($object->getOrdernumber(), 0);
        $this->assertEquals($object->getIsUsed(), false);

        $this->assertEquals($newObject->getTitle(), "TESTOBJECT");
        $this->assertEquals($newObject->getDisplayedTitle(), "TESTTITLE");
        $this->assertEquals($newObject->getInfo(), "TESTSTRING");
        $this->assertEquals($newObject->getLabel1(), "");
        $this->assertEquals($newObject->getLabel2(), "");
        $this->assertEquals($newObject->getLabel3(), "");
        $this->assertEquals($newObject->getLabel4(), "");
        $this->assertEquals($newObject->getLabel5(), "");
        $this->assertEquals($newObject->getIsLocked(), false);
        $this->assertEquals($newObject->getEnableComment(), false);
        $this->assertEquals($newObject->getOnlyTextualFeedback(), false);
        $this->assertEquals($newObject->getOrdernumber(), 0);
        $this->assertEquals($newObject->getIsUsed(), false);
        $this->assertNotSame($newObject, $object);
    }

    /**
     * @depends testCreate
     */
    public function testWithLabel1($object)
    {
        $newObject = $object->withLabel1("TESTSTRING");
        $this->assertEquals($object->getTitle(), "TESTOBJECT");
        $this->assertEquals($object->getDisplayedTitle(), "TESTTITLE");
        $this->assertEquals($object->getInfo(), "");
        $this->assertEquals($object->getLabel1(), "");
        $this->assertEquals($object->getLabel2(), "");
        $this->assertEquals($object->getLabel3(), "");
        $this->assertEquals($object->getLabel4(), "");
        $this->assertEquals($object->getLabel5(), "");
        $this->assertEquals($object->getIsLocked(), false);
        $this->assertEquals($object->getEnableComment(), false);
        $this->assertEquals($object->getOnlyTextualFeedback(), false);
        $this->assertEquals($object->getOrdernumber(), 0);
        $this->assertEquals($object->getIsUsed(), false);

        $this->assertEquals($newObject->getTitle(), "TESTOBJECT");
        $this->assertEquals($newObject->getDisplayedTitle(), "TESTTITLE");
        $this->assertEquals($newObject->getInfo(), "");
        $this->assertEquals($newObject->getLabel1(), "TESTSTRING");
        $this->assertEquals($newObject->getLabel2(), "");
        $this->assertEquals($newObject->getLabel3(), "");
        $this->assertEquals($newObject->getLabel4(), "");
        $this->assertEquals($newObject->getLabel5(), "");
        $this->assertEquals($newObject->getIsLocked(), false);
        $this->assertEquals($newObject->getEnableComment(), false);
        $this->assertEquals($newObject->getOnlyTextualFeedback(), false);
        $this->assertEquals($newObject->getOrdernumber(), 0);
        $this->assertEquals($newObject->getIsUsed(), false);
        $this->assertNotSame($newObject, $object);
    }

    /**
     * @depends testCreate
     */
    public function testWithLabel2($object)
    {
        $newObject = $object->withLabel2("TESTSTRING");
        $this->assertEquals($object->getTitle(), "TESTOBJECT");
        $this->assertEquals($object->getDisplayedTitle(), "TESTTITLE");
        $this->assertEquals($object->getInfo(), "");
        $this->assertEquals($object->getLabel1(), "");
        $this->assertEquals($object->getLabel2(), "");
        $this->assertEquals($object->getLabel3(), "");
        $this->assertEquals($object->getLabel4(), "");
        $this->assertEquals($object->getLabel5(), "");
        $this->assertEquals($object->getIsLocked(), false);
        $this->assertEquals($object->getEnableComment(), false);
        $this->assertEquals($object->getOnlyTextualFeedback(), false);
        $this->assertEquals($object->getOrdernumber(), 0);
        $this->assertEquals($object->getIsUsed(), false);

        $this->assertEquals($newObject->getTitle(), "TESTOBJECT");
        $this->assertEquals($newObject->getDisplayedTitle(), "TESTTITLE");
        $this->assertEquals($newObject->getInfo(), "");
        $this->assertEquals($newObject->getLabel1(), "");
        $this->assertEquals($newObject->getLabel2(), "TESTSTRING");
        $this->assertEquals($newObject->getLabel3(), "");
        $this->assertEquals($newObject->getLabel4(), "");
        $this->assertEquals($newObject->getLabel5(), "");
        $this->assertEquals($newObject->getIsLocked(), false);
        $this->assertEquals($newObject->getEnableComment(), false);
        $this->assertEquals($newObject->getOnlyTextualFeedback(), false);
        $this->assertEquals($newObject->getOrdernumber(), 0);
        $this->assertEquals($newObject->getIsUsed(), false);
        $this->assertNotSame($newObject, $object);
    }

    /**
     * @depends testCreate
     */
    public function testWithLabel3($object)
    {
        $newObject = $object->withLabel3("TESTSTRING");
        $this->assertEquals($object->getTitle(), "TESTOBJECT");
        $this->assertEquals($object->getDisplayedTitle(), "TESTTITLE");
        $this->assertEquals($object->getInfo(), "");
        $this->assertEquals($object->getLabel1(), "");
        $this->assertEquals($object->getLabel2(), "");
        $this->assertEquals($object->getLabel3(), "");
        $this->assertEquals($object->getLabel4(), "");
        $this->assertEquals($object->getLabel5(), "");
        $this->assertEquals($object->getIsLocked(), false);
        $this->assertEquals($object->getEnableComment(), false);
        $this->assertEquals($object->getOnlyTextualFeedback(), false);
        $this->assertEquals($object->getOrdernumber(), 0);
        $this->assertEquals($object->getIsUsed(), false);

        $this->assertEquals($newObject->getTitle(), "TESTOBJECT");
        $this->assertEquals($newObject->getDisplayedTitle(), "TESTTITLE");
        $this->assertEquals($newObject->getInfo(), "");
        $this->assertEquals($newObject->getLabel1(), "");
        $this->assertEquals($newObject->getLabel2(), "");
        $this->assertEquals($newObject->getLabel3(), "TESTSTRING");
        $this->assertEquals($newObject->getLabel4(), "");
        $this->assertEquals($newObject->getLabel5(), "");
        $this->assertEquals($newObject->getIsLocked(), false);
        $this->assertEquals($newObject->getEnableComment(), false);
        $this->assertEquals($newObject->getOnlyTextualFeedback(), false);
        $this->assertEquals($newObject->getOrdernumber(), 0);
        $this->assertEquals($newObject->getIsUsed(), false);
        $this->assertNotSame($newObject, $object);
    }

    /**
     * @depends testCreate
     */
    public function testWithLabel4($object)
    {
        $newObject = $object->withLabel4("TESTSTRING");
        $this->assertEquals($object->getTitle(), "TESTOBJECT");
        $this->assertEquals($object->getDisplayedTitle(), "TESTTITLE");
        $this->assertEquals($object->getInfo(), "");
        $this->assertEquals($object->getLabel1(), "");
        $this->assertEquals($object->getLabel2(), "");
        $this->assertEquals($object->getLabel3(), "");
        $this->assertEquals($object->getLabel4(), "");
        $this->assertEquals($object->getLabel5(), "");
        $this->assertEquals($object->getIsLocked(), false);
        $this->assertEquals($object->getEnableComment(), false);
        $this->assertEquals($object->getOnlyTextualFeedback(), false);
        $this->assertEquals($object->getOrdernumber(), 0);
        $this->assertEquals($object->getIsUsed(), false);

        $this->assertEquals($newObject->getTitle(), "TESTOBJECT");
        $this->assertEquals($newObject->getDisplayedTitle(), "TESTTITLE");
        $this->assertEquals($newObject->getInfo(), "");
        $this->assertEquals($newObject->getLabel1(), "");
        $this->assertEquals($newObject->getLabel2(), "");
        $this->assertEquals($newObject->getLabel3(), "");
        $this->assertEquals($newObject->getLabel4(), "TESTSTRING");
        $this->assertEquals($newObject->getLabel5(), "");
        $this->assertEquals($newObject->getIsLocked(), false);
        $this->assertEquals($newObject->getEnableComment(), false);
        $this->assertEquals($newObject->getOnlyTextualFeedback(), false);
        $this->assertEquals($newObject->getOrdernumber(), 0);
        $this->assertEquals($newObject->getIsUsed(), false);
        $this->assertNotSame($newObject, $object);
    }

    /**
     * @depends testCreate
     */
    public function testWithLabel5($object)
    {
        $newObject = $object->withLabel5("TESTSTRING");
        $this->assertEquals($object->getTitle(), "TESTOBJECT");
        $this->assertEquals($object->getDisplayedTitle(), "TESTTITLE");
        $this->assertEquals($object->getInfo(), "");
        $this->assertEquals($object->getLabel1(), "");
        $this->assertEquals($object->getLabel2(), "");
        $this->assertEquals($object->getLabel3(), "");
        $this->assertEquals($object->getLabel4(), "");
        $this->assertEquals($object->getLabel5(), "");
        $this->assertEquals($object->getIsLocked(), false);
        $this->assertEquals($object->getEnableComment(), false);
        $this->assertEquals($object->getOnlyTextualFeedback(), false);
        $this->assertEquals($object->getOrdernumber(), 0);
        $this->assertEquals($object->getIsUsed(), false);

        $this->assertEquals($newObject->getTitle(), "TESTOBJECT");
        $this->assertEquals($newObject->getDisplayedTitle(), "TESTTITLE");
        $this->assertEquals($newObject->getInfo(), "");
        $this->assertEquals($newObject->getLabel1(), "");
        $this->assertEquals($newObject->getLabel2(), "");
        $this->assertEquals($newObject->getLabel3(), "");
        $this->assertEquals($newObject->getLabel4(), "");
        $this->assertEquals($newObject->getLabel5(), "TESTSTRING");
        $this->assertEquals($newObject->getIsLocked(), false);
        $this->assertEquals($newObject->getEnableComment(), false);
        $this->assertEquals($newObject->getOnlyTextualFeedback(), false);
        $this->assertEquals($newObject->getOrdernumber(), 0);
        $this->assertEquals($newObject->getIsUsed(), false);
        $this->assertNotSame($newObject, $object);
    }

    /**
     * @depends testCreate
     */
    public function testWithIsLocked($object)
    {
        $newObject = $object->withIsLocked(true);
        $this->assertEquals($object->getTitle(), "TESTOBJECT");
        $this->assertEquals($object->getDisplayedTitle(), "TESTTITLE");
        $this->assertEquals($object->getInfo(), "");
        $this->assertEquals($object->getLabel1(), "");
        $this->assertEquals($object->getLabel2(), "");
        $this->assertEquals($object->getLabel3(), "");
        $this->assertEquals($object->getLabel4(), "");
        $this->assertEquals($object->getLabel5(), "");
        $this->assertEquals($object->getIsLocked(), false);
        $this->assertEquals($object->getEnableComment(), false);
        $this->assertEquals($object->getOnlyTextualFeedback(), false);
        $this->assertEquals($object->getOrdernumber(), 0);
        $this->assertEquals($object->getIsUsed(), false);

        $this->assertEquals($newObject->getTitle(), "TESTOBJECT");
        $this->assertEquals($newObject->getDisplayedTitle(), "TESTTITLE");
        $this->assertEquals($newObject->getInfo(), "");
        $this->assertEquals($newObject->getLabel1(), "");
        $this->assertEquals($newObject->getLabel2(), "");
        $this->assertEquals($newObject->getLabel3(), "");
        $this->assertEquals($newObject->getLabel4(), "");
        $this->assertEquals($newObject->getLabel5(), "");
        $this->assertEquals($newObject->getIsLocked(), true);
        $this->assertEquals($newObject->getEnableComment(), false);
        $this->assertEquals($newObject->getOnlyTextualFeedback(), false);
        $this->assertEquals($newObject->getOrdernumber(), 0);
        $this->assertEquals($newObject->getIsUsed(), false);
        $this->assertNotSame($newObject, $object);
    }

    /**
     * @depends testCreate
     */
    public function testWithEnableComment($object)
    {
        $newObject = $object->withEnableComment(true);
        $this->assertEquals($object->getTitle(), "TESTOBJECT");
        $this->assertEquals($object->getDisplayedTitle(), "TESTTITLE");
        $this->assertEquals($object->getInfo(), "");
        $this->assertEquals($object->getLabel1(), "");
        $this->assertEquals($object->getLabel2(), "");
        $this->assertEquals($object->getLabel3(), "");
        $this->assertEquals($object->getLabel4(), "");
        $this->assertEquals($object->getLabel5(), "");
        $this->assertEquals($object->getIsLocked(), false);
        $this->assertEquals($object->getEnableComment(), false);
        $this->assertEquals($object->getOnlyTextualFeedback(), false);
        $this->assertEquals($object->getOrdernumber(), 0);
        $this->assertEquals($object->getIsUsed(), false);

        $this->assertEquals($newObject->getTitle(), "TESTOBJECT");
        $this->assertEquals($newObject->getDisplayedTitle(), "TESTTITLE");
        $this->assertEquals($newObject->getInfo(), "");
        $this->assertEquals($newObject->getLabel1(), "");
        $this->assertEquals($newObject->getLabel2(), "");
        $this->assertEquals($newObject->getLabel3(), "");
        $this->assertEquals($newObject->getLabel4(), "");
        $this->assertEquals($newObject->getLabel5(), "");
        $this->assertEquals($newObject->getIsLocked(), false);
        $this->assertEquals($newObject->getEnableComment(), true);
        $this->assertEquals($newObject->getOnlyTextualFeedback(), false);
        $this->assertEquals($newObject->getOrdernumber(), 0);
        $this->assertEquals($newObject->getIsUsed(), false);
        $this->assertNotSame($newObject, $object);
    }

    /**
     * @depends testCreate
     */
    public function testWithOnlyTextualFeedback($object)
    {
        $newObject = $object->withOnlyTextualFeedback(true);
        $this->assertEquals($object->getTitle(), "TESTOBJECT");
        $this->assertEquals($object->getDisplayedTitle(), "TESTTITLE");
        $this->assertEquals($object->getInfo(), "");
        $this->assertEquals($object->getLabel1(), "");
        $this->assertEquals($object->getLabel2(), "");
        $this->assertEquals($object->getLabel3(), "");
        $this->assertEquals($object->getLabel4(), "");
        $this->assertEquals($object->getLabel5(), "");
        $this->assertEquals($object->getIsLocked(), false);
        $this->assertEquals($object->getEnableComment(), false);
        $this->assertEquals($object->getOnlyTextualFeedback(), false);
        $this->assertEquals($object->getOrdernumber(), 0);
        $this->assertEquals($object->getIsUsed(), false);

        $this->assertEquals($newObject->getTitle(), "TESTOBJECT");
        $this->assertEquals($newObject->getDisplayedTitle(), "TESTTITLE");
        $this->assertEquals($newObject->getInfo(), "");
        $this->assertEquals($newObject->getLabel1(), "");
        $this->assertEquals($newObject->getLabel2(), "");
        $this->assertEquals($newObject->getLabel3(), "");
        $this->assertEquals($newObject->getLabel4(), "");
        $this->assertEquals($newObject->getLabel5(), "");
        $this->assertEquals($newObject->getIsLocked(), false);
        $this->assertEquals($newObject->getEnableComment(), false);
        $this->assertEquals($newObject->getOnlyTextualFeedback(), true);
        $this->assertEquals($newObject->getOrdernumber(), 0);
        $this->assertEquals($newObject->getIsUsed(), false);
        $this->assertNotSame($newObject, $object);
    }

    /**
     * @depends testCreate
     */
    public function testWithOrdernumber($object)
    {
        $newObject = $object->withOrdernumber(22);
        $this->assertEquals($object->getTitle(), "TESTOBJECT");
        $this->assertEquals($object->getDisplayedTitle(), "TESTTITLE");
        $this->assertEquals($object->getInfo(), "");
        $this->assertEquals($object->getLabel1(), "");
        $this->assertEquals($object->getLabel2(), "");
        $this->assertEquals($object->getLabel3(), "");
        $this->assertEquals($object->getLabel4(), "");
        $this->assertEquals($object->getLabel5(), "");
        $this->assertEquals($object->getIsLocked(), false);
        $this->assertEquals($object->getEnableComment(), false);
        $this->assertEquals($object->getOnlyTextualFeedback(), false);
        $this->assertEquals($object->getOrdernumber(), 0);
        $this->assertEquals($object->getIsUsed(), false);


        $this->assertEquals($newObject->getTitle(), "TESTOBJECT");
        $this->assertEquals($newObject->getDisplayedTitle(), "TESTTITLE");
        $this->assertEquals($newObject->getInfo(), "");
        $this->assertEquals($newObject->getLabel1(), "");
        $this->assertEquals($newObject->getLabel2(), "");
        $this->assertEquals($newObject->getLabel3(), "");
        $this->assertEquals($newObject->getLabel4(), "");
        $this->assertEquals($newObject->getLabel5(), "");
        $this->assertEquals($newObject->getIsLocked(), false);
        $this->assertEquals($newObject->getEnableComment(), false);
        $this->assertEquals($newObject->getOnlyTextualFeedback(), false);
        $this->assertEquals($newObject->getOrdernumber(), 22);
        $this->assertEquals($newObject->getIsUsed(), false);
        $this->assertNotSame($newObject, $object);
    }

    /**
     * @depends testCreate
     */
    public function testWithIsUsed($object)
    {
        $newObject = $object->withIsUsed(true);
        $this->assertEquals($object->getTitle(), "TESTOBJECT");
        $this->assertEquals($object->getDisplayedTitle(), "TESTTITLE");
        $this->assertEquals($object->getInfo(), "");
        $this->assertEquals($object->getLabel1(), "");
        $this->assertEquals($object->getLabel2(), "");
        $this->assertEquals($object->getLabel3(), "");
        $this->assertEquals($object->getLabel4(), "");
        $this->assertEquals($object->getLabel5(), "");
        $this->assertEquals($object->getIsLocked(), false);
        $this->assertEquals($object->getEnableComment(), false);
        $this->assertEquals($object->getOnlyTextualFeedback(), false);
        $this->assertEquals($object->getOrdernumber(), 0);
        $this->assertEquals($object->getIsUsed(), false);


        $this->assertEquals($newObject->getTitle(), "TESTOBJECT");
        $this->assertEquals($newObject->getDisplayedTitle(), "TESTTITLE");
        $this->assertEquals($newObject->getInfo(), "");
        $this->assertEquals($newObject->getLabel1(), "");
        $this->assertEquals($newObject->getLabel2(), "");
        $this->assertEquals($newObject->getLabel3(), "");
        $this->assertEquals($newObject->getLabel4(), "");
        $this->assertEquals($newObject->getLabel5(), "");
        $this->assertEquals($newObject->getIsLocked(), false);
        $this->assertEquals($newObject->getEnableComment(), false);
        $this->assertEquals($newObject->getOnlyTextualFeedback(), false);
        $this->assertEquals($newObject->getOrdernumber(), 0);
        $this->assertEquals($newObject->getIsUsed(), true);
        $this->assertNotSame($newObject, $object);
    }
}
