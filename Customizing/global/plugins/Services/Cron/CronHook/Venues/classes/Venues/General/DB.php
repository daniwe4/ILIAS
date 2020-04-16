<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\General;

/**
 * Inteface for general configuration DB
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    public function create(
        int $id,
        string $name,
        string $homepage = "",
        array $tags = array(),
        array $search_tags = array()
    ) : General;

    public function update(General $general);
    public function delete(int $id);
}
