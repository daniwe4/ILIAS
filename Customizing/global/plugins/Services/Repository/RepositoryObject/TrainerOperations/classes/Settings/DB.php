<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\Settings;

/**
 * Interface for DB handle of additional setting values.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
interface DB
{
    public function createEmpty(int $obj_id) : Settings;

    public function update(Settings $settings);

    public function selectFor(int $obj_id) : Settings;

    public function deleteFor(int $obj_id, \ilDBInterface $db = null);
}
