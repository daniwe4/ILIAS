<?php

declare(strict_types=1);

namespace CaT\Plugins\BookingApprovals\Settings;

/**
 * Interface for DB handle of additional setting values.
 *
 * @author  Nils Haagen 	<nils.haagen@concepts-and-training.de>
  */
interface DB
{
    /**
     * Create a new settings object for BookingApprovals object.
     */
    public function create(int $obj_id, bool $superior_view = false) : BookingApprovals;

    /**
     * Return BookingApprovals for $obj_id.
     */
    public function selectFor(int $obj_id) : BookingApprovals;

    /**
     * Update settings of an existing repo object.
     * @return 	void
     */
    public function update(BookingApprovals $settings);

    /**
     * Delete all information of the given obj id.
     * @return 	void
     */
    public function deleteFor(int $obj_id);
}
