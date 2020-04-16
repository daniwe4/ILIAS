<?php

use CaT\Plugins\CronJobSurveillance\Cron;
use PHPUnit\Framework\TestCase;

require_once('mockCronMan.php');


class CronJobFactoryTest extends TestCase
{

    //these are tests against an implementation with data
    public function setUp() : void
    {
        $cronman = new mockCronMan();
        $this->cronman = $cronman;
        $this->job_ids = $cronman->getJobIds();
        $this->schedule_types = $cronman->getScheduleTypes();

        $this->factory = new Cron\ilCronJobFactory(
            new Cron\CronManager($cronman),
            $this->schedule_types
        );
    }

    public function testSurveillanceGetJob()
    {
        foreach ($this->job_ids as $id) {
            $job = $this->factory->getCronJob($id);
            if (is_null($this->cronman->jobs[$id]['schedule_type']) && is_null($this->cronman->jobs[$id]['schedule_value'])
                || $this->cronman->jobs[$id]['job_status'] == 0
            ) {
                $this->assertNull($job);
            } else {
                $this->assertInstanceOf(Cron\CronJob::class, $job);
            }
        }
    }

    public function testIntervalFormat()
    {
        foreach ($this->job_ids as $id) {
            $job = $this->factory->getCronJob($id);
            if (!is_null($job)) {
                $interval = $job->getInterval();
                $this->assertInstanceOf(\DateInterval::class, $interval);
            }
        }
    }

    public function testInvalidJobId()
    {
        $job = $this->factory->getCronJob('this_id_does_not_exist');
        $this->assertEquals(null, $job);
    }
}
