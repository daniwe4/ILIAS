<?php
namespace CaT\Plugins\AgendaItemPool\Settings;

/**
 * Interface for DB handle of additional setting values
 */
interface DB
{
    /**
     * Update settings of an existing repo object.
     *
     * @param	Settings		$settings
     */
    public function update(Settings $settings);

    /**
     * Create a new settings object for Settings object.
     *
     * @param	int			$obj_id
     *
     * @return \CaT\Plugins\AgendaItemPool\Settings\Settings
     */
    public function create($obj_id);

    /**
     * return Settings for $obj_id
     *
     * @param int $obj_id
     *
     * @return \CaT\Plugins\AgendaItemPool\Settings\Settings
     */
    public function selectFor($obj_id);

    /**
     * Delete all information of the given obj id
     *
     * @param 	int 	$obj_id
     */
    public function deleteFor($obj_id);
}
