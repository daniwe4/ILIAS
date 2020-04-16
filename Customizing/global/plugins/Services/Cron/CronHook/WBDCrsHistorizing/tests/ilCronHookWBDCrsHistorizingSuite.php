<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;
use CaT\Plugins\WBDCrsHistorizing;

class ilCronHookWBDCrsHistorizingSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        $suite->addTestSuite(WBDCrsHistorizing\Digesters\CourseIdDigesterTest::class);
        $suite->addTestSuite(WBDCrsHistorizing\Digesters\CreatedTSDigesterTest::class);
        $suite->addTestSuite(WBDCrsHistorizing\Digesters\DeletedDigesterTest::class);
        $suite->addTestSuite(WBDCrsHistorizing\HistCases\WBDCrsTest::class);

        return $suite;
    }
}
