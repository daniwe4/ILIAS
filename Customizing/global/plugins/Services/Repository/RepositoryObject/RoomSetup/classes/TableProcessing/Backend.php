<?php

namespace CaT\Plugins\RoomSetup\TableProcessing;

interface Backend
{
    /**
     * Delete the service option in record
     *
     * @param array
     *
     * @return null
     */
    public function delete($record);

    /**
     * Checks service option in record if it is valid
     * If not fills key errors with values
     *
     * @param array
     *
     * @return array
     */
    public function valid($record);

    /**
     * Update an existing service option
     *
     * @param array
     *
     * @return array
     */
    public function update($record);

    /**
     * Creates a new service option
     *
     * @param array
     *
     * @return array
     */
    public function create($record);
}
