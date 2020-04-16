<?php

use PHPUnit\Framework\TestCase;
use CaT\Plugins\ScheduledEvents as PSE;
use ILIAS\TMS\ScheduledEvents as TSE;

/**
 * @group needsInstalledILIAS
 */
class ilActionsTest extends TestCase
{
    public function setUp() : void
    {
        require_once 'Services/PHPUnit/classes/class.ilUnitUtil.php';
        ilUnitUtil::performInitialisation();
    }

    public function test_init()
    {
        $actions = new PSE\ilActions($this->getEventsDBMock());
        $this->assertInstanceOf(PSE\ilActions::class, $actions);
        return $actions;
    }


    /**
     * @depends test_init
     */
    public function test_all_events()
    {
        $events = $this->test_init()->getAllEvents();
        $event_datas = [];
        foreach ($events as $event) {
            $event_datas[$event->getId()]
                = [
                    $event->getId()
                    ,$event->getIssuerRef()
                    ,$event->getDue()->format('Y-m-d')
                    ,$event->getComponent()
                    ,$event->getEvent()
                    ,$event->getParameters()
                ];
        }
        $this->assertEquals(
            $event_datas,
            [1 => [
                    1
                    ,11
                    ,'2001-01-01'
                    ,'component_1'
                    ,'event_1'
                    ,['p11' => 'v11']
                ]
            ,2 => [
                    2
                    ,22
                    ,'2001-01-01'
                    ,'component_2'
                    ,'event_2'
                    ,['p21' => 'v21','p22' => 'v22']
                ]
            ,3 => [
                    3
                    ,33
                    ,'2001-01-01'
                    ,'component_3'
                    ,'event_3'
                    ,[]
                ]
            ]
        );
    }

    /**
     * @depends test_init
     */
    public function test_due_events()
    {
        $events = $this->test_init()->getAllDueEvents();
        $event_datas = [];
        foreach ($events as $event) {
            $event_datas[$event->getId()]
                = [
                    $event->getId()
                    ,$event->getIssuerRef()
                    ,$event->getDue()->format('Y-m-d')
                    ,$event->getComponent()
                    ,$event->getEvent()
                    ,$event->getParameters()
                ];
        }
        $this->assertEquals(
            $event_datas,
            [1 => [
                    1
                    ,11
                    ,'2001-01-01'
                    ,'component_1'
                    ,'event_1'
                    ,['p11' => 'v11']
                ]
            ,2 => [
                    2
                    ,22
                    ,'2001-01-01'
                    ,'component_2'
                    ,'event_2'
                    ,['p21' => 'v21','p22' => 'v22']
                ]
            ]
        );
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
            ['p11' => 'v11']
        );
        $event_2 = new TSE\Event(
            2,
            22,
            $date_time,
            'component_2',
            'event_2',
            ['p21' => 'v21','p22' => 'v22']
        );
        $event_3 = new TSE\Event(
            3,
            33,
            $date_time,
            'component_3',
            'event_3',
            []
        );

        $mock->method('getAll')
            ->willReturn(
                [$event_1,$event_2,$event_3]
            );
        $mock->method('getAllDue')
            ->willReturn(
                [$event_1,$event_2]
            );
        return $mock;
    }
}
