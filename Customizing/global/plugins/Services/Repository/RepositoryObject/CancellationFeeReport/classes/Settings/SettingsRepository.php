<?php declare(strict_types = 1);
namespace CaT\Plugins\CancellationFeeReport\Settings;

/**
 * Interface for DB handle of additional setting values
 */
interface SettingsRepository
{
    /**
     * Update settings of an existing repo object.
     *
     * @param	Settings		$settings
     */
    public function update(Settings $settings);

    /**
     * Create a new settings object for object.
     *
     * @param	int	$obj_id
     *
     * @return	Settings
     */
    public function create(int $obj_id) : Settings;

    /**
     * return Settings for $obj
     *
     * @param	int	$obj_id
     *
     * @return	Settings
     */
    public function load(int $obj_id) : Settings;

    /**
     * Delete all information of the given obj id
     *
     * @param	Settings $settings
     * @return	void
     */
    public function delete(Settings $settings);

    /**
     * Check if data exists for obj_id
     *
     * @param	int	$obj_id
     * @return	bool
     */
    public function exists(int $obj_id) : bool;
}
