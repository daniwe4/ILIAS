<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Conditions;

/**
 * Inteface for contact conditions DB
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    /**
     * Create a conditions configuration
     *
     * @param int 	$id
     * @param bool 	$general_agreement
     * @param string 	$terms
     * @param string 	$valuta
     *
     * @return Conditions
     */
    public function create(
        int $id,
        bool $general_agreement = false,
        string $terms = "",
        string $valuta = ""
    ) : Conditions;

    public function update(Conditions $conditions);
    public function delete(int $id);
}
