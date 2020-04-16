<?php

use PHPUnit\Framework\TestCase;
use CaT\Plugins\CourseMember\Members\Member;

/**
 * Tests the immutable object member
 *
 * @author Stefan Hecken	<stefan.hecken@concspts-and-training.de>
 * @group needsInstalledILIAS
 */
class CourseMemberTest extends TestCase
{
    public static function setUpBeforeClass() : void
    {
        require_once("Services/Calendar/classes/class.ilDateTime.php");
    }

    public function test_object()
    {
        $dt = new ilDateTime(date("Y-m-d"), IL_CAL_DATE);
        $member = new Member(1, 22, 1, "bestanden", 2, 10, 10, $dt, 6);

        $this->assertEquals(1, $member->getUserId());
        $this->assertEquals(22, $member->getCrsId());
        $this->assertEquals(1, $member->getLPId());
        $this->assertEquals("bestanden", $member->getLPValue());
        $this->assertEquals(2, $member->getILIASLP());
        $this->assertSame($dt, $member->getLastEdited());
        $this->assertEquals(6, $member->getLastEditBy());
    }

    public function withLastEdited()
    {
        $dt = new ilDateTime(date("2017-m-d"), IL_CAL_DATE);
        $dt2 = new ilDateTime(date("2018-m-d"), IL_CAL_DATE);
        $member = new Member(1, 22, 1, "bestanden", 2, 10, 10, $dt, 6);
        $new_member = $member->withLastEdited($dt2);

        $this->assertEquals(1, $member->getUserId());
        $this->assertEquals(22, $member->getCrsId());
        $this->assertEquals(1, $member->getLPId());
        $this->assertEquals("bestanden", $member->getLPValue());
        $this->assertEquals(2, $member->getILIASLP());
        $this->assertEquals(10, $member->getCredits());
        $this->assertEquals(10, $member->getIDDLearningTime());
        $this->assertSame($dt, $member->getLastEdited());
        $this->assertEquals(6, $member->getLastEditBy());

        $this->assertEquals(1, $new_member->getUserId());
        $this->assertEquals(22, $new_member->getCrsId());
        $this->assertEquals(1, $new_member->getLPId());
        $this->assertEquals("bestanden", $new_member->getLPValue());
        $this->assertEquals(2, $new_member->getILIASLP());
        $this->assertSame($dt2, $new_member->getLastEdited());
        $this->assertEquals(6, $new_member->getLastEditBy());

        $this->assertNotSame($member, $new_member);
    }

    public function withLastEditBy()
    {
        $dt = new ilDateTime(date("2017-m-d"), IL_CAL_DATE);
        $member = new Member(1, 22, 1, "bestanden", 2, 10, 10, $dt, 6);
        $new_member = $member->withLastEditBy(12);

        $this->assertEquals(1, $member->getUserId());
        $this->assertEquals(22, $member->getCrsId());
        $this->assertEquals(1, $member->getLPId());
        $this->assertEquals("bestanden", $member->getLPValue());
        $this->assertEquals(2, $member->getILIASLP());
        $this->assertEquals(10, $member->getCredits());
        $this->assertEquals(10, $member->getIDDLearningTime());
        $this->assertSame($dt, $member->getLastEdited());
        $this->assertEquals(6, $member->getLastEditBy());

        $this->assertEquals(1, $new_member->getUserId());
        $this->assertEquals(22, $new_member->getCrsId());
        $this->assertEquals(1, $new_member->getLPId());
        $this->assertEquals("bestanden", $new_member->getLPValue());
        $this->assertEquals(2, $new_member->getILIASLP());
        $this->assertEquals(10, $new_member->getCredits());
        $this->assertEquals(10, $new_member->getIDDLearningTime());
        $this->assertSame($dt, $new_member->getLastEdited());
        $this->assertEquals(12, $new_member->getLastEditBy());

        $this->assertNotSame($member, $new_member);
    }

    public function withCredits()
    {
        $dt = new ilDateTime(date("2017-m-d"), IL_CAL_DATE);
        $member = new Member(1, 22, 1, "bestanden", 2, 10, 10, $dt, 6);
        $new_member = $member->withCredits(12);

        $this->assertEquals(1, $member->getUserId());
        $this->assertEquals(22, $member->getCrsId());
        $this->assertEquals(1, $member->getLPId());
        $this->assertEquals("bestanden", $member->getLPValue());
        $this->assertEquals(2, $member->getILIASLP());
        $this->assertEquals(10, $member->getCredits());
        $this->assertEquals(10, $member->getIDDLearningTime());
        $this->assertSame($dt, $member->getLastEdited());
        $this->assertEquals(6, $member->getLastEditBy());

        $this->assertEquals(1, $new_member->getUserId());
        $this->assertEquals(22, $new_member->getCrsId());
        $this->assertEquals(1, $new_member->getLPId());
        $this->assertEquals("bestanden", $new_member->getLPValue());
        $this->assertEquals(2, $new_member->getILIASLP());
        $this->assertEquals(12, $new_member->getCredits());
        $this->assertEquals(10, $new_member->getIDDLearningTime());
        $this->assertSame($dt, $new_member->getLastEdited());
        $this->assertEquals(12, $new_member->getLastEditBy());

        $this->assertNotSame($member, $new_member);
    }

    public function withIDDLearningTime()
    {
        $dt = new ilDateTime(date("2017-m-d"), IL_CAL_DATE);
        $member = new Member(1, 22, 1, "bestanden", 2, 10, 10, $dt, 6);
        $new_member = $member->withIDDLearningTime(12);

        $this->assertEquals(1, $member->getUserId());
        $this->assertEquals(22, $member->getCrsId());
        $this->assertEquals(1, $member->getLPId());
        $this->assertEquals("bestanden", $member->getLPValue());
        $this->assertEquals(2, $member->getILIASLP());
        $this->assertEquals(10, $member->getCredits());
        $this->assertEquals(10, $member->getIDDLearningTime());
        $this->assertSame($dt, $member->getLastEdited());
        $this->assertEquals(6, $member->getLastEditBy());

        $this->assertEquals(1, $new_member->getUserId());
        $this->assertEquals(22, $new_member->getCrsId());
        $this->assertEquals(1, $new_member->getLPId());
        $this->assertEquals("bestanden", $new_member->getLPValue());
        $this->assertEquals(2, $new_member->getILIASLP());
        $this->assertEquals(10, $new_member->getCredits());
        $this->assertEquals(12, $new_member->getIDDLearningTime());
        $this->assertSame($dt, $new_member->getLastEdited());
        $this->assertEquals(12, $new_member->getLastEditBy());

        $this->assertNotSame($member, $new_member);
    }
}
