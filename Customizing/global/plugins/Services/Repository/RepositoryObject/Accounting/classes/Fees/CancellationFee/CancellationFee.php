<?php

declare(strict_types=1);

namespace CaT\Plugins\Accounting\Fees\CancellationFee;

class CancellationFee
{
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var float | null
     */
    protected $cancellation_fee;

    public function __construct(int $obj_id, float $cancellation_fee = null)
    {
        $this->obj_id = $obj_id;
        $this->cancellation_fee = $cancellation_fee;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    /**
     * @return float | null
     */
    public function getCancellationFee()
    {
        return $this->cancellation_fee;
    }

    public function withCancellationFee(float $cancellation_fee = null) : CancellationFee
    {
        $clone = clone $this;
        $clone->cancellation_fee = $cancellation_fee;
        return $clone;
    }
}
