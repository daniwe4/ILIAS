<?php
namespace CaT\Plugins\CourseMailing\Settings;

/**
 * Interface for DB handle of role mappings
 */
interface DB
{
    /**
     * Create a new Setting with amount of days (offset to course start)
     * to trigger invitation mails.
     *
     * @param int 	$obj_id
     * @param int  	$days_invite
     * @param int  	$days_remind_invitation
     * @param bool 	$prevent_mailing
     *
     * @return \CaT\Plugins\CourseMailing\Settings\Setting
     */
    public function create($obj_id, $days_invite, $days_remind_invitation, $prevent_mailing);

    /**
     * Update a Settings
     *
     * @param	Setting		$setting
     */
    public function update(Setting $setting);

    /**
     * get Setting for $obj_id
     *
     * @param int $obj_id
     *
     * @return \CaT\Plugins\CourseMailing\Settings\Setting | null
     */
    public function selectForObject($obj_id);

    /**
     * delete Setting for $obj_id
     *
     * @param int $obj_id
     *
     * @return void
     */
    public function deleteForObject($obj_id);
}
