<?php

namespace CaT\Plugins\TalentAssessment\Settings;

class TalentAssessment
{
	const IN_PROGRESS = "1";
	const FINISHED = "2";

	/**
	 * @var int
	 */
	protected $obj_id;

	/**
	 * @var int
	 */
	protected $state;

	/**
	 * @var int
	 */
	protected $career_goal_id;

	/**
	 * @var string
	 */
	protected $username;

	/**
	 * @var string
	 */
	protected $firstname;

	/**
	 * @var string
	 */
	protected $lastname;

	/**
	 * @var string
	 */
	protected $email;

	/**
	 * @var \ilDateTime
	 */
	protected $start_date;

	/**
	 * @var \ilDateTime
	 */
	protected $end_date;

	/**
	 * @var string
	 */
	protected $venue;

	/**
	 * @var string
	 */
	protected $org_unit;

	/**
	 * @var boolean
	 */
	protected $started;

	/**
	 * @var float
	 */
	protected $lowmark;

	/**
	 * @var float
	 */
	protected $should_specifiaction;

	/**
	 * @var float
	 */
	protected $potential;

	/**
	 * @var string
	 */
	protected $result_comment;

	/**
	 * @var string
	 */
	protected $default_text_failed;

	/**
	 * @var string
	 */
	protected $default_text_partial;

	/**
	 * @var string
	 */
	protected $default_text_success;

	public function __construct(
	    int $obj_id,
        int $state,
        int $career_goal_id,
        string $username,
        string $firstname,
        string $lastname,
        string $email,
        \ilDateTime $start_date,
        \ilDateTime $end_date,
        string $venue,
        ?string $org_unit,
        bool $started,
        float $lowmark,
        float $should_specifiaction,
        float $potential,
        string $result_comment,
        string $default_text_failed,
        string $default_text_partial,
        string $default_text_success
    ) {
        $this->obj_id = $obj_id;
        $this->state = $state;
        $this->career_goal_id = $career_goal_id;
        $this->username = $username;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->venue = $venue;
        $this->org_unit = $org_unit;
        $this->started = $started;
        $this->lowmark = $lowmark;
        $this->should_specifiaction = $should_specifiaction;
        $this->potential = $potential;
        $this->result_comment = $result_comment;
        $this->default_text_failed = $default_text_failed;
        $this->default_text_partial = $default_text_partial;
        $this->default_text_success = $default_text_success;

    }

	public function withState($state)
	{
		$clone = clone $this;
		$clone->state = $state;

		return $clone;
	}

	public function withCareerGoalID(int $career_goal_id)
	{
		$clone = clone $this;
		$clone->career_goal_id = $career_goal_id;

		return $clone;
	}

	public function withUserdata(string $username, string $firstname, string  $lastname, string  $email)
	{
		$clone = clone $this;
		$clone->username = $username;
		$clone->firstname = $firstname;
		$clone->lastname = $lastname;
		$clone->email = $email;

		return $clone;
	}

	public function withUsername(string $username)
	{
		$clone = clone $this;
		$clone->username = $username;

		return $clone;
	}

	public function withStartDate(\ilDateTime $start_date)
	{
		$clone = clone $this;
		$clone->start_date = $start_date;

		return $clone;
	}

	public function withEndDate(\ilDateTime $end_date)
	{
		$clone = clone $this;
		$clone->end_date = $end_date;

		return $clone;
	}

	public function withVenue(string $venue)
	{
		$clone = clone $this;
		$clone->venue = $venue;

		return $clone;
	}

	public function withOrgUnit(?string $org_unit)
	{
		$clone = clone $this;
		$clone->org_unit = $org_unit;

		return $clone;
	}

	public function withStarted(bool $started)
	{
		$clone = clone $this;
		$clone->started = $started;

		return $clone;
	}

	public function withLowmark(float $lowmark)
	{
		$clone = clone $this;
		$clone->lowmark = $lowmark;

		return $clone;
	}

	public function withShouldSpecifiaction(float $should_specifiaction)
	{
		$clone = clone $this;
		$clone->should_specifiaction = $should_specifiaction;

		return $clone;
	}

	public function withPotential(float $potential)
	{
		$clone = clone $this;
		$clone->potential = $potential;

		return $clone;
	}

	public function withResultComment(string $result_comment)
	{
		$clone = clone $this;
		$clone->result_comment = $result_comment;

		return $clone;
	}

	public function withDefaultTextFailed(string $default_text_failed)
	{
		$clone = clone $this;
		$clone->default_text_failed = $default_text_failed;

		return $clone;
	}

	public function withDefaultTextPartial(string $default_text_partial)
	{
		$clone = clone $this;
		$clone->default_text_partial = $default_text_partial;

		return $clone;
	}

	public function withDefaultTextSuccess(string $default_text_success)
	{
		$clone = clone $this;
		$clone->default_text_success = $default_text_success;

		return $clone;
	}

	public function withFinished($finished)
	{
		if ($finished) {
			$clone = clone $this;
			$clone->state = self::FINISHED;

			return $clone;
		}

		return $this;
	}

	public function getObjId()
	{
		return $this->obj_id;
	}

	public function getState()
	{
		return $this->state;
	}

	public function getCareerGoalId()
	{
		return $this->career_goal_id;
	}

	public function getUsername()
	{
		return $this->username;
	}

	public function getFirstname()
	{
		return $this->firstname;
	}

	public function getLastname()
	{
		return $this->lastname;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getStartDate()
	{
		return $this->start_date;
	}

	public function getEndDate()
	{
		return $this->end_date;
	}

	public function getVenue()
	{
		return $this->venue;
	}

	public function getOrgUnit()
	{
		return $this->org_unit;
	}

	public function getStarted()
	{
		return $this->started;
	}

	public function getLowmark()
	{
		return $this->lowmark;
	}

	public function getShouldSpecification()
	{
		return $this->should_specifiaction;
	}

	public function getPotential()
	{
		return $this->potential;
	}

	public function getResultComment()
	{
		return $this->result_comment;
	}

	public function getDefaultTextFailed()
	{
		return $this->default_text_failed;
	}

	public function getDefaultTextPartial()
	{
		return $this->default_text_partial;
	}

	public function getDefaultTextSuccess()
	{
		return $this->default_text_success;
	}

	public function Finished()
	{
		return $this->state == self::FINISHED;
	}

	/**
	 * return the default text suitable to actual result
	 *
	 * @return string
	 */
	public function getTextForPotential()
	{
		$potential = $this->getPotential();
		$lowmark = $this->getLowmark();
		$should = $this->getShouldSpecification();

		if (!$potential) {
			return "";
		}

		if ($potential < $lowmark) {
			return $this->getDefaultTextFailed();
		} elseif ($potential > $should) {
			return $this->getDefaultTextSuccess();
		} else {
			return $this->getDefaultTextPartial();
		}
	}
}
