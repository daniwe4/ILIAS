<?php

use PHPUnit\Framework\TestCase;
use CaT\Plugins\ScheduledEvents as PSE;
use ILIAS\TMS\ScheduledEvents as TSE;

/**
 * @group needsInstalledILIAS
 */
class ScheduledEventsJobTest extends TestCase
{
    public function setUp() : void
    {
        require_once 'Services/PHPUnit/classes/class.ilUnitUtil.php';


        require_once 'Customizing/global/plugins/Services/Cron/CronHook/ScheduledEvents/classes/class.ilScheduledEventsPlugin.php';
        require_once 'Customizing/global/plugins/Services/Cron/CronHook/ScheduledEvents/classes/class.ilScheduledEventsJob.php';
        ilUnitUtil::performInitialisation();
    }

    public function test_init()
    {
        $job = new ilScheduledEventsJob(
            new ilScheduledEventsPlugin(),
            $this->getEventsDBMock(),
            $this->getEventHaldlerMock()
        );
        $this->assertInstanceOf(ilScheduledEventsJob::class, $job);
    }

    /**
     * @depends test_init
     */
    public function test_run()
    {
        require_once "Services/Cron/classes/class.ilCronJobResult.php";
        $eh_mock = $this->getEventHaldlerMock();
        $eh_mock->expects($this->exactly(2))
            ->method('raise')
            ->withConsecutive(
                [$this->equalTo('component_1'), $this->equalTo('event_1'), $this->equalTo([])],
                [$this->equalTo('component_2'), $this->equalTo('event_2'), $this->equalTo(['p21' => 'v21','p22' => 'v22'])]
            );
        $job = new ilScheduledEventsJob(
            new ilScheduledEventsPlugin(),
            $this->getEventsDBMock(),
            $eh_mock
        );
        $this->assertInstanceOf(\ilCronJobResult::class, $job->run());
    }

    protected function getEventsDBMock()
    {
        $mock = $this->createMock(TSE\DB::class);
        $date_time = \DateTime::createFromFormat('Y-m-d', '2001-01-01');

        $event_1 = new TSE\Event(
            1,
            11,
            $date_time,
            'component_1',
            'event_1',
            []
        );
        $event_2 = new TSE\Event(
            2,
            22,
            $date_time,
            'component_2',
            'event_2',
            ['p21' => 'v21','p22' => 'v22']
        );

        $mock->method('getAllDue')
            ->willReturn([$event_1,$event_2]);
        return $mock;
    }

    protected function getEventHaldlerMock()
    {
        require_once 'Services/EventHandling/classes/class.ilAppEventHandler.php';
        return $this->getMockBuilder(ilAppEventHandler::class)
                     ->setMethods(['raise'])
                     ->getMock();
    }
}
