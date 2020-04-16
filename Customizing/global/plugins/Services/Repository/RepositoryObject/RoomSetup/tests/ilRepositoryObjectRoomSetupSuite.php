<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;
use CaT\Plugins\RoomSetup;

class ilRepositoryObjectRoomSetupSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        $suite->addTestSuite(RoomSetup\Equipment\EquipmentTest::class);
        $suite->addTestSuite(RoomSetup\ServiceOptions\ServiceOptionTest::class);

        return $suite;
    }
}
