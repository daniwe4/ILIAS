<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;

class ilEventHookOrguByMailDomainSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        require_once "Customizing/global/plugins/Services/EventHandling/EventHook/OrguByMailDomain/tests/Configuration/ConfigurationTest.php";
        require_once "Customizing/global/plugins/Services/EventHandling/EventHook/OrguByMailDomain/tests/Configuration/RepositoryTest.php";
        $suite->addTestSuite(ConfigurationTest::class);
        $suite->addTestSuite(RepositoryTest::class);

        return $suite;
    }
}
