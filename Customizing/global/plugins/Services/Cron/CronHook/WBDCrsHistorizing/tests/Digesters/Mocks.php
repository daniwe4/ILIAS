<?php


/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDCrsHistorizing\Digesters;

use PHPUnit\Framework\TestCase;

class Mocks extends TestCase
{
    public function getCrsMock()
    {
        $crs = $this->createMock(\ilObjCourse::class);
        $crs
            ->expects($this->any())
            ->method('getId')
            ->willReturn(22);

        return $crs;
    }
}
