<?php

namespace CaT\Plugins\MaterialList\RPC\Procedures\Course;

use PHPUnit\Framework\TestCase;
use \CaT\Plugins\MaterialList\RPC\FunctionResult;

interface ilObjCourse
{
    /**
     * @return string
     */
    public function getTitle();
}

/**
 * Sample for PHP Unit tests
 */
class GetTitleTest extends TestCase
{
    public function setUp() : void
    {
        $crs = $this->getMockBuilder('ilObjCourse')
                                 ->disableOriginalConstructor()
                                 ->getMock();

        $crs->method("getTitle")
                   ->will($this->returnValue("Course"));

        $this->prc_get_title = new ilGetTitle($crs, function ($s) {
            return $s;
        });
    }

    public function test_getTitle()
    {
        $title = $this->prc_get_title->run();
        $this->assertInstanceOf(FunctionResult::class, $title);
        $this->assertEquals("Course", $title->getValue());
    }
}
