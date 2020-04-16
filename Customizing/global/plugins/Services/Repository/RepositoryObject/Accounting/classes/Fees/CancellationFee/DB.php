<?php
/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Accounting\Fees\CancellationFee;

interface DB
{
    public function create(int $obj_id, float $fee_value = null) : CancellationFee;

    public function update(CancellationFee $fee);

    public function select(int $obj_id) : CancellationFee;

    public function delete(int $obj_id);
}
