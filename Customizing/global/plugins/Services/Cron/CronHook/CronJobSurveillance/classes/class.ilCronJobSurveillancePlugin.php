<?php
require_once("./Services/Cron/classes/class.ilCronHookPlugin.php");
require_once("./Services/Cron/classes/class.ilCronManager.php");
require_once("class.ilCronJobSurveillanceJob.php");
require_once(__DIR__ . "/../vendor/autoload.php");

use \CaT\Plugins\CronJobSurveillance\Config;
use \CaT\Plugins\CronJobSurveillance\Cron;
use \CaT\Plugins\CronJobSurveillance\Mail;
use \CaT\Plugins\CronJobSurveillance\Surveillance;

/**
 * Plugin base class. Keeps all information the plugin needs
 */
class ilCronJobSurveillancePlugin extends \ilCronHookPlugin
{
    /**
     * @var ilDBInterface
     */
    protected $g_db;

    /**
     * @var Cron\CronManager
     */
    protected $cron_manager;

    /**
     * @var Cron\CronJobFactory
     */
    protected $cron_job_factory;

    /**
     * @vsr Mailer
     */
    protected $mailer;

    /**
     * @var MailSetting[]
     */
    protected $mail_settings;

    /**
     * Config\ilDB
     */
    protected $config_db;

    /**
     * @var JobSettings[]
     */
    protected $job_settings;

    /**
     * @var CJSConfig
     */
    protected $cjs_config;

    public function __construct()
    {
        global $DIC;

        $this->g_db = $DIC->database();
        $this->job_settings = array();
        $this->dic = $DIC;

        parent::__construct();
    }

    /**
     * Get the name of the Plugin
     *
     * @return string
     */
    public function getPluginName()
    {
        return "CronJobSurveillance";
    }

    /**
     * Get an array with 1 to n numbers of cronjob objects
     *
     * @return ilCronJobSurveillanceJob[]
     */
    public function getCronJobInstances()
    {
        return array($this->getCronJobInstance(0));
    }

    /**
     * Get a single cronjob object
     *
     * @return ilCronJobSurveillanceJob
     */
    public function getCronJobInstance($a_job_id)
    {
        $surveillance = $this->getSurveillance();
        return new ilCronJobSurveillanceJob($this, $surveillance);
    }

    /**
     * Get a Surveillance instance.
     *
     * @return Surveillance\Surveillance
     */
    protected function getSurveillance()
    {
        if ($this->surveillance == null) {
            $mailer = $this->getMailer();
            $factory = $this->getCronJobFactory();
            $job_settings = $this->getJobSettingObjects();
            $this->surveillance = new Surveillance\Surveillance($job_settings, $factory, $mailer);
        }

        return $this->surveillance;
    }

    /**
     * Get a Mailer instance.
     *
     * @return Mail\Mailer
     */
    public function getMailer()
    {
        if ($this->mailer == null) {
            $this->mailer = new Mail\Mailer($this->getMailSettingObjects(), CLIENT_NAME);
        }
        return $this->mailer;
    }

    /**
     * Get MailSetting objects.
     *
     * @return MailSetting[]
     */
    public function getMailSettingObjects()
    {
        if ($this->mail_settings == null) {
            //TODO: change staticDB to new name after implementation
            $db = new Mail\staticDB();
            $this->mail_settings = $db->select();
        }
        return $this->mail_settings;
    }

    /**
     * Get a config db object.
     *
     * @return 	Config\ilDB
     */
    public function getConfigDB()
    {
        if ($this->config_db == null) {
            $this->config_db = new Config\ilDB($this->g_db);
        }
        return $this->config_db;
    }

    /**
     * Get JobSetting objects.
     *
     * @return JobSettings[]
     */
    public function getJobSettingObjects()
    {
        if ($this->job_settings == null) {
            $db = $this->getConfigDB();
            $this->job_settings = $db->select();
        }
        return $this->job_settings;
    }

    /**
     * Get an instance of ilCronJobFactory.
     *
     * @return Cron\ilCronJobFactory
     */
    public function getCronJobFactory()
    {
        if ($this->cron_job_factory == null) {
            $this->cron_job_factory = new Cron\ilCronJobFactory(
                $this->getCronManager(),
                $this->getScheduleTypes()
            );
        }
        return $this->cron_job_factory;
    }

    /**
     * Get an instance of CronManager.
     *
     * @return CronManager
     */
    public function getCronManager()
    {
        if ($this->cron_manager == null) {
            $this->cron_manager = new Cron\CronManager(
                new \ilCronManager(
                    $this->dic['ilSetting'],
                    $this->dic->logger()->root()
                )
            );
        }
        return $this->cron_manager;
    }

    /**
     * @return array
     */
    private function getScheduleTypes()
    {
        $types = array(
            \ilCronJob::SCHEDULE_TYPE_DAILY => 'P1D',
            \ilCronJob::SCHEDULE_TYPE_IN_MINUTES => 'PT%dM',
            \ilCronJob::SCHEDULE_TYPE_IN_HOURS => 'PT%dH',
            \ilCronJob::SCHEDULE_TYPE_IN_DAYS => 'P%dD',
            \ilCronJob::SCHEDULE_TYPE_WEEKLY => 'P1W',
            \ilCronJob::SCHEDULE_TYPE_MONTHLY => 'P1M',
            \ilCronJob::SCHEDULE_TYPE_QUARTERLY => 'P3M',
            \ilCronJob::SCHEDULE_TYPE_YEARLY => 'P1Y'
        );
        return $types;
    }

    /**
     * Closure to get txt from plugin
     */
    public function txtClosure()
    {
        return function ($code) {
            return $this->txt($code);
        };
    }
}
