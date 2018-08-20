<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\Approval;

/**
 *
 */
class BookingRequest {

	//states
	const OPEN = 1;
	const APPROVED = 2;
	const DECLINED = 3;
	const OUTDATED = 4;
	const CANCELED_BY_USER = 5;
	const CANCELED_FOR_REASONS = 6;


	protected $id;
	protected $usr_id;
	protected $crs_ref_id;
	protected $date;
	protected $booking_data;
	protected $state;

	public function __construct(
		int $id,
		int $usr_id,
		int $crs_ref_id,
		\DateTime $date,
		string $booking_data = '',
		$state = 1
	) {
		$this->id = $id;
		$this->usr_id = $usr_id;
		$this->crs_ref_id = $crs_ref_id;
		$this->date = $date;
		$this->booking_data = $booking_data;
		$this->checkValidState($state);
		$this->state = $state;
	}

	public function getId() : int {
		return $this->id;
	}

	public function getUserId() : int {
		return $this->usr_id;
	}

	public function getCourseRefId() : int {
		return $this->crs_ref_id;
	}

	public function getRequestDate() : \DateTime {
		return $this->date;
	}

	public function getBookingData() : string {
		return $this->booking_data;
	}

	protected function checkValidState($state) {
		if(! in_array($state, [
			static::OPEN,
			static::APPROVED,
			static::DECLINED,
			static::OUTDATED,
			static::CANCELED_BY_USER,
			static::CANCELED_FOR_REASONS
		])) {
			throw new \InvalidArgumentException("Invalid State: $state", 1);
		}
	}

	public function withState(int $state) : BookingRequest {
		$this->checkValidState($state);
		$clone = clone $this;
		$clone->state = $state;
		return $clone;
	}

	public function getState() : int {
		return $this->state;
	}

}