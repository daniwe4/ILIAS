<?php

declare(strict_types=1);

namespace CaT\Plugins\CopySettings\Settings;

/**
 * Interface for saving settings of copy settings
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    public function create(int $obj_id) : Settings;
    public function update(Settings $settings);
    public function select(int $obj_id) : Settings;
    public function delete(int $obj_id);
}
