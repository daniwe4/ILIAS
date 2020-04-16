<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;
use CaT\Plugins\TrainingDemandAdvanced\Settings;

class ilRepositoryObjectTrainingDemandAdvancedSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        $suite->addTestSuite(Settings\DBSettingsRepositoryTest::class);
        $suite->addTestSuite(Settings\SettingsTest::class);

        return $suite;
    }
}
