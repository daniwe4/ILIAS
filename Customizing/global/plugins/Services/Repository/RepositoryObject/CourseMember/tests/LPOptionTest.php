<?php

use PHPUnit\Framework\TestCase;
use CaT\Plugins\CourseMember\LPOptions\LPOption;

/**
 * Sample for PHP Unit tests
 */
class LPOptionTest extends TestCase
{
    public function test_create()
    {
        $lp_option = new LPOption(1, "title", 10, false, true);

        $this->assertEquals(1, $lp_option->getId());
        $this->assertEquals("title", $lp_option->getTitle());
        $this->assertEquals(10, $lp_option->getILIASLP());
        $this->assertTrue($lp_option->getActive() === false);
        $this->assertTrue($lp_option->isStandard());

        return $lp_option;
    }

    /**
     * @depends test_create
     */
    public function test_withTitle($lp_option)
    {
        $new_lp_option = $lp_option->withTitle("new_title");

        $this->assertEquals(1, $lp_option->getId());
        $this->assertEquals("title", $lp_option->getTitle());
        $this->assertEquals(10, $lp_option->getILIASLP());
        $this->assertTrue($lp_option->getActive() === false);
        $this->assertTrue($lp_option->isStandard());

        $this->assertEquals(1, $new_lp_option->getId());
        $this->assertEquals("new_title", $new_lp_option->getTitle());
        $this->assertEquals(10, $new_lp_option->getILIASLP());
        $this->assertTrue($new_lp_option->getActive() === false);
        $this->assertTrue($new_lp_option->isStandard());

        $this->assertNotSame($new_lp_option, $lp_option);

        return $lp_option;
    }

    /**
     * @depends test_withTitle
     */
    public function test_withILIASLP($lp_option)
    {
        $new_lp_option = $lp_option->withILIASLP(20);

        $this->assertEquals(1, $lp_option->getId());
        $this->assertEquals("title", $lp_option->getTitle());
        $this->assertEquals(10, $lp_option->getILIASLP());
        $this->assertTrue($lp_option->getActive() === false);
        $this->assertTrue($lp_option->isStandard());

        $this->assertEquals(1, $new_lp_option->getId());
        $this->assertEquals("title", $new_lp_option->getTitle());
        $this->assertEquals(20, $new_lp_option->getILIASLP());
        $this->assertTrue($new_lp_option->getActive() === false);
        $this->assertTrue($new_lp_option->isStandard());

        $this->assertNotSame($new_lp_option, $lp_option);

        return $lp_option;
    }

    /**
     * @depends test_withILIASLP
     */
    public function test_withActive($lp_option)
    {
        $new_lp_option = $lp_option->withActive(true);

        $this->assertEquals(1, $lp_option->getId());
        $this->assertEquals("title", $lp_option->getTitle());
        $this->assertEquals(10, $lp_option->getILIASLP());
        $this->assertTrue($lp_option->getActive() === false);
        $this->assertTrue($lp_option->isStandard());

        $this->assertEquals(1, $new_lp_option->getId());
        $this->assertEquals("title", $new_lp_option->getTitle());
        $this->assertEquals(10, $new_lp_option->getILIASLP());
        $this->assertTrue($new_lp_option->getActive() === true);
        $this->assertTrue($new_lp_option->isStandard());

        $this->assertNotSame($new_lp_option, $lp_option);
    }

    /**
     * @depends test_withILIASLP
     */
    public function test_withStandard($lp_option)
    {
        $new_lp_option = $lp_option->withStandard(false);

        $this->assertEquals(1, $lp_option->getId());
        $this->assertEquals("title", $lp_option->getTitle());
        $this->assertEquals(10, $lp_option->getILIASLP());
        $this->assertTrue($lp_option->getActive() === false);
        $this->assertTrue($lp_option->isStandard());

        $this->assertEquals(1, $new_lp_option->getId());
        $this->assertEquals("title", $new_lp_option->getTitle());
        $this->assertEquals(10, $new_lp_option->getILIASLP());
        $this->assertTrue($new_lp_option->getActive() === false);
        $this->assertFalse($new_lp_option->isStandard());

        $this->assertNotSame($new_lp_option, $lp_option);
    }
}
