<?php
namespace CaT\Plugins\Accomodation\TableProcessing;

/**
 * Backend interface for table processing.
 * Defines options how to act with table elements
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface Backend
{
    /**
     * Delete the option in record
     *
     * @param array
     *
     * @return null
     */
    public function delete($record);

    /**
     * Checks option in record if it is valid
     * If not fills key errors with values
     *
     * @param array
     *
     * @return array
     */
    public function valid($record);

    /**
     * Update an existing option
     *
     * @param array
     *
     * @return array
     */
    public function update($record);

    /**
     * Creates a new option
     *
     * @param array
     *
     * @return array
     */
    public function create($record);
}
