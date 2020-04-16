<?php

declare(strict_types=1);

namespace CaT\Plugins\Accounting\Fees\Fee;

class Fee
{
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var float | null
     */
    protected $fee;

    public function __construct(int $obj_id, float $fee = null)
    {
        $this->obj_id = $obj_id;
        $this->fee = $fee;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    /**
     * @return float | null
     */
    public function getFee()
    {
        return $this->fee;
    }

    public function withFee(float $fee = null) : Fee
    {
        $clone = clone $this;
        $clone->fee = $fee;
        return $clone;
    }
}
