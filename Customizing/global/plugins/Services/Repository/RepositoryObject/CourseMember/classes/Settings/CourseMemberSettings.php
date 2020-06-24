<?php

namespace CaT\Plugins\CourseMember\Settings;

/**
 * This is the object for additional settings.
 */
class CourseMemberSettings
{
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var float
     */
    protected $credits;

    /**
     * @var bool
     */
    protected $closed;

    /**
     * @var int
     */
    protected $lp_mode;

    /**
     * @var int
     */
    protected $list_required;

    /**
     * @var bool
     */
    protected $opt_orgu;

    /**
     * @var bool
     */
    protected $opt_text;

    /**
     * @param int 	$obj_id
     * @param float | null	$credits
     * @param bool 	$closed
     * @param int 	$lp_mode
     * @param bool 	$list_required
     */
    public function __construct(
        int $obj_id,
        $credits = null,
        bool $closed = false,
        int $lp_mode = 0,
        bool $list_required = false,
        bool $opt_orgu = false,
        bool $opt_text = true

    ) {
        assert('is_float($credits) || is_null($credits)');
        $this->obj_id = $obj_id;
        $this->credits = $credits;
        $this->closed = $closed;
        $this->lp_mode = $lp_mode;
        $this->list_required = $list_required;
        $this->opt_orgu = $opt_orgu;
        $this->opt_text = $opt_text;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    /**
     * @return float|null
     */
    public function getCredits()
    {
        return $this->credits;
    }

    public function getClosed() : bool
    {
        return $this->closed;
    }

    public function getLPMode() : int
    {
        return $this->lp_mode;
    }

    public function getListRequired() : bool
    {
        return $this->list_required;
    }

    public function withLPMode(int $lp_mode) : CourseMemberSettings
    {
        $clone = clone $this;
        $clone->lp_mode = $lp_mode;
        return $clone;
    }

    /**
     * Get clone with credits
     *
     * @param float 	$credits
     */
    public function withCredits($credits) : CourseMemberSettings
    {
        assert('is_float($credits) || is_null($credits)');
        $clone = clone $this;
        $clone->credits = $credits;
        return $clone;
    }

    public function withClosed(bool $closed) : CourseMemberSettings
    {
        $clone = clone $this;
        $clone->closed = $closed;
        return $clone;
    }

    public function withListRequired(bool $list_required) : CourseMemberSettings
    {
        $clone = clone $this;
        $clone->list_required = $list_required;
        return $clone;
    }

    public function withListOptionOrgu(bool $opt) : CourseMemberSettings
    {
        $clone = clone $this;
        $clone->opt_orgu = $opt;
        return $clone;
    }

    public function getListOptionOrgu() : bool
    {
        return $this->opt_orgu;
    }

    public function withListOptionText(bool $opt) : CourseMemberSettings
    {
        $clone = clone $this;
        $clone->opt_text = $opt;
        return $clone;
    }

    public function getListOptionText() : bool
    {
        return $this->opt_text;
    }
}
