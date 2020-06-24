<?php

namespace CaT\Plugins\CareerGoal\Observations;
use CaT\Plugins\CareerGoal\Requirements;

class Observation {
	/**
	 * @var int
	 */
	protected $obj_id;

	/**
	 * @var int
	 */
	protected $career_goal_id;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var int
	 */
	protected $position;

	/**
	 * @var int[]
	 */
	protected $requirements;

	public function __construct(
	    int $obj_id,
        int $career_goal_id,
        string $title,
        string $description,
        int $position,
        array $requirements
    ) {
		$this->obj_id = $obj_id;
		$this->career_goal_id = $career_goal_id;
		$this->title = $title;
		$this->description = $description;
		$this->position = $position;
		$this->requirements = $requirements;
	}

	public function withTitle(string $title) {
		$clone = clone $this;
		$clone->title = $title;

		return $clone;
	}

	public function withDescription(string $description) {
		$clone = clone $this;
		$clone->description = $description;

		return $clone;
	}

	public function withPosition(int $position) {
		$clone = clone $this;
		$clone->position = $position;

		return $clone;
	}

	public function withRequirements(array $requirements) {
		$clone = clone $this;
		$clone->requirements = $requirements;

		return $clone;
	}

	public function getObjId() {
		return $this->obj_id;
	}

	public function getCareerGoalId() {
		return $this->career_goal_id;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getDescription() {
		return $this->description;
	}

	public function getPosition() {
		return $this->position;
	}

	public function getRequirements() {
		return $this->requirements;
	}
}