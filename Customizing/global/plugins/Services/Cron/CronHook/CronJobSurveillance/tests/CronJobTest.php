<?php
/**
 * Test CronJob class
 */
use CaT\Plugins\CronJobSurveillance\Cron;
use PHPUnit\Framework\TestCase;

class CronJobTest extends TestCase
{
    public function setUp() : void
    {
        $this->id = 'crnId';
        $this->interval = new DateInterval('PT10M'); //ten minutes
        $this->finished = true;
        $this->last_run = new DateTime('2018-05-26 17:45:10');

        $this->job = new Cron\CronJob($this->id, $this->interval, $this->finished, $this->last_run);
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(Cron\CronJob::class, $this->job);
    }

    public function testGetters()
    {
        $this->assertEquals($this->id, $this->job->getId());
        $this->assertEquals($this->interval, $this->job->getInterval());
        $this->assertEquals($this->finished, $this->job->getIsFinished());
        $this->assertEquals($this->last_run, $this->job->getLastRunStart());

        $this->assertEquals(
            '2018-05-26 17:55:10',
            $this->job
                ->getLastRunStart()->add($this->job->getInterval())
                ->format('Y-m-d H:i:s')
        );
    }
}
