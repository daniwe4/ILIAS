<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainingDemandAdvanced\Settings;

/**
 * Interface for DB handle of additional setting values
 */
interface SettingsRepository
{
    public function update(Settings $settings);
    public function create(int $obj_id) : Settings;
    public function load(int $obj_id) : Settings;
    public function delete(Settings $settings);
}
