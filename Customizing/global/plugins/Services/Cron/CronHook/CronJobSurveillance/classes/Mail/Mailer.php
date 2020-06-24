<?php
namespace CaT\Plugins\CronJobSurveillance\Mail;

use CaT\Plugins\CronJobSurveillance\Cron\CronJob;
use CaT\Plugins\CronJobSurveillance\Config\JobSetting;

/**
 *
 *
 * @author Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class Mailer
{

    /**
     * @var MailSetting[]
     */
    protected $settings;

    /**
     * @var string
     */
    protected $installation_id;

    /**
     * @param 	MailSetting[] 	$settings
     */
    public function __construct(array $settings, string $installation_id)
    {
        foreach ($settings as $entry) {
            if (!$entry instanceof MailSetting) {
                throw new \InvalidArgumentException("Parameter must be list of MailSetting", 1);
            }
        }
        $this->settings = $settings;
        $this->installation_id = $installation_id;
    }

    /**
     * @param 	mixed[] 	$jobs 	List with either CronJob or JobSetting
     * @return 	bool
     */
    public function send($jobs)
    {
        $subject = 'CronSurveillance from ' . $this->installation_id;
        $body = $this->prepareMail($jobs);

        foreach ($this->settings as $setting) {
            $result = mail(
                $setting->getRecipientAddress(),
                $subject,
                $body
            );
        }
        return true;
    }

    private function prepareMail($jobs)
    {
        $now = new \DateTime();

        $body = array(
            "Surveillance of crons:",
            "check conducted"
                . " for " . $this->installation_id
                . " on " . $now->format('Y-m-d H:i:s'),
            ""
        );

        foreach ($jobs as $job) {
            if ($job instanceof CronJob) {
                $msg = $job->getId()
                    . " last ran on "
                    . $job->getLastRunStart()->format('Y-m-d H:i:s')
                    . ". ";

                if ($job->getIsFinished()) {
                    $msg .= 'The job claims to be finished and did not run again yet.';
                } else {
                    $msg .= 'The job is STILL running - this is taking too long.';
                }
            }

            if ($job instanceof JobSetting) {
                $msg = $job->getJobId()
                    . " is under surveillance, but the job was deactivated or uninstalled.";
            }

            $body[] = $msg;
        }

        return implode("\n", $body);
    }
}
