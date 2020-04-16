<?php

namespace CaT\Plugins\Webinar\Settings;

use PHPUnit\Framework\TestCase;

/**
 * Test for unmutable webinar settings
 */
class WebinarTest extends TestCase
{
    public function test_create()
    {
        $webinar = new Webinar(1, "vc");

        $this->assertEquals(1, $webinar->getObjId());
        $this->assertEquals("vc", $webinar->getVCType());
        $this->assertNull($webinar->getBeginning());
        $this->assertNull($webinar->getEnding());
        $this->assertNull($webinar->getAdmission());
        $this->assertNull($webinar->getUrl());
        $this->assertFalse($webinar->getOnline());
        $this->assertEquals(0, $webinar->getLPMode());
        $this->assertFalse($webinar->isFinished());

        return $webinar;
    }

    /**
     * @depends test_create
     */
    public function test_withBeginning($webinar)
    {
        $today = $this
            ->getMockBuilder("ilDateTime")
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $new_webinar = $webinar->withBeginning($today);

        $this->assertEquals(1, $webinar->getObjId());
        $this->assertEquals("vc", $webinar->getVCType());
        $this->assertNull($webinar->getBeginning());
        $this->assertNull($webinar->getEnding());
        $this->assertNull($webinar->getAdmission());
        $this->assertNull($webinar->getUrl());
        $this->assertFalse($webinar->getOnline());
        $this->assertEquals(0, $webinar->getLPMode());
        $this->assertFalse($webinar->isFinished());

        $this->assertEquals(1, $new_webinar->getObjId());
        $this->assertEquals("vc", $new_webinar->getVCType());
        $this->assertSame($today, $new_webinar->getBeginning());
        $this->assertNull($new_webinar->getEnding());
        $this->assertNull($new_webinar->getAdmission());
        $this->assertNull($new_webinar->getUrl());
        $this->assertFalse($new_webinar->getOnline());
        $this->assertEquals(0, $new_webinar->getLPMode());
        $this->assertFalse($new_webinar->isFinished());

        $this->assertNotSame($new_webinar, $webinar);

        return $webinar;
    }

    /**
     * @depends test_withBeginning
     */
    public function test_withEnding($webinar)
    {
        $today = $this
            ->getMockBuilder("ilDateTime")
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $new_webinar = $webinar->withEnding($today);

        $this->assertEquals(1, $webinar->getObjId());
        $this->assertEquals("vc", $webinar->getVCType());
        $this->assertNull($webinar->getBeginning());
        $this->assertNull($webinar->getEnding());
        $this->assertNull($webinar->getAdmission());
        $this->assertNull($webinar->getUrl());
        $this->assertFalse($webinar->getOnline());
        $this->assertEquals(0, $webinar->getLPMode());
        $this->assertFalse($webinar->isFinished());

        $this->assertEquals(1, $new_webinar->getObjId());
        $this->assertEquals("vc", $new_webinar->getVCType());
        $this->assertNull($new_webinar->getBeginning());
        $this->assertSame($today, $new_webinar->getEnding());
        $this->assertNull($new_webinar->getAdmission());
        $this->assertNull($new_webinar->getUrl());
        $this->assertFalse($new_webinar->getOnline());
        $this->assertEquals(0, $new_webinar->getLPMode());
        $this->assertFalse($new_webinar->isFinished());

        $this->assertNotSame($new_webinar, $webinar);

        return $webinar;
    }

    /**
     * @depends test_withEnding
     */
    public function test_withAdmission($webinar)
    {
        $new_webinar = $webinar->withAdmission("self");

        $this->assertEquals(1, $webinar->getObjId());
        $this->assertEquals("vc", $webinar->getVCType());
        $this->assertNull($webinar->getBeginning());
        $this->assertNull($webinar->getEnding());
        $this->assertNull($webinar->getAdmission());
        $this->assertNull($webinar->getUrl());
        $this->assertFalse($webinar->getOnline());
        $this->assertEquals(0, $webinar->getLPMode());
        $this->assertFalse($webinar->isFinished());

        $this->assertEquals(1, $new_webinar->getObjId());
        $this->assertEquals("vc", $new_webinar->getVCType());
        $this->assertNull($new_webinar->getBeginning());
        $this->assertNull($new_webinar->getEnding());
        $this->assertEquals("self", $new_webinar->getAdmission());
        $this->assertNull($new_webinar->getUrl());
        $this->assertFalse($new_webinar->getOnline());
        $this->assertEquals(0, $new_webinar->getLPMode());
        $this->assertFalse($new_webinar->isFinished());

        $this->assertNotSame($new_webinar, $webinar);

        return $webinar;
    }

