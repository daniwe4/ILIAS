<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;
use CaT\Plugins\EduTracking;

class ilRepositoryObjectEduTrackingSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        $suite->addTestSuite(EduTracking\Purposes\GTI\ilDBTest::class);
        $suite->addTestSuite(EduTracking\Purposes\GTI\GTITest::class);
        $suite->addTestSuite(EduTracking\Purposes\GTI\Configuration\CategoryGTITest::class);
        $suite->addTestSuite(EduTracking\Purposes\GTI\Configuration\ConfigGTITest::class);
        $suite->addTestSuite(EduTracking\Purposes\GTI\Configuration\ilDBTest::class);
        $suite->addTestSuite(EduTracking\Purposes\IDD\IDDTest::class);
        $suite->addTestSuite(EduTracking\Purposes\IDD\ilDBTest::class);
        $suite->addTestSuite(EduTracking\Purposes\IDD\Configuration\ConfigIDDTest::class);
        $suite->addTestSuite(EduTracking\Purposes\IDD\Configuration\ilDBTest::class);
        $suite->addTestSuite(EduTracking\Purposes\WBD\WBDTest::class);
        $suite->addTestSuite(EduTracking\Purposes\WBD\ilDBTest::class);
        $suite->addTestSuite(EduTracking\Purposes\WBD\Configuration\ConfigWBDTest::class);
        $suite->addTestSuite(EduTracking\Purposes\WBD\Configuration\ilDBTest::class);
        $suite->addTestSuite(EduTracking\Purposes\WBD\WBDDataInterfaceTest::class);

        return $suite;
    }
}
