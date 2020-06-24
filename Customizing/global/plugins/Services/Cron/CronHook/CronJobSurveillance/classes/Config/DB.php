<?php
namespace CaT\Plugins\CronJobSurveillance\Config;

/**
 * Interface for DB handle of job settings
 */
interface DB
{
    /**
     * Get all settings.
     *
     * @return 	JobSetting[]
     */
    public function select() : array;

    /**
     * Create a new Setting
     *
     * @param 	JobSetting 	$job_setting
     * @param 	int 		$counter
     *
     * @return void
     */
    public function create(JobSetting $job_setting, int $counter) : void;

    /**
     * Select setting by job_id.
     *
     * @param string $job_id
     * @return void
     */
    public function selectForJob(string $job_id) : array;

    /**
     * Delete setting for $job_id
     *
     * @param 	string 	$job_id
     *
     * @return void
     */
    public function deleteForJob(string $job_id) : void;

    /**
     * Delete all db entries.
     *
     * @return void
     */
    public function deleteAll() : void;
}
