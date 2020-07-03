<?php

namespace CaT\Plugins\OnlineSeminar\Settings;

use PHPUnit\Framework\TestCase;

/**
 * Test for unmutable online seminar settings
 */
class OnlineSeminarTest extends TestCase
{
    public function test_create()
    {
        $online_seminar = new OnlineSeminar(1, "vc");

        $this->assertEquals(1, $online_seminar->getObjId());
        $this->assertEquals("vc", $online_seminar->getVCType());
        $this->assertNull($online_seminar->getBeginning());
        $this->assertNull($online_seminar->getEnding());
        $this->assertNull($online_seminar->getAdmission());
        $this->assertNull($online_seminar->getUrl());
        $this->assertFalse($online_seminar->getOnline());
        $this->assertEquals(0, $online_seminar->getLPMode());
        $this->assertFalse($online_seminar->isFinished());

        return $online_seminar;
    }

    /**
     * @depends test_create
     */
    public function test_withBeginning($online_seminar)
    {
        $today = $this
            ->getMockBuilder("ilDateTime")
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $new_online_seminar = $online_seminar->withBeginning($today);

        $this->assertEquals(1, $online_seminar->getObjId());
        $this->assertEquals("vc", $online_seminar->getVCType());
        $this->assertNull($online_seminar->getBeginning());
        $this->assertNull($online_seminar->getEnding());
        $this->assertNull($online_seminar->getAdmission());
        $this->assertNull($online_seminar->getUrl());
        $this->assertFalse($online_seminar->getOnline());
        $this->assertEquals(0, $online_seminar->getLPMode());
        $this->assertFalse($online_seminar->isFinished());

        $this->assertEquals(1, $new_online_seminar->getObjId());
        $this->assertEquals("vc", $new_online_seminar->getVCType());
        $this->assertSame($today, $new_online_seminar->getBeginning());
        $this->assertNull($new_online_seminar->getEnding());
        $this->assertNull($new_online_seminar->getAdmission());
        $this->assertNull($new_online_seminar->getUrl());
        $this->assertFalse($new_online_seminar->getOnline());
        $this->assertEquals(0, $new_online_seminar->getLPMode());
        $this->assertFalse($new_online_seminar->isFinished());

        $this->assertNotSame($new_online_seminar, $online_seminar);

        return $online_seminar;
    }

    /**
     * @depends test_withBeginning
     */
    public function test_withEnding($online_seminar)
    {
        $today = $this
            ->getMockBuilder("ilDateTime")
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $new_online_seminar = $online_seminar->withEnding($today);

        $this->assertEquals(1, $online_seminar->getObjId());
        $this->assertEquals("vc", $online_seminar->getVCType());
        $this->assertNull($online_seminar->getBeginning());
        $this->assertNull($online_seminar->getEnding());
        $this->assertNull($online_seminar->getAdmission());
        $this->assertNull($online_seminar->getUrl());
        $this->assertFalse($online_seminar->getOnline());
        $this->assertEquals(0, $online_seminar->getLPMode());
        $this->assertFalse($online_seminar->isFinished());

        $this->assertEquals(1, $new_online_seminar->getObjId());
        $this->assertEquals("vc", $new_online_seminar->getVCType());
        $this->assertNull($new_online_seminar->getBeginning());
        $this->assertSame($today, $new_online_seminar->getEnding());
        $this->assertNull($new_online_seminar->getAdmission());
        $this->assertNull($new_online_seminar->getUrl());
        $this->assertFalse($new_online_seminar->getOnline());
        $this->assertEquals(0, $new_online_seminar->getLPMode());
        $this->assertFalse($new_online_seminar->isFinished());

        $this->assertNotSame($new_online_seminar, $online_seminar);

        return $online_seminar;
    }

    /**
     * @depends test_withEnding
     */
    public function test_withAdmission($online_seminar)
    {
        $new_online_seminar = $online_seminar->withAdmission("self");

        $this->assertEquals(1, $online_seminar->getObjId());
        $this->assertEquals("vc", $online_seminar->getVCType());
        $this->assertNull($online_seminar->getBeginning());
        $this->assertNull($online_seminar->getEnding());
        $this->assertNull($online_seminar->getAdmission());
        $this->assertNull($online_seminar->getUrl());
        $this->assertFalse($online_seminar->getOnline());
        $this->assertEquals(0, $online_seminar->getLPMode());
        $this->assertFalse($online_seminar->isFinished());

        $this->assertEquals(1, $new_online_seminar->getObjId());
        $this->assertEquals("vc", $new_online_seminar->getVCType());
        $this->assertNull($new_online_seminar->getBeginning());
        $this->assertNull($new_online_seminar->getEnding());
        $this->assertEquals("self", $new_online_seminar->getAdmission());
        $this->assertNull($new_online_seminar->getUrl());
        $this->assertFalse($new_online_seminar->getOnline());
        $this->assertEquals(0, $new_online_seminar->getLPMode());
        $this->assertFalse($new_online_seminar->isFinished());

        $this->assertNotSame($new_online_seminar, $online_seminar);

        return $online_seminar;
    }

