<?php

namespace CaT\Plugins\BookingModalities\Settings\Storno;

/**
 * Modalities for storno
 */
class Storno
{
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var int | null
     */
    protected $deadline;

    /**
     * @var int | null
     */
    protected $hard_deadline;

    /**
     * @var string | null
     */
    protected $modus;

    /**
     * @var ApproveRole[]
     */
    protected $approve_roles;

    /**
     * @var string
     */
    protected $reason_type;

    /**
     * @var bool
     */
    protected $reason_optional;

    /**
     * @param int 	$obj_id
     * @param int | null	$deadline
     * @param int | null	$hard_deadline
     * @param string | null	$reason_type
     * @param string | null	$modus
     */
    public function __construct($obj_id, $deadline = null, $hard_deadline = null, $modus = null, $reason_type = null, array $approve_roles = [], $reason_optional = false)
    {
        assert('is_int($obj_id)');
        assert('is_int($deadline) || is_null($min)');
        assert('is_int($hard_deadline) || is_null($min)');
        assert('is_string($modus) || is_null($modus)');
        assert('is_string($reason_type) || is_null($reason_type)');
        assert('is_bool($reason_optional)');

        $this->obj_id = $obj_id;
        $this->deadline = $deadline;
        $this->hard_deadline = $hard_deadline;
        $this->modus = $modus;
        $this->approve_roles = $approve_roles;
        $this->reason_type = $reason_type;
        $this->reason_optional = $reason_optional;
    }

    /**
     * Get the id of parent crs
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->obj_id;
    }

    /**
     * Get deadline of storno
     *
     * @return int
     */
    public function getDeadline()
    {
        return $this->deadline;
    }

    /**
     * Get hard deadline of storno
     *
     * @return int
     */
    public function getHardDeadline()
    {
        return $this->hard_deadline;
    }

    /**
     * Get modus of storno
     *
     * @return string
     */
    public function getModus()
    {
        return $this->modus;
    }

    /**
     * Get role names of roles should approve
     *
     * @return string[]
     */
    public function getApproveRoles()
    {
        return $this->approve_roles;
    }

    /**
     * Get defined reason type for storno
     *
     * @return string
     */
    public function getReasonType()
    {
        return $this->reason_type;
    }

    /**
     * @return bool
     */
    public function getReasonOptional()
    {
        return $this->reason_optional;
    }

    /**
     * Get clone of this with deadline
     *
     * @param int | null	$deadline
     *
     * @return Storno
     */
    public function withDeadline($deadline)
    {
        assert('is_int($deadline) || is_null($deadline)');
        $clone = clone $this;
        $clone->deadline = $deadline;
        return $clone;
    }

    /**
     * Get clone of this with hard_deadline
     *
     * @param int | null	$hard_deadline
     *
     * @return Storno
     */
    public function withHardDeadline($hard_deadline)
    {
        assert('is_int($hard_deadline) || is_null($hard_deadline)');
        $clone = clone $this;
        $clone->hard_deadline = $hard_deadline;
        return $clone;
    }

    /**
     * Get clone of this with modus
     *
     * @param string | null	$modus
     *
     * @return Storno
     */
    public function withModus($modus)
    {
        assert('is_string($modus) || is_null($modus)');
        $clone = clone $this;
        $clone->modus = $modus;
        return $clone;
    }

    /**
     * Get clone of this with approve roles
     *
     * @param ApproveRole[]	$approve_roles
     *
     * @return Storno
     */
    public function withApproveRoles(array $approve_roles)
    {
        $clone = clone $this;
        $clone->approve_roles = $approve_roles;
        return $clone;
    }

    /**
     * Get clone of this with reason type
     *
     * @param string	$reason_type
     *
     * @return Storno
     */
    public function withReasonType($reason_type)
    {
        $clone = clone $this;
        $clone->reason_type = $reason_type;
        return $clone;
    }

    /**
     * Get clone of this with reason optional
     *
     * @param bool 	$reason_optional
     *
     * @return Storno
     */
    public function withReasonOptional($reason_optional)
    {
        assert('is_bool($reason_optional)');
        $clone = clone $this;
        $clone->reason_optional = $reason_optional;
        return $clone;
    }
}
