<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\EduTracking;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/EduTracking/classes/class.ilObjEduTracking.php";

class Mocks extends TestCase
{
    public function getMembersObjectMock() : MockObject
    {
        $obj = $this->createMock(\ilCourseParticipants::class);
        $obj
            ->expects($this->any())
            ->method('getTutors')
            ->willReturn([33])
        ;
        $obj
            ->expects($this->any())
            ->method('getAdmins')
            ->willReturn([33])
        ;

        return $obj;
    }

    public function getCrsMock()
    {
        $crs = $this->createMock(\ilObjCourse::class);
        $crs
            ->expects($this->any())
            ->method('getId')
            ->willReturn(22)
        ;

        $crs
            ->expects($this->any())
            ->method('getRefId')
            ->willReturn(33)
        ;
        $crs
            ->expects($this->any())
            ->method('getMembersObject')
            ->willReturn($this->getMembersObjectMock())
        ;

        return $crs;
    }

    public function getEduTrackingObjectMock() : MockObject
    {
        $obj = $this->createMock(\ilObjEduTracking::class);
        $obj
            ->expects($this->any())
            ->method('getId')
            ->willReturn(22)
        ;

        $obj
            ->expects($this->any())
            ->method('getParentCourse')
            ->willReturn($this->getCrsMock())
        ;

        return $obj;
    }

    public function getIliasDBMock() : MockObject
    {
        return $this->createMock(\ilDBInterface::class);
    }

    public function getIliasAppEventHandler() : MockObject
    {
        return $this->createMock(\ilAppEventHandler::class);
    }

    public function getIliasTree() : MockObject
    {
        return $this->createMock(\ilTree::class);
    }
}
