<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Costs;

/**
 * Inteface for costs configuration DB
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    public function create(
        int $id,
        float $fixed_rate_day = null,
        float $fixed_rate_all_inclusive = null,
        float $bed_and_breakfast = null,
        float $bed = null,
        float $fixed_rate_conference = null,
        float $room_usage = null,
        float $other = null,
        string $terms = ""
    ) : Costs;

    public function update(Costs $costs);
    public function delete(int $id);
}
