<?php

use CaT\Plugins\CronJobSurveillance\Config;
use CaT\Plugins\CronJobSurveillance\Cron;
use CaT\Plugins\CronJobSurveillance\Mail;
use CaT\Plugins\CronJobSurveillance\Surveillance;
use PHPUnit\Framework\TestCase;

class CronJobSurveillanceTest extends TestCase
{
    public $schedule_types = array(
        1 => 'P1D', //ilCronJob::SCHEDULE_TYPE_DAILY
        2 => 'PT%dM', //ilCronJob::SCHEDULE_TYPE_IN_MINUTES
        3 => 'PT%dH', //ilCronJob::SCHEDULE_TYPE_IN_HOURS
        4 => 'P%dD', //ilCronJob::SCHEDULE_TYPE_IN_DAYS
        5 => 'P1W', //ilCronJob::SCHEDULE_TYPE_WEEKLY
        6 => 'P1M', //ilCronJob::SCHEDULE_TYPE_MONTHLY
        7 => 'P3M', //ilCronJob::SCHEDULE_TYPE_QUARTERLY
        8 => 'P1Y', //ilCronJob::SCHEDULE_TYPE_YEARLY
    );


    public $job_settings = [];


    protected function getTestJobData()
    {
        return  array(
            'd1_finished_1h_ago' => array(
                'schedule_type' => 1, //daily
                'schedule_value' => 1,
                'job_result_ts' => date_create('now')->format('U'), //->sub(new \DateInterval('PT1H'))->format('U'),
                'running_ts' => 0,
                'alive_ts' => 0,
                'job_status' => 1,

                'test_should_fail' => false
            ),
            'd1_finished_1h_ago_but_deactivated' => array(
                'schedule_type' => 1, //daily
                'schedule_value' => 1,
                'job_result_ts' => date_create('now')->format('U'),
                'running_ts' => 0,
                'alive_ts' => 0,
                'job_status' => 0,

                'test_should_fail' => true
            ),

            'h1_finished_2h_ago' => array(
                'schedule_type' => 3, //hourly
                'schedule_value' => 1,
                'job_result_ts' => date_create('now')->sub(new \DateInterval('PT2H'))->format('U'),
                'running_ts' => 0,
                'alive_ts' => 0,
                'job_status' => 1,

                'test_should_fail' => true
            ),

            'm10_running_since_20m' => array(
                'schedule_type' => 2, //minutes
                'schedule_value' => 10,
                'job_result_ts' => date_create('now')->sub(new \DateInterval('PT30M'))->format('U'),
                'running_ts' => date_create('now')->sub(new \DateInterval('PT20M'))->format('U'),
                'alive_ts' => date_create('now')->sub(new \DateInterval('PT20M'))->format('U'),
                'job_status' => 1,

                'test_should_fail' => true //with tolerance < 20
            ),

            'y1_never_ran_or_reset' => array(
                'schedule_type' => 8, //yearly
                'schedule_value' => 10,
                'job_result_ts' => 0,
                'running_ts' => 0,
                'alive_ts' => 0,
                'job_status' => 1,

                'test_should_fail' => true
            ),

            'd1_deactivated' => array(
                'schedule_type' => 1, //daily
                'schedule_value' => 1,
                'job_result_ts' => 0,
                'running_ts' => 0,
                'alive_ts' => 0,
                'job_status' => 0,

                'test_should_fail' => true
            )
        );
    }

    //these will purely test behavior

    public function setUp() : void
    {
        $this->cronman = $this->getMockBuilder(Cron\CronManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->factory = new Cron\ilCronJobFactory(
            $this->cronman,
            $this->schedule_types
        );

        $this->mailer = $this->getMockBuilder(Mail\Mailer::class)
            ->disableOriginalConstructor()
            ->getMock();


        foreach ($this->getTestJobData() as $job_id => $row) {
            $this->job_settings[] = new Config\JobSetting($job_id);
        }

        $this->surveillance = new Surveillance\Surveillance(
            $this->job_settings,
            $this->factory,
            $this->mailer
        );
    }

    public function testFactory()
    {
        $this->cronman
            ->expects($this->once())
            ->method("getCronJobData")
            ->with('someJobId')
            ->willReturn(
                array(
                    array(
                        'schedule_type' => 1, //daily
                        'schedule_value' => 1,
                        'job_result_ts' => date_create('now')->format('U'), //->sub(new \DateInterval('PT1H'))->format('U'),
                        'running_ts' => 0,
                        'alive_ts' => 0,
                        'job_status' => 1
                    )
                )
            );

        $job = $this->factory->getCronJob('someJobId'); //run
        $this->assertInstanceOf(Cron\CronJob::class, $job);
    }

    public function testFactoryDeactivated()
    {
        $this->cronman
            ->expects($this->once())
            ->method("getCronJobData")
            ->with('someJobId')
            ->willReturn(
                array(
                    array(
                        'schedule_type' => 1,
                        'schedule_value' => 1,
                        'job_status' => 0
                    )
                )
            );

        $this->cronman
            ->expects($this->never())
            ->method("getFixScheduleForJob");

        $job = $this->factory->getCronJob('someJobId'); //run
        $this->assertNull($job);
    }

    public function testFactoryFixedSchedule()
    {
        $this->cronman
            ->expects($this->once())
            ->method("getCronJobData")
            ->with('someJobId')
            ->willReturn(
                array(
                    array(
                        'schedule_type' => null,
                        'schedule_value' => null,
                        'job_status' => 1,
                        'component' => 'SomeModule'
                    )
                )
            );

        $this->cronman
            ->expects($this->once())
            ->method("getFixScheduleForJob");

        $job = $this->factory->getCronJob('someJobId'); //run
        $this->assertNull($job);
    }



    public function testSurveillance()
    {
        /*
        the job's run-method will call exactly this:
            $this->surveillance->checkJobs();
        */

        $map = [];
        foreach ($this->getTestJobData() as $key => $value) {
            $map[] = array($key, array($value));
        }
        $this->cronman
            ->expects($this->exactly(count($this->job_settings))) //cronmanager should be asked for every job
            ->method('getCronJobData')
            ->will($this->returnValueMap($map));

        //we know, that there is at least one failed job
        $this->mailer
            ->expects($this->once())
            ->method('send');

        $this->surveillance->checkJobs();
    }

    public function testJobFailure()
    {
        $map = [];
        $expected_failures = 0;
        foreach ($this->getTestJobData() as $key => $value) {
            $map[] = array($key, array($value));
            if ($value['test_should_fail']) {
                $expected_failures += 1;
            }
        }
        $this->cronman
            ->expects($this->exactly(count($this->job_settings))) //cronmanager should be asked for every job
            ->method('getCronJobData')
            ->will($this->returnValueMap($map));

        $this->assertEquals($expected_failures, count($this->surveillance->getFailedJobs()));
    }
}
