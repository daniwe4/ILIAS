<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;
use CaT\Plugins\EduBiography;

class ilRepositoryObjectEduBiographySuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/EduBiography/tests/SettingsRepositoryTest.php";
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/EduBiography/tests/SettingsTest.php";
        $suite->addTestSuite(EduBiography\Config\OverviewCertificate\Activation\ActiveTest::class);
        $suite->addTestSuite(EduBiography\Config\OverviewCertificate\Activation\ilDBTest::class);
        $suite->addTestSuite(EduBiography\Config\OverviewCertificate\Schedules\SettingsRepositoryTest::class);
        $suite->addTestSuite(SettingsRepositoryTest::class);
        $suite->addTestSuite(SettingsTest::class);

        return $suite;
    }
}
