<?php
declare(strict_types=1);

namespace CaT\Plugins\TrainingStatistics\Settings;

/**
 * Interface for DB handle of additional setting values.
 *
 * @author
 * @copyright Extended GPL, see LICENSE
 */
interface SettingsRepository
{
    /**
     * Update settings of an existing repo object.
     *
     * @param	Settings		$settings
     * @return 	void
     */
    public function update(Settings $settings);

    /**
     * Create a new settings object for TrainingStatistics object.
     *
     * @param	int		$obj_id
     * @return 	\CaT\Plugins\TrainingStatistics\Settings\Settings
     */
    public function create(int $obj_id) : Settings;

    /**
     * Return TrainingStatistics for $obj_id.
     *
     * @param 	int $obj_id
     * @return 	\CaT\Plugins\TrainingStatistics\Settings\Settings
     */
    public function load(int $obj_id) : Settings;

    /**
     * Delete all information of the given obj id.
     *
     * @param 	Settings 	$settings
     * @return 	void
     */
    public function delete(Settings $settings);
}
