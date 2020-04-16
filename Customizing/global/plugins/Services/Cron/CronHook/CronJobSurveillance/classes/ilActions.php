<?php
namespace CaT\Plugins\CronJobSurveillance;

use CaT\Plugins\CronJobSurveillance\Config;
use CaT\Plugins\CronJobSurveillance\Mail;

/**
 * Communication class between front- and backend.
 * E.g. GUI only use this class to get information from ILIAS DB.
 */
class ilActions
{

    /**
     * @var 	Cron\DB
     */
    protected $jobsettings_db;

    /**
     * @var 	Mail\DB
     */
    protected $mailsettings_db;

    public function __construct(Config\DB $job_db, Mail\DB $mail_db)
    {
        $this->jobsettings_db = $job_db;
        $this->mailsettings_db = $mail_db;
    }

    /**
     * @return 	JobSetting[]
     */
    public function getJobSettings()
    {
        return $this->jobsettings_db->select();
    }
    /**
     * @return 	MailSetting[]
     */
    public function getMailSettings()
    {
        return $this->mailsettings_db->select();
    }
}
