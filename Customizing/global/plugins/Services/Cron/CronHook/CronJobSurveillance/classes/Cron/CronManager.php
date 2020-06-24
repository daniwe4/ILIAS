<?php
namespace CaT\Plugins\CronJobSurveillance\Cron;

/**
 * This is the facade for ILIAS' cronManager.
 *
 * @author Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class CronManager
{

    /*
     * @var \ilCronManager
     */
    private $cron_manager;

    /**
     * @param \ilCronManager $cron_manager
     */
    public function __construct(\ilCronManager $cron_manager)
    {
        $this->cron_manager = $cron_manager;
    }

    /**
     * Get information about a cron-job.
     *
     * @param 	string 	$id
     * @return 	array<string, mixed>
     */
    public function getCronJobData(string $id)
    {
        return $this->cron_manager->getCronJobData($id);
    }

    /**
     * Get Schedule-values for a job with non-flexible schedule.
     *
     * @param 	string 	$id
     * @param 	string 	$component
     * @return 	int[]|null[]
     */
    public function getFixScheduleForJob(string $id, string $component)
    {
        $job = $this->cron_manager->getJobInstanceById($id);

        if (!$job) { //try to get from plugin
            $id = 'pl__' . $component . '__' . $id;
            $job = $this->cron_manager->getJobInstanceById($id);
        }

        if (!$job) {
            return [null, null];
        }

        $schedule_type = $job->getDefaultScheduleType();
        $schedule_value = $job->getDefaultScheduleValue();
        return [$schedule_type, $schedule_value];
    }

    /**
     * Get all possible jobs to take under surveillance.
     *
     * @return array <job_id => title>
     */
    public function getPossibleJobsToTakeUnderSurveillance()
    {
        $options = array();
        $plugin_jobs = $this->cron_manager->getPluginJobs();
        $data = $this->cron_manager->getCronJobData();

        foreach ($data as $key => $item) {
            $job = $this->cron_manager->getJobInstance(
                $item['job_id'],
                $item["component"],
                $item["class"],
                $item["path"]
            );
            $options[$item['job_id']] = $job->getTitle();
        }

        foreach ($plugin_jobs as $key => $item) {
            $job = $item[0];
            $item = $item[1];
            $options[$item['job_id']] = $job->getTitle();
        }

        asort($options);
        return $options;
    }
}