    /**
     * @depends test_withAdmission
     */
    public function test_withUrl($online_seminar)
    {
        $new_online_seminar = $online_seminar->withUrl("http://url.de");

        $this->assertEquals(1, $online_seminar->getObjId());
        $this->assertEquals("vc", $online_seminar->getVCType());
        $this->assertNull($online_seminar->getBeginning());
        $this->assertNull($online_seminar->getEnding());
        $this->assertNull($online_seminar->getAdmission());
        $this->assertNull($online_seminar->getUrl());
        $this->assertFalse($online_seminar->getOnline());
        $this->assertEquals(0, $online_seminar->getLPMode());
        $this->assertFalse($online_seminar->isFinished());

        $this->assertEquals(1, $new_online_seminar->getObjId());
        $this->assertEquals("vc", $new_online_seminar->getVCType());
        $this->assertNull($new_online_seminar->getBeginning());
        $this->assertNull($new_online_seminar->getEnding());
        $this->assertNull($new_online_seminar->getAdmission());
        $this->assertEquals("http://url.de", $new_online_seminar->getUrl());
        $this->assertFalse($new_online_seminar->getOnline());
        $this->assertEquals(0, $new_online_seminar->getLPMode());
        $this->assertFalse($new_online_seminar->isFinished());

        $this->assertNotSame($new_online_seminar, $online_seminar);

        return $online_seminar;
    }

    /**
     * @depends test_withUrl
     */
    public function test_withOnline($online_seminar)
    {
        $new_online_seminar = $online_seminar->withOnline(true);

        $this->assertEquals(1, $online_seminar->getObjId());
        $this->assertEquals("vc", $online_seminar->getVCType());
        $this->assertNull($online_seminar->getBeginning());
        $this->assertNull($online_seminar->getEnding());
        $this->assertNull($online_seminar->getAdmission());
        $this->assertNull($online_seminar->getUrl());
        $this->assertFalse($online_seminar->getOnline());
        $this->assertEquals(0, $online_seminar->getLPMode());
        $this->assertFalse($online_seminar->isFinished());

        $this->assertEquals(1, $new_online_seminar->getObjId());
        $this->assertEquals("vc", $new_online_seminar->getVCType());
        $this->assertNull($new_online_seminar->getBeginning());
        $this->assertNull($new_online_seminar->getEnding());
        $this->assertNull($new_online_seminar->getAdmission());
        $this->assertNull($new_online_seminar->getUrl());
        $this->assertTrue($new_online_seminar->getOnline());
        $this->assertEquals(0, $new_online_seminar->getLPMode());
        $this->assertFalse($new_online_seminar->isFinished());

        $this->assertNotSame($new_online_seminar, $online_seminar);

        return $online_seminar;
    }

    /**
     * @depends test_withOnline
     */
    public function test_withLPMode($online_seminar)
    {
        $new_online_seminar = $online_seminar->withLPMode(1);

        $this->assertEquals(1, $online_seminar->getObjId());
        $this->assertEquals("vc", $online_seminar->getVCType());
        $this->assertNull($online_seminar->getBeginning());
        $this->assertNull($online_seminar->getEnding());
        $this->assertNull($online_seminar->getAdmission());
        $this->assertNull($online_seminar->getUrl());
        $this->assertFalse($online_seminar->getOnline());
        $this->assertEquals(0, $online_seminar->getLPMode());
        $this->assertFalse($online_seminar->isFinished());

        $this->assertEquals(1, $new_online_seminar->getObjId());
        $this->assertEquals("vc", $new_online_seminar->getVCType());
        $this->assertNull($new_online_seminar->getBeginning());
        $this->assertNull($new_online_seminar->getEnding());
        $this->assertNull($new_online_seminar->getAdmission());
        $this->assertNull($new_online_seminar->getUrl());
        $this->assertFalse($new_online_seminar->getOnline());
        $this->assertEquals(1, $new_online_seminar->getLPMode());
        $this->assertFalse($new_online_seminar->isFinished());

        $this->assertNotSame($new_online_seminar, $online_seminar);

        return $online_seminar;
    }

    /**
     * @depends test_withLPMode
     */
    public function test_withFinished($online_seminar)
    {
        $new_online_seminar = $online_seminar->withFinished(true);

        $this->assertEquals(1, $online_seminar->getObjId());
        $this->assertEquals("vc", $online_seminar->getVCType());
        $this->assertNull($online_seminar->getBeginning());
        $this->assertNull($online_seminar->getEnding());
        $this->assertNull($online_seminar->getAdmission());
        $this->assertNull($online_seminar->getUrl());
        $this->assertFalse($online_seminar->getOnline());
        $this->assertEquals(0, $online_seminar->getLPMode());
        $this->assertFalse($online_seminar->isFinished());

        $this->assertEquals(1, $new_online_seminar->getObjId());
        $this->assertEquals("vc", $new_online_seminar->getVCType());
        $this->assertNull($new_online_seminar->getBeginning());
        $this->assertNull($new_online_seminar->getEnding());
        $this->assertNull($new_online_seminar->getAdmission());
        $this->assertNull($new_online_seminar->getUrl());
        $this->assertFalse($new_online_seminar->getOnline());
        $this->assertEquals(0, $new_online_seminar->getLPMode());
        $this->assertTrue($new_online_seminar->isFinished());

        $this->assertNotSame($new_online_seminar, $online_seminar);
    }
}