    /**
     * @depends test_withAdmission
     */
    public function test_withUrl($webinar)
    {
        $new_webinar = $webinar->withUrl("http://url.de");

        $this->assertEquals(1, $webinar->getObjId());
        $this->assertEquals("vc", $webinar->getVCType());
        $this->assertNull($webinar->getBeginning());
        $this->assertNull($webinar->getEnding());
        $this->assertNull($webinar->getAdmission());
        $this->assertNull($webinar->getUrl());
        $this->assertFalse($webinar->getOnline());
        $this->assertEquals(0, $webinar->getLPMode());
        $this->assertFalse($webinar->isFinished());

        $this->assertEquals(1, $new_webinar->getObjId());
        $this->assertEquals("vc", $new_webinar->getVCType());
        $this->assertNull($new_webinar->getBeginning());
        $this->assertNull($new_webinar->getEnding());
        $this->assertNull($new_webinar->getAdmission());
        $this->assertEquals("http://url.de", $new_webinar->getUrl());
        $this->assertFalse($new_webinar->getOnline());
        $this->assertEquals(0, $new_webinar->getLPMode());
        $this->assertFalse($new_webinar->isFinished());

        $this->assertNotSame($new_webinar, $webinar);

        return $webinar;
    }

    /**
     * @depends test_withUrl
     */
    public function test_withOnline($webinar)
    {
        $new_webinar = $webinar->withOnline(true);

        $this->assertEquals(1, $webinar->getObjId());
        $this->assertEquals("vc", $webinar->getVCType());
        $this->assertNull($webinar->getBeginning());
        $this->assertNull($webinar->getEnding());
        $this->assertNull($webinar->getAdmission());
        $this->assertNull($webinar->getUrl());
        $this->assertFalse($webinar->getOnline());
        $this->assertEquals(0, $webinar->getLPMode());
        $this->assertFalse($webinar->isFinished());

        $this->assertEquals(1, $new_webinar->getObjId());
        $this->assertEquals("vc", $new_webinar->getVCType());
        $this->assertNull($new_webinar->getBeginning());
        $this->assertNull($new_webinar->getEnding());
        $this->assertNull($new_webinar->getAdmission());
        $this->assertNull($new_webinar->getUrl());
        $this->assertTrue($new_webinar->getOnline());
        $this->assertEquals(0, $new_webinar->getLPMode());
        $this->assertFalse($new_webinar->isFinished());

        $this->assertNotSame($new_webinar, $webinar);

        return $webinar;
    }

    /**
     * @depends test_withOnline
     */
    public function test_withLPMode($webinar)
    {
        $new_webinar = $webinar->withLPMode(1);

        $this->assertEquals(1, $webinar->getObjId());
        $this->assertEquals("vc", $webinar->getVCType());
        $this->assertNull($webinar->getBeginning());
        $this->assertNull($webinar->getEnding());
        $this->assertNull($webinar->getAdmission());
        $this->assertNull($webinar->getUrl());
        $this->assertFalse($webinar->getOnline());
        $this->assertEquals(0, $webinar->getLPMode());
        $this->assertFalse($webinar->isFinished());

        $this->assertEquals(1, $new_webinar->getObjId());
        $this->assertEquals("vc", $new_webinar->getVCType());
        $this->assertNull($new_webinar->getBeginning());
        $this->assertNull($new_webinar->getEnding());
        $this->assertNull($new_webinar->getAdmission());
        $this->assertNull($new_webinar->getUrl());
        $this->assertFalse($new_webinar->getOnline());
        $this->assertEquals(1, $new_webinar->getLPMode());
        $this->assertFalse($new_webinar->isFinished());

        $this->assertNotSame($new_webinar, $webinar);

        return $webinar;
    }

    /**
     * @depends test_withLPMode
     */
    public function test_withFinished($webinar)
    {
        $new_webinar = $webinar->withFinished(true);

        $this->assertEquals(1, $webinar->getObjId());
        $this->assertEquals("vc", $webinar->getVCType());
        $this->assertNull($webinar->getBeginning());
        $this->assertNull($webinar->getEnding());
        $this->assertNull($webinar->getAdmission());
        $this->assertNull($webinar->getUrl());
        $this->assertFalse($webinar->getOnline());
        $this->assertEquals(0, $webinar->getLPMode());
        $this->assertFalse($webinar->isFinished());

        $this->assertEquals(1, $new_webinar->getObjId());
        $this->assertEquals("vc", $new_webinar->getVCType());
        $this->assertNull($new_webinar->getBeginning());
        $this->assertNull($new_webinar->getEnding());
        $this->assertNull($new_webinar->getAdmission());
        $this->assertNull($new_webinar->getUrl());
        $this->assertFalse($new_webinar->getOnline());
        $this->assertEquals(0, $new_webinar->getLPMode());
        $this->assertTrue($new_webinar->isFinished());

        $this->assertNotSame($new_webinar, $webinar);
    }
}
