<?php

namespace CaT\Plugins\CourseMember\Members;

/**
 * Immutable object with informations aubout course member
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class Member
{
    /**
     * @var int
     */
    protected $user_id;

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
    protected $login;

    /**
     * @var int
     */
    protected $crs_id;

    /**
     * @var int
     */
    protected $lp_id;

    /**
     * @var string
     */
    protected $lp_value;

    /**
     * @var int
     */
    protected $ilias_lp;

    /**
     * @var int
     */
    protected $credits;

    /**
     * @var \ilDateTime
     */
    protected $last_edited;

    /**
     * @var int
     */
    protected $last_edit_by;

    /**
     * @var int | null
     */
    protected $idd_learning_time;

    /**
     * @param int 	$user_id
     * @param int 	$crs_id
     * @param string | null	$lp_value
     * @param int | null	$ilias_lp
     * @param int | null	$credits
     * @param \ilDateTime | null 	$last_edited
     * @param int | null 	$last_edit_by
     */
    public function __construct(
        int $user_id,
        int $crs_id,
        ?int $lp_id = null,
        ?string $lp_value = null,
        ?int $ilias_lp = null,
        ?float $credits = null,
        ?int $idd_learning_time = null,
        ?\ilDateTime $last_edited = null,
        ?int $last_edit_by = null
    ) {
        $this->user_id = $user_id;
        $this->crs_id = $crs_id;
        $this->lp_id = $lp_id;
        $this->lp_value = $lp_value;
        $this->ilias_lp = $ilias_lp;
        $this->credits = $credits;
        $this->last_edited = $last_edited;
        $this->last_edit_by = $last_edit_by;
        $this->idd_learning_time = $idd_learning_time;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Needed for tableprocessor
     *
     * @return int
     */
    public function getId()
    {
        return $this->user_id;
    }

    /**
     * @return int
     */
    public function getCrsId()
    {
        return $this->crs_id;
    }

    /**
     * @return int
     */
    public function getLPId()
    {
        return $this->lp_id;
    }

    /**
     * @return string
     */
    public function getLPValue()
    {
        return $this->lp_value;
    }

    /**
     * @return int
     */
    public function getILIASLP()
    {
        return $this->ilias_lp;
    }

    /**
     * @return int
     */
    public function getCredits()
    {
        return $this->credits;
    }

    /**
     * @return \ilDateTime
     */
    public function getLastEdited()
    {
        return $this->last_edited;
    }

    /**
     * @return int
     */
    public function getLastEditBy()
    {
        return $this->last_edit_by;
    }

    /**
     * @return int
     */
    public function getIDDLearningTime()
    {
        return $this->idd_learning_time;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Get clone with credits
     *
     * @param int | null	$credits
     *
     * @return Member
     */
    public function withCredits(?int $credits)
    {
        $clone = clone $this;
        $clone->credits = $credits;
        return $clone;
    }

    /**
     * Get clone with last edited
     *
     * @param \ilDateTime	$last_edited
     *
     * @return Member
     */
    public function withLastEdited(\ilDateTime $last_edited)
    {
        $clone = clone $this;
        $clone->last_edited = $last_edited;
        return $clone;
    }

    /**
     * Get clone with last edit by
     *
     * @param int 	$edit_user_id
     *
     * @return Member
     */
    public function withLastEditBy(int $edit_user_id)
    {
        $clone = clone $this;
        $clone->last_edit_by = $edit_user_id;
        return $clone;
    }

    /**
     * Get clone with idd learning time
     *
     * @param int 	$edit_user_id
     *
     * @return Member
     */
    public function withIDDLearningTime(?int $idd_learning_time)
    {
        $clone = clone $this;
        $clone->idd_learning_time = $idd_learning_time;
        return $clone;
    }

    /**
     * @param string 	$firstname
     * @return Member
     */
    public function withFirstname($firstname)
    {
        $clone = clone $this;
        $clone->firstname = $firstname;
        return $clone;
    }

    /**
     * @param string 	$lastname
     * @return Member
     */
    public function withLastname($lastname)
    {
        $clone = clone $this;
        $clone->lastname = $lastname;
        return $clone;
    }

    /**
     * @param string 	$login
     * @return Member
     */
    public function withLogin($login)
    {
        $clone = clone $this;
        $clone->login = $login;
        return $clone;
    }
}
