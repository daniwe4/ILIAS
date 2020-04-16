<?php

namespace CaT\Plugins\CourseMember\TableProcessing;

/**
 * This describes functions the table processor uses to proceed different actions
 *
 * Every basic option who wants to use the table processor needs
 * an implementation of this interface
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface backend
{
    /**
     * Delete the option in record
     *
     * @param array 	$record
     *
     * @return null
     */
    public function delete(array $record);

    /**
     * Checks option in record if it is valid
     * If not fills key errors with values
     *
     * @param array 	$record
     *
     * @return array
     */
    public function valid(array $record);

    /**
     * Update an existing option
     *
     * @param array 	$record
     *
     * @return array
     */
    public function update(array $record);

    /**
     * Creates a new option
     *
     * @param array 	$record
     *
     * @return array
     */
    public function create(array $record);
}
