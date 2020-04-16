<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Address;

/**
 * Inteface for contact address DB
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    public function create(
        int $id,
        string $address1 = "",
        string $country = "",
        string $address2 = "",
        string $postcode = "",
        string $city = "",
        float $latitude = 0.0,
        float $longitude = 0.0,
        int $zoom = 10
    ) : Address;

    public function update(Address $address);
    public function delete(int $id);
}
