<?php

namespace CaT\Plugins\BookingModalities\Settings\Waitinglist;

/**
 * Modalities for waitinglist
 */
class Waitinglist
{
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var int | null
     */
    protected $cancellation;

    /**
     * @var int | null
     */
    protected $max;

    /**
     * @var string | null
     */
    protected $modus;

    /**
     * @param int 	$obj_id
     * @param int | null	$cancellation
     * @param int | null	$max
     * @param string | null	$modus
     */
    public function __construct(
        int $obj_id,
        ?int $cancellation = null,
        ?int $max = null,
        ?string $modus = null
    ) {
        $this->obj_id = $obj_id;
        $this->cancellation = $cancellation;
        $this->max = $max;
        $this->modus = $modus;
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
     * Get cancellation of waitinglist
     *
     * @return int
     */
    public function getCancellation()
    {
        return $this->cancellation;
    }

    /**
     * Get max of waitinglist
     *
     * @return int
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * Get modus of waitinglist
     *
     * @return string
     */
    public function getModus()
    {
        return $this->modus;
    }

    /**
     * Get clone of this with cancellation
     *
     * @param int | null	$cancellation
     *
     * @return Waitinglist
     */
    public function withCancellation(?int $cancellation)
    {
        $clone = clone $this;
        $clone->cancellation = $cancellation;
        return $clone;
    }

    /**
     * Get clone of this with max
     *
     * @param int | null	$max
     *
     * @return Waitinglist
     */
    public function withMax(?int $max)
    {
        $clone = clone $this;
        $clone->max = $max;
        return $clone;
    }

    /**
     * Get clone of this with modus
     *
     * @param string | null	$modus
     *
     * @return Waitinglist
     */
    public function withModus(?string $modus)
    {
        $clone = clone $this;
        $clone->modus = $modus;
        return $clone;
    }
}
