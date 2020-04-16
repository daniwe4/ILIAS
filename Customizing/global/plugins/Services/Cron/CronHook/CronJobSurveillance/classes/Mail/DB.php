<?php
namespace CaT\Plugins\CronJobSurveillance\Mail;

/**
 * Interface for DB handle of mail settings
 */
interface DB
{
    /**
     * Get all settings.
     *
     * @return 	MailSetting[]
     */
    public function select();

    /**
     * Create a new Setting
     *
     * @param 	string 	$id
     * @param 	int  	$mail_address
     *
     * @return MailSetting
     */
    public function create($id, $mail_address);

    /**
     * Update a Setting
     *
     * @param	MailSetting		$setting
     */
    public function update(MailSetting $setting);

    /**
     * Delete setting for $id
     *
     * @param 	string 	$id
     *
     * @return void
     */
    public function deleteFor($id);
}
