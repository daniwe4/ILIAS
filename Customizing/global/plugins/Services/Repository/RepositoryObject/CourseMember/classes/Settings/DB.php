<?php

namespace CaT\Plugins\CourseMember\Settings;

/**
 * Interface for DB handle of additional setting values
 */
interface DB
{
    /**
     * Update settings of an existing repo object.
     *
     * @param	CourseMemberSettings		$settings
     */
    public function update(CourseMemberSettings $settings);

    /**
     * Create a new settings object for CourseMember object.
     *
     * @param int 	$obj_id
     * @param int 	$credits
     * @param bool 	$closed
     * @param int 	$lp_mode
     * @param bool 	$list_required
     *
     * @return CourseMemberSettings
     */
    public function create(
        int $obj_id,
        $credits,
        bool $closed = false,
        int $lp_mode = 0,
        bool $list_required = false,
        bool $opt_orgu = false,
        bool $opt_text = true
    ) : CourseMemberSettings;

    public function selectFor(int $obj_id) : CourseMemberSettings;

    /**
     * Delete all information of the given obj id
     */
    public function deleteFor(int $obj_id);
}
