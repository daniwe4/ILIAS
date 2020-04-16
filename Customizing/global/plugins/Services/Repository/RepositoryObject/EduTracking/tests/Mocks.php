<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\EduTracking;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/EduTracking/classes/class.ilObjEduTracking.php";

class Mocks extends TestCase
{
    public function getCrsMock()
    {
        $crs = $this->createMock(\ilObjCourse::class);
        $crs
            ->expects($this->any())
            ->method('getId')
            ->willReturn(22)
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
