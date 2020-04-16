<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\UserSettings;

/**
 * Interface for DB handle of user-config
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
interface DB
{
    /**
     * @return TEPCalendarSettings[]
     */
    public function selectFor(int $tep_obj_id) : array;

    /**
     * @return TEPCalendarSettings[]
     */
    public function selectForUser(int $tep_obj_id, int $usr_id) : array;

    /**
     * @param TEPCalendarSettings[] $settings
     */
    public function store(array $settings);


    public function deleteFor(int $tep_obj_id);
}
