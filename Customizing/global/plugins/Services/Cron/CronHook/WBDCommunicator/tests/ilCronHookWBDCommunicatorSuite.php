<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;
use CaT\Plugins\WBDCommunicator;

class ilCronHookWBDCommunicatorSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        // TODO: Add WBDCommunicator to composer.json
//        $suite->addTestSuite(WBDCommunicator\Config\Connection\ConnectionTest::class);
//        $suite->addTestSuite(WBDCommunicator\Config\Tgic\TgicTest::class);
//        $suite->addTestSuite(WBDCommunicator\Config\UDF\UDFDefinitionTest::class);
//        $suite->addTestSuite(WBDCommunicator\Config\WBD\ilDBTest::class);
//        $suite->addTestSuite(WBDCommunicator\Config\WBD\SystemTest::class);
//        $suite->addTestSuite(WBDCommunicator\Jobs\ilCancelParticipationsJobTest::class);
//        $suite->addTestSuite(WBDCommunicator\Jobs\ilReportParticipationsJobTest::class);
//        $suite->addTestSuite(WBDCommunicator\Jobs\ilRequestParticipationsJobTest::class);
//        $suite->addTestSuite(WBDCommunicator\SOAP\UDFDefinitionTest::class);

        return $suite;
    }
}
