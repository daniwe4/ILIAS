<?php

declare(strict_types=1);

namespace CaT\Plugins\RoomSetup\ServiceOptions;

/**
 * DB interface for service options
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    /**
     * Installing plugin necessary tables etc.
     * @return null
     */
    public function install();

    /**
     * Create a new service option in system
     */
    public function create(string $name, bool $active) : ServiceOption;

    /**
     * Updates values of service option
     * @return void
     */
    public function update(ServiceOption $service_option);

    /**
     * Get a single service option
     * @throws \LogicException
     */
    public function select(int $id) : ServiceOption;

    /**
     * Select all service options
     * @return ServiceOption[]
     */
    public function selectAll(int $offset, int $limit, string $order_field, string $order_direction) : array;

    /**
     * Select all active service options
     * @return ServiceOption[]
     */
    public function selectAllActive() : array;

    /**
     * Get inactive assigned service options for GUI
     * @param int[] 	$missing
     * @return array<int, string>
     */
    public function getMissingAssignedInactiveOptions(array $missing) : array;

    /**
     * Delete single service option
     * @return void
     */
    public function deleteById(int $id);
}
