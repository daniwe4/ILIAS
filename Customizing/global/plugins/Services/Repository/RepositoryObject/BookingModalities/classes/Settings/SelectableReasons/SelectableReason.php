<?php

namespace CaT\Plugins\BookingModalities\Settings\SelectableReasons;

/**
 * Keeps information of selectable reasons
 *
 * @author 	Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class SelectableReason
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $reason;

    /**
     * @var bool
     */
    protected $active;

    /**
     * @param int 	$id
     * @param string 	$reason
     * @param bool 	$active
     */
    public function __construct($id = -1, $reason = "", $active = false)
    {
        assert('is_int($id)');
        assert('is_string($reason)');
        assert('is_bool($active)');

        $this->id = $id;
        $this->reason = $reason;
        $this->active = $active;
    }

    /**
     * Get the id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the reason
     *
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Get active
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Get clone with reason
     *
     * @param string 	$reason
     *
     * @return this
     */
    public function withReason($reason)
    {
        assert('is_string($reason)');
        $clone = clone $this;
        $clone->reason = $reason;
        return $clone;
    }

    /**
     * Get clone with active
     *
     * @param bool 	$active
     *
     * @return this
     */
    public function withActive($active)
    {
        assert('is_bool($active)');
        $clone = clone $this;
        $clone->active = $active;
        return $clone;
    }
}
