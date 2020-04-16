<?php

namespace CaT\Plugins\BookingModalities\Settings\Member;

/**
 * Modalities for member
 */
class Member
{
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var int | null
     */
    protected $min;

    /**
     * @var int | null
     */
    protected $max;

    /**
     * @param int | null	$obj_id
     * @param int | null	$min
     * @param int | null	$max
     */
    public function __construct($obj_id, $min = null, $max = null)
    {
        assert('is_int($obj_id)');
        assert('is_int($min) || is_null($min)');
        assert('is_int($max) || is_null($max)');

        $this->obj_id = $obj_id;
        $this->min = $min;
        $this->max = $max;
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
     * Get min members
     *
     * @return int
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * Get max members
     *
     * @return int
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * Get clone of this with min
     *
     * @param int | null	$min
     *
     * @return Member
     */
    public function withMin($min)
    {
        assert('is_int($min) || is_null($min)');
        $clone = clone $this;
        $clone->min = $min;
        return $clone;
    }

    /**
     * Get clone of this with max
     *
     * @param int | null	$max
     *
     * @return Member
     */
    public function withMax($max)
    {
        assert('is_int($max) || is_null($max)');
        $clone = clone $this;
        $clone->max = $max;
        return $clone;
    }
}
