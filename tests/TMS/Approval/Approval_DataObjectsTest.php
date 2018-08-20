<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

use ILIAS\TMS\Approval;

class Approval_DataObjectsTest extends PHPUnit_Framework_TestCase {

	public function setUp() {
		$this->br_id = 1;
		$this->usr_id = 6;
		$this->crs_ref_id = 667;
		$this->date = new \DateTime('08/17/2018');
		$this->booking_data = 'actually_some_json_string';
		$this->a_id = 2;
		$this->order_number = 0;
		$this->approval_position = 12;
	}

	public function testConstructionBookingRequest() {
		$br = new Approval\BookingRequest(
			$this->br_id,
			$this->usr_id,
			$this->crs_ref_id,
			$this->date,
			$this->booking_data
		);
		$this->assertInstanceOf(Approval\BookingRequest::class, $br);
		return $br;
	}

	/**
	 * @depends testConstructionBookingRequest
	 */
	public function testGettersBookingRequest($br) {
		$this->assertEquals($this->br_id, $br->getId());
		$this->assertEquals($this->usr_id, $br->getUserId());
		$this->assertEquals($this->crs_ref_id, $br->getCourseRefId());
		$this->assertEquals($this->date, $br->getRequestDate());
		$this->assertEquals($this->booking_data, $br->getBookingData());
		$this->assertEquals(Approval\BookingRequest::OPEN, $br->getState());
	}

	/**
	 * @depends testConstructionBookingRequest
	 */
	public function testWithState($br) {
		$br = $br->withState(Approval\BookingRequest::APPROVED);
		$this->assertEquals(Approval\BookingRequest::APPROVED, $br->getState());
	}

	/**
	 * @depends testConstructionBookingRequest
	 */
	public function testWithWrongState($br) {
		try {
			$br = $br->withState(-1);
			$this->assertFalse("This should not happen");
		}
		catch (\InvalidArgumentException $e) {
			$this->assertTrue(true);
		}
	}



	public function testConstructionApproval() {
		$ap = new Approval\Approval(
			$this->a_id,
			$this->br_id,
			$this->order_number,
			$this->approval_position
		);
		$this->assertInstanceOf(Approval\Approval::class, $ap);
		return $ap;
	}

	/**
	 * @depends testConstructionApproval
	 */
	public function testGettersApproval($ap) {
		$this->assertEquals($this->a_id, $ap->getId());
		$this->assertEquals($this->order_number, $ap->getOrderNumber());
		$this->assertEquals($this->approval_position, $ap->getApprovalPosition());
	}

	/**
	 * @depends testConstructionApproval
	 */

	public function testInitialApprovalState($ap) {
		$this->assertEquals(Approval\Approval::OPEN, $ap->getState());
		$this->assertNull($ap->getApprovingUserId());
		$this->assertNull($ap->getApprovalDate());
	}

	/**
	 * @depends testConstructionApproval
	 */
	public function testApprovalWithWrongState($ap) {
		try {
			$ap = $ap->withState(-1);
			$this->assertFalse("This should not happen");
		}
		catch (\InvalidArgumentException $e) {
			$this->assertTrue(true);
		}
	}

	/**
	 * @depends testConstructionApproval
	 */
	public function testApprovalWithState($ap) {
		$ap = $ap->withState(Approval\Approval::APPROVED);
		$this->assertEquals(Approval\Approval::APPROVED, $ap->getState());
		$this->assertFalse($ap->isApproved());
		return $ap;
	}

	/**
	 * @depends testApprovalWithState
	 */
	public function testApprovalWithUser($ap) {
		$approving_user = 7;
		$ap = $ap->withApprovingUserId(7);
		$this->assertEquals($approving_user, $ap->getApprovingUserId());
		$this->assertTrue($ap->isApproved());
	}

	/**
	 * @depends testConstructionApproval
	 */
	public function testApprovalWithDate($ap) {
		$ap = $ap->withApprovalDate($this->date);
		$this->assertEquals($this->date, $ap->getApprovalDate());
	}
}
