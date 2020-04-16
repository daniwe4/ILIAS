<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace CaT\Plugins\TrainingProvider\ProviderAssignment;

/**
 * Describes database functionalities for course/provider relation
 *
 * @author Nils Haagen	<nils.haagen@concepts-and-training.de>
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
interface DB
{
    /**
     * Creates a new list-provider assignment
     *
     * @param int 		$crs_id
     * @param int 		$provider_id
     *
     * @return \CaT\Plugins\Provider\ProviderAssignment\ListAssignment
     */
    public function createListProviderAssignment($crs_id, $provider_id);

    /**
     * Creates a new list-provider assignment
     *
     * @param int 		$crs_id
     * @param string 	$text
     *
     * @return \CaT\Plugins\Provider\ProviderAssignment\CustomAssignment
     */
    public function createCustomProviderAssignment($crs_id, $text);

    /**
     * Read assignment of course from DB and return ProviderAssignment (or false)
     *
     * @param int 		$crs_id
     *
     * @return \CaT\Plugins\Provider\ProviderAssignment\ProviderAssignment | false
     */
    public function select($crs_id);

    /**
     * Update a provider assignment
     *
     * @param \CaT\Plugins\Provider\Provider\ProviderAssignment 		$provider_assignment
     */
    public function update(ProviderAssignment $provider_assignment);

    /**
     * Delete a provider assignment for a course
     *
     * @param int 		$crs_id
     */
    public function delete($crs_id);
}
