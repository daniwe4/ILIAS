<?php

namespace CaT\Plugins\Webinar\VC\CSN;

use PHPUnit\Framework\TestCase;

/**
 * Test for a CSN Participant
 */
class ParticipantTest extends TestCase
{
    public function test_create()
    {
        $participant = new Participant(1, 1, "Karl", "Karl@mail.com", "0123456789", "", 25, "", null);

        $this->assertEquals(1, $participant->getId());
        $this->assertEquals(1, $participant->getObjId());
        $this->assertEquals("Karl", $participant->getName());
        $this->assertEquals("Karl@mail.com", $participant->getEmail());
        $this->assertEquals("", $participant->getCompany());
        $this->assertEquals(25, $participant->getMinutes());
        $this->assertEquals("", $participant->getUserName());
        $this->isNull($participant->getUserId());
        $this->assertTrue($participant->isKnownUser());

        $participant = new Participant(1, 1, "Karl", "Karl@mail.com", "0123456789", "", 25, "KaterKarlo", 1234);

        $this->assertEquals(1, $participant->getId());
        $this->assertEquals(1, $participant->getObjId());
        $this->assertEquals("Karl", $participant->getName());
        $this->assertEquals("Karl@mail.com", $participant->getEmail());
        $this->assertEquals("", $participant->getCompany());
        $this->assertEquals(25, $participant->getMinutes());
        $this->assertEquals(1234, $participant->getUserId());
        $this->assertEquals("KaterKarlo", $participant->getUserName());
        $this->assertTrue($participant->isKnownUser());
    }
}
