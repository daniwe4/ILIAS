<?php

namespace CaT\Plugins\Accounting\Fees\Fee;

interface DB
{
    /**
     * Create a new entry for fee
     *
     * @return Fee
     */
    public function create(int $obj_id, float $fee_value = null);

    /**
     * Updates existing fee entry
     *
     * @param Fee 	$fee
     *
     * @return void
     */
    public function update(Fee $fee);

    /**
     * Selects fee entry for object
     *
     * @param int 	$obj_id
     *
     * @return Fee
     */
    public function select(int $obj_id);

    /**
     * Deletes a fee entry
     *
     * @param int 	$obj_id
     *
     * @return void
     */
    public function delete(int $obj_id);
}
