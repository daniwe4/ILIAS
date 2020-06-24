<?php

namespace CaT\Plugins\BookingModalities\Settings\Booking;

/**
 * Modalities for booking
 */
class Booking
{
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var int | null
     */
    protected $beginning;

    /**
     * @var int | null
     */
    protected $deadline;

    /**
     * @var string | null
     */
    protected $modus;

    /**
     * @var string[] | null
     */
    protected $approve_roles;

    /**
     * @var bool
     */
    protected $to_be_acknowledged;

    /**
     * @var bool
     */
    protected $skip_duplicate_check;

    /**
     * @var bool
     */
    protected $hide_superior_approve;

    /**
     * @param int 	$obj_id
     * @param int | null	$beginning
     * @param int | null	$deadline
     * @param string | null	$modus
     */
    public function __construct(
        int $obj_id,
        ?int $beginning = null,
        ?int $deadline = null,
        ?string $modus = null,
        array $approve_roles = [],
        bool $to_be_acknowledged = false,
        bool $skip_duplicate_check = false,
        bool $hide_superior_approve = false
    ) {
        $this->obj_id = $obj_id;
        $this->beginning = $beginning;
        $this->deadline = $deadline;
        $this->modus = $modus;
        $this->approve_roles = $approve_roles;
        $this->to_be_acknowledged = $to_be_acknowledged;
        $this->skip_duplicate_check = $skip_duplicate_check;
        $this->hide_superior_approve = $hide_superior_approve;
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
     * Get beginning of booking
     *
     * @return int
     */
    public function getBeginning()
    {
        return $this->beginning;
    }

    /**
     * Get deadline of booking
     *
     * @return int
     */
    public function getDeadline()
    {
        return $this->deadline;
    }

    /**
     * Get modus of booking
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
     * Get should be acknowledged
     *
     * @return bool
     */
    public function getToBeAcknowledged()
    {
        return $this->to_be_acknowledged;
    }


    /**
     * Get the duplicate check should be skipped
     *
     * @return bool
     */
    public function getSkipDuplicateCheck()
    {
        return $this->skip_duplicate_check;
    }

    /**
     * Get the superior approval should be hidden
     *
     * @return bool
     */
    public function getHideSuperiorApprove()
    {
        return $this->hide_superior_approve;
    }

    /**
     * Get clone of this with beginning
     *
     * @param int | null	$beginning
     *
     * @return Booking
     */
    public function withBeginning(?int $beginning)
    {
        $clone = clone $this;
        $clone->beginning = $beginning;
        return $clone;
    }

    /**
     * Get clone of this with deadline
     *
     * @param int | null	$deadline
     *
     * @return Booking
     */
    public function withDeadline(?int $deadline)
    {
        $clone = clone $this;
        $clone->deadline = $deadline;
        return $clone;
    }

    /**
     * Get clone of this with modus
     *
     * @param string | null	$modus
     *
     * @return Booking
     */
    public function withModus(?string $modus)
    {
        $clone = clone $this;
        $clone->modus = $modus;
        return $clone;
    }

    /**
     * Get clone of this with approve roles
     *
     * @param string[]	$approve_roles
     *
     * @return Booking
     */
    public function withApproveRoles(array $approve_roles)
    {
        $clone = clone $this;
        $clone->approve_roles = $approve_roles;
        return $clone;
    }

    /**
     * Get clone of this with approve roles
     *
     * @param bool	$to_be_acknowledged
     *
     * @return Booking
     */
    public function withToBeAcknowledged($to_be_acknowledged)
    {
        $clone = clone $this;
        $clone->to_be_acknowledged = $to_be_acknowledged;
        return $clone;
    }


    /**
     * Get clone of this with skip duplicate check
     *
     * @param bool 	$skip_duplicate_check
     *
     * @return Booking
     */
    public function withSkipDuplicateCheck(bool $skip_duplicate_check)
    {
        $clone = clone $this;
        $clone->skip_duplicate_check = $skip_duplicate_check;
        return $clone;
    }

    /**
     * Get clone of this with hide superior approve
     *
     * @param bool 	$hide_superior_approve
     *
     * @return Booking
     */
    public function withHideSuperiorApprove(bool $hide_superior_approve)
    {
        $clone = clone $this;
        $clone->hide_superior_approve = $hide_superior_approve;
        return $clone;
    }
}
