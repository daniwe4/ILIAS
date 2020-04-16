<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Rating;

/**
 * Inteface for general configuration DB
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    public function create(
        int $id,
        float $rating = 0.0,
        string $info = ""
    ) : Rating;

    public function update(Rating $rating);
    public function delete(int $id);
}
