<?php

namespace CaT\Plugins\CronJobSurveillance\Surveillance;

use CaT\Plugins\CronJobSurveillance\Config\JobSetting;
use CaT\Plugins\CronJobSurveillance\Cron\CronJobFactory;
use CaT\Plugins\CronJobSurveillance\Mail\Mailer;

/**
 * Test, if entries of a set of jobs (defined by job_settings) are to be
 * considered as "failed" and react by sending a mail.
 *
 * @author Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class Surveillance
{

    /**
     * @var JobSetting[]
     */
    private $job_settings;

    /**
     * @var CronJobFactory
     */
    private $job_factory;

    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @param 	JobSetting[] 	$job_settings
     * @param 	CronJobFactory 	$job_factory
     * @param 	Mailer 	$mailer
     */
    public function __construct(array $job_settings, CronJobFactory $job_factory, Mailer $mailer)
    {
        foreach ($job_settings as $entry) {
            if (!$entry instanceof JobSetting) {
                throw new \InvalidArgumentException("Parameter must be list of JobSetting", 1);
            }
        }
        $this->job_settings = $job_settings;
        $this->job_factory = $job_factory;
        $this->mailer = $mailer;
    }

    /**
     * Run check and send mail with failed jobs.
     *
     * @return 	void
     */
    public function checkJobs()
    {
        $failed = $this->getFailedJobs();
        if ($failed) {
            $this->mailer->send($failed);
        }
    }

    /**
     * @return 	mixed[] 	CronJob | JobSetting
     */
    public function getFailedJobs()
    {
        $failed = array();
        foreach ($this->job_settings as $job_setting) {
            $job = $this->job_factory->getCronJob($job_setting->getJobId());
            if ($job) {
                if ($this->considerAsFailed($job, $job_setting->getTolerance())) {
                    $failed[] = $job;
                }
            } else {
                $failed[] = $job_setting;
            }
        }
        return $failed;
    }

    /**
     * @param 	CronJob 	$job
     * @param 	int 		$estimated_job_time 	number of minutes this job is supposed to take
     * @return 	bool
     */
    public function considerAsFailed($job, $estimated_job_time = 0)
    {
        $tolerance = new \DateInterval('PT' . $estimated_job_time . 'M');
        $last_start = $job->getLastRunStart();
        if (is_null($last_start)) {
            return false;
        }
        $anticipated_next = $last_start
            ->add($tolerance)
            ->add($job->getInterval());

        return $anticipated_next < new \DateTime('now');
    }
}
