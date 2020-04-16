<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;

class ilRepositoryObjectObjCancellationFeeReportSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/CancellationFeeReport/tests/Settings/DBSettingsRepositoryTest.php";
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/CancellationFeeReport/tests/Settings/SettingsTest.php";
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/CancellationFeeReport/tests/DICTest.php";
        $suite->addTestSuite(DBSettingsRepositoryTest::class);
        $suite->addTestSuite(SettingsTest::class);
        $suite->addTestSuite(DICTest::class);

        return $suite;
    }
}
