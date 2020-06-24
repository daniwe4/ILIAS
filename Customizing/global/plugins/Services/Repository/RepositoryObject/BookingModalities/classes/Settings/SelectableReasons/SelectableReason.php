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
    public function __construct(int $id = -1, string $reason = "", bool $active = false)
    {
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
     * @return SelectableReason
     */
    public function withReason(string $reason)
    {
        $clone = clone $this;
        $clone->reason = $reason;
        return $clone;
    }

    /**
     * Get clone with active
     *
     * @param bool 	$active
     *
     * @return SelectableReason
     */
    public function withActive(bool $active)
    {
        $clone = clone $this;
        $clone->active = $active;
        return $clone;
    }
}
