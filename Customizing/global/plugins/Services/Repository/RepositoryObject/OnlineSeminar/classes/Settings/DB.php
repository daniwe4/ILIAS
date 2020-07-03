<?php

namespace CaT\Plugins\OnlineSeminar\Settings;

require_once("Services/Calendar/classes/class.ilDateTime.php");

/**
 * Interface for DB handle of additional setting values
 */
interface DB
{
    /**
     * Update settings of an existing repo object.
     *
     * @param	OnlineSeminar		$settings
     */
    public function update(OnlineSeminar $settings);

    /**
     * Create a new settings object for OnlineSeminar object.
     *
     * @param int 	$obj_id
     * @param string 	$vc_type
     * @param \ilDateTime | null 	$beginning
     * @param \ilDateTime | null 	$ending
     * @param string | null 	$admission
     * @param string | null 	$url
     * @param bool 	$online
     *
     * @return \CaT\Plugins\OnlineSeminar\Settings\OnlineSeminar
     */
    public function create(
        int $obj_id,
        string $vc_type,
        ?\ilDateTime $beginning = null,
        ?\ilDateTime $ending = null,
        ?string $admission = null,
        ?string $url = null,
        bool $online = false,
        int $lp_mode = 0
    );

    /**
     * return OnlineSeminar for $obj_id
     *
     * @param int $obj_id
     *
     * @return \CaT\Plugins\OnlineSeminar\Settings\OnlineSeminar
     */
    public function selectFor($obj_id);

    /**
     * Delete all information of the given obj id
     *
     * @param 	int 	$obj_id
     */
    public function deleteFor($obj_id);
}
