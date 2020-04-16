<?php
namespace CaT\Plugins\ScaledFeedback\Config\Sets;

use PHPUnit\Framework\TestCase;

/**
 * Class SetTest
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class SetTest extends TestCase
{
    public function setUp() : void
    {
    }

    public function testCreate()
    {
        $object = new Set(33, "TESTOBJECT", "", "EXTROTEXT", "REPEATTEXT");

        $this->assertEquals($object->getSetId(), 33);
        $this->assertEquals($object->getTitle(), "TESTOBJECT");
        $this->assertEquals($object->getIntrotext(), "");
        $this->assertEquals($object->getExtrotext(), "EXTROTEXT");
        $this->assertEquals($object->getRepeattext(), "REPEATTEXT");
        $this->assertEquals($object->getIsLocked(), false);
        $this->assertEquals($object->getMinSubmissions(), 6);

        return $object;
    }

    /**
     * @depends testCreate
     */
    public function testWithIntrotext($object)
    {
        $newobject = $object->withIntrotext("TESTSTRING");

        $this->assertEquals($object->getSetId(), 33);
        $this->assertEquals($object->getTitle(), "TESTOBJECT");
        $this->assertEquals($object->getIntrotext(), "");
        $this->assertEquals($object->getExtrotext(), "EXTROTEXT");
        $this->assertEquals($object->getRepeattext(), "REPEATTEXT");
        $this->assertEquals($object->getIsLocked(), false);
        $this->assertEquals($object->getMinSubmissions(), 6);

        $this->assertEquals($newobject->getSetId(), 33);
        $this->assertEquals($newobject->getTitle(), "TESTOBJECT");
        $this->assertEquals($newobject->getIntrotext(), "TESTSTRING");
        $this->assertEquals($newobject->getExtrotext(), "EXTROTEXT");
        $this->assertEquals($newobject->getRepeattext(), "REPEATTEXT");
        $this->assertEquals($newobject->getIsLocked(), false);
        $this->assertEquals($newobject->getMinSubmissions(), 6);
    }

    /**
     * @depends testCreate
     */
    public function testWithExtrotext($object)
    {
        $newobject = $object->withExtrotext("TESTSTRING");

        $this->assertEquals($object->getSetId(), 33);
        $this->assertEquals($object->getTitle(), "TESTOBJECT");
        $this->assertEquals($object->getIntrotext(), "");
        $this->assertEquals($object->getExtrotext(), "EXTROTEXT");
        $this->assertEquals($object->getRepeattext(), "REPEATTEXT");
        $this->assertEquals($object->getIsLocked(), false);
        $this->assertEquals($object->getMinSubmissions(), 6);

        $this->assertEquals($newobject->getSetId(), 33);
        $this->assertEquals($newobject->getTitle(), "TESTOBJECT");
        $this->assertEquals($newobject->getIntrotext(), "");
        $this->assertEquals($newobject->getExtrotext(), "TESTSTRING");
        $this->assertEquals($newobject->getRepeattext(), "REPEATTEXT");
        $this->assertEquals($newobject->getIsLocked(), false);
        $this->assertEquals($newobject->getMinSubmissions(), 6);
    }

    /**
     * @depends testCreate
     */
    public function testWithRepeattext($object)
    {
        $newobject = $object->withRepeattext("TESTSTRING");

        $this->assertEquals($object->getSetId(), 33);
        $this->assertEquals($object->getTitle(), "TESTOBJECT");
        $this->assertEquals($object->getIntrotext(), "");
        $this->assertEquals($object->getExtrotext(), "EXTROTEXT");
        $this->assertEquals($object->getRepeattext(), "REPEATTEXT");
        $this->assertEquals($object->getIsLocked(), "");
        $this->assertEquals($object->getMinSubmissions(), 6);

        $this->assertEquals($newobject->getSetId(), 33);
        $this->assertEquals($newobject->getTitle(), "TESTOBJECT");
        $this->assertEquals($newobject->getIntrotext(), "");
        $this->assertEquals($newobject->getExtrotext(), "EXTROTEXT");
        $this->assertEquals($newobject->getRepeattext(), "TESTSTRING");
        $this->assertEquals($newobject->getIsLocked(), false);
        $this->assertEquals($newobject->getMinSubmissions(), 6);
    }

    /**
     * @depends testCreate
     */
    public function testWithIsLocked($object)
    {
        $newobject = $object->withIsLocked(true);

        $this->assertEquals($object->getSetId(), 33);
        $this->assertEquals($object->getTitle(), "TESTOBJECT");
        $this->assertEquals($object->getIntrotext(), "");
        $this->assertEquals($object->getExtrotext(), "EXTROTEXT");
        $this->assertEquals($object->getRepeattext(), "REPEATTEXT");
        $this->assertEquals($object->getIsLocked(), false);
        $this->assertEquals($object->getMinSubmissions(), 6);

        $this->assertEquals($newobject->getSetId(), 33);
        $this->assertEquals($newobject->getTitle(), "TESTOBJECT");
        $this->assertEquals($newobject->getIntrotext(), "");
        $this->assertEquals($newobject->getExtrotext(), "EXTROTEXT");
        $this->assertEquals($newobject->getRepeattext(), "REPEATTEXT");
        $this->assertEquals($newobject->getIsLocked(), true);
        $this->assertEquals($newobject->getMinSubmissions(), 6);
    }

    /**
     * @depends testCreate
     */
    public function testWithMinSubmissions($object)
    {
        $newobject = $object->withMinSubmissions(10);

        $this->assertEquals($object->getSetId(), 33);
        $this->assertEquals($object->getTitle(), "TESTOBJECT");
        $this->assertEquals($object->getIntrotext(), "");
        $this->assertEquals($object->getExtrotext(), "EXTROTEXT");
        $this->assertEquals($object->getRepeattext(), "REPEATTEXT");
        $this->assertEquals($object->getIsLocked(), false);
        $this->assertEquals($object->getMinSubmissions(), 6);

        $this->assertEquals($newobject->getSetId(), 33);
        $this->assertEquals($newobject->getTitle(), "TESTOBJECT");
        $this->assertEquals($newobject->getIntrotext(), "");
        $this->assertEquals($newobject->getExtrotext(), "EXTROTEXT");
        $this->assertEquals($newobject->getRepeattext(), "REPEATTEXT");
        $this->assertEquals($newobject->getIsLocked(), false);
        $this->assertEquals($newobject->getMinSubmissions(), 10);
    }
}
