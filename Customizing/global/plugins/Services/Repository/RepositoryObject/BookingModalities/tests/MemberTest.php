<?php

use CaT\Plugins\BookingModalities\Settings\Member\Member;
use PHPUnit\Framework\TestCase;

/**
 * Sample for PHP Unit tests
 */
class MemberTest extends TestCase
{
    public function test_create()
    {
        $member = new Member(1, 10, 20);

        $this->assertEquals(1, $member->getObjId());
        $this->assertEquals(10, $member->getMin());
        $this->assertEquals(20, $member->getMax());

        return $member;
    }

    /**
     * @depends test_create
     */
    public function test_withMin($member)
    {
        $new_member = $member->withMin(15);

        $this->assertEquals(1, $member->getObjId());
        $this->assertEquals(10, $member->getMin());
        $this->assertEquals(20, $member->getMax());

        $this->assertEquals(1, $new_member->getObjId());
        $this->assertEquals(15, $new_member->getMin());
        $this->assertEquals(20, $new_member->getMax());

        return $member;
    }

    /**
     * @depends test_withMin
     */
    public function test_withMax($member)
    {
        $new_member = $member->withMax(25);

        $this->assertEquals(1, $member->getObjId());
        $this->assertEquals(10, $member->getMin());
        $this->assertEquals(20, $member->getMax());

        $this->assertEquals(1, $new_member->getObjId());
        $this->assertEquals(10, $new_member->getMin());
        $this->assertEquals(25, $new_member->getMax());
    }
}
