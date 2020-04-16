<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

use CaT\Plugins\BookingApprovals\Approvals;
use CaT\Plugins\BookingApprovals\Events;
use CaT\Plugins\BookingApprovals\Utils;
use PHPUnit\Framework\TestCase;

class ApprovalActionsTest extends TestCase
{
    public function createDBMock()
    {
        return $this->getMockBuilder(Approvals\ApprovalDB::class)
            ->setMethods(
                [
                    'createBookingRequest',
                    'updateBookingRequest',
                    'getBookingRequests',
                    'createApproval',
                    'updateApproval',
                    'getApprovalsForRequest',
                    'hasUserOpenRequestOnCourse'
                ]
            )
            ->getMock();
    }

    public function createEventsMock()
    {
        return $this->getMockBuilder(Events\Events::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'fireEventBookingRequestCreated',
                    'fireEventBookingRequestStateApproved',
                    'fireEventBookingRequestStateDeclined',
                    'fireEventApprovalApproved',
                    'fireEventApprovalDeclined',
                    'lookupObjId'
                ]
            )
            ->getMock();
    }

    public function createOrguUtilsMock()
    {
        return $this->getMockBuilder(Utils\OrguUtils::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getAllPositionsForUserId',
                    'getNextHigherUsersWithPositionForUser'
                ]
            )
            ->getMock();
    }

    public function setUp() : void
    {
        $this->db = $this->createDBMock();
        $this->events = $this->createEventsMock();
        $this->orgu_utils = $this->createOrguUtilsMock();
    }

    public function getActions()
    {
        return new Approvals\Actions($this->db, $this->events, $this->orgu_utils);
    }


    public function test_construct()
    {
        $this->assertInstanceOf(
            Approvals\Actions::class,
            $this->getActions()
        );
    }


    public function testGetBookingRequests()
    {
        $this->db
            ->expects($this->once())
            ->method("getBookingRequests")
            ->willReturn([]);
        $actions = $this->getActions();
        $actions->getBookingRequests([]);
    }


    public function testRequestBooking()
    {
        $approvals = [1,2,3];
        $br = new Approvals\BookingRequest(1, 6, 6, 667, 20, new \DateTime(), '');
        $this->db
            ->expects($this->once())
            ->method("createBookingRequest")
            ->willReturn(
                $br
            );

        $this->db
            ->expects($this->exactly(count($approvals)))
            ->method("createApproval");

        $actions = $this->getActions();
        $actions->requestBooking(6, 6, 667, 20, $approvals, '');
    }


    public function testApprove()
    {
        $approval = $this->getMockBuilder(Approvals\Approval::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->setMethods([
                'withState',
                'getBookingRequestId',
                'getState'
            ])
            ->getMock();

        $approval
            ->expects($this->once())
            ->method("getBookingRequestId")
            ->willReturn(1);

        $approval
            ->expects($this->once())
            ->method("withState")
            ->willReturn($approval);

        $this->db
            ->expects($this->once())
            ->method("updateApproval");

        $this->db
            ->expects($this->once())
            ->method("getApprovalsForRequest")
            ->willReturn([$approval]);
        $this->db
            ->expects($this->once())
            ->method("getBookingRequests")
            ->willReturn([
                new Approvals\BookingRequest(1, 6, 6, 667, 20, new \DateTime(), '')
            ]);

        $actions = $this->getActions();
        $actions->approve($approval, 7);
    }
}
