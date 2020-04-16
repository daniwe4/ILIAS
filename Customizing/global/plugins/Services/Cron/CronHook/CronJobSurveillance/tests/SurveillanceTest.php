<?php

use CaT\Plugins\CronJobSurveillance\Config;
use CaT\Plugins\CronJobSurveillance\Cron;
use CaT\Plugins\CronJobSurveillance\Mail;
use CaT\Plugins\CronJobSurveillance\Surveillance\Surveillance;
use PHPUnit\Framework\TestCase;

require_once('mockCronMan.php');


class SurveillanceTest extends TestCase
{
    public function setUp() : void
    {
        $this->cronman = new mockCronMan();

        $this->factory = new Cron\ilCronJobFactory(
            new Cron\CronManager($this->cronman),
            $this->cronman->getScheduleTypes()
        );

        $this->settings = array();
        foreach ($this->cronman->getJobIds() as $id) {
            $this->settings[] = new Config\JobSetting($id);
        }

        $this->mail_settings = array(new Mail\MailSetting('an@email.de'));
        $this->mailer = new Mail\Mailer($this->mail_settings, 'UnitTest');

        $this->surveillance = new Surveillance($this->settings, $this->factory, $this->mailer);
    }

    public function testContructionOfSurveillance()
    {
        $this->assertInstanceOf(Surveillance::class, $this->surveillance);
    }

    public function testWrongContructionOfSurveillance()
    {
        try {
            $surveillance = new Surveillance(array(1,2,3), $this->factory, $this->mailer);
            $this->assertTrue(false);
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testFailureConsiderationWithData()
    {
        foreach ($this->cronman->getJobIds() as $job_id) {
            $job = $this->factory->getCronJob($job_id);
            if (!is_null($job)) {
                $this->assertEquals(
                    $this->cronman->jobs[$job_id]['test_should_fail'],
                    $this->surveillance->considerAsFailed($job)
                );
            }
        }
    }

    public function testFailureConsiderationWithTolerance()
    {
        $job = $this->factory->getCronJob('m10_running_since_20m');
        $this->assertEquals(
            true,
            $this->surveillance->considerAsFailed($job, 19)
        );
        $this->assertEquals(
            false,
            $this->surveillance->considerAsFailed($job, 21)//with tolerance >20, this is OK
        );
    }

    public function testBulkFailure()
    {
        $expected = array();
        foreach ($this->cronman->getJobIds() as $job_id) {
            if ($this->cronman->jobs[$job_id]['test_should_fail']) {
                if ($this->cronman->jobs[$job_id]['job_status'] == 1) {
                    $expected[] = $this->factory->getCronJob($job_id);
                } else {
                    $expected[] = new Config\JobSetting($job_id);
                }
            }
        }
        $this->assertEquals(
            $expected,
            $this->surveillance->getFailedJobs()
        );
    }
}
