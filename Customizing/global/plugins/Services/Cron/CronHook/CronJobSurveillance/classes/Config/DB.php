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
    public function select();

    /**
     * Create a new Setting
     *
     * @param 	JobSetting 	$job_setting
     * @param 	int 		$counter
     *
     * @return JobSetting
     */
    public function create(JobSetting $job_setting, $counter);

    /**
     * Select setting by job_id.
     *
     * @param string $job_id
     * @return void
     */
    public function selectForJob($job_id);

    /**
     * Delete setting for $job_id
     *
     * @param 	string 	$job_id
     *
     * @return void
     */
    public function deleteForJob($job_id);

    /**
     * Delete all db entries.
     *
     * @return void
     */
    public function deleteAll();
}
