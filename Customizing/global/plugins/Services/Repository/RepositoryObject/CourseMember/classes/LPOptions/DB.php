<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseMember\LPOptions;

/**
 * Interface to save lp options
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    /**
     * Create a new lp option object as db entry and object
     */
    public function create(string $title, int $ilias_lp, bool $active, bool $default) : LPOption;

    /**
     * Update an existing lp option
     */
    public function update(LPOption $lp_option);

    /**
     * Select all existing lp options
     * @return LPOption[]
     */
    public function select(bool $only_active = false) : array;

    /**
     * Delete all lp options
     */
    public function deleteAll();

    /**
     * Delete a single lp option
     */
    public function delete(int $id);
}
