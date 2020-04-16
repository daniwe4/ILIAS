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
     */
    public function createListProviderAssignment(int $crs_id, int $provider_id) : ListAssignment;

    /**
     * Creates a new custom-provider assignment
     */
    public function createCustomProviderAssignment(int $crs_id, string $text) : CustomAssignment;

    /**
     * Read assignment of course from DB and return ProviderAssignment (or false)
     *
     * @return \CaT\Plugins\Provider\ProviderAssignment\ProviderAssignment | false
     */
    public function select(int $crs_id);

    /**
     * Update a provider assignment
     */
    public function update(ProviderAssignment $provider_assignment) : void;

    /**
     * Delete a provider assignment for a course
     */
    public function delete(int $crs_id) : void;
}
