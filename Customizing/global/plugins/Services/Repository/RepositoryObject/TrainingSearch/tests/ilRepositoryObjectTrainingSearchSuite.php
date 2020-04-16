<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;
use CaT\Plugins\TrainingSearch;

class ilRepositoryObjectTrainingSearchSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        $suite->addTestSuite(TrainingSearch\Search\ilCachingDBTest::class);
        $suite->addTestSuite(TrainingSearch\Search\OptionsTest::class);
        $suite->addTestSuite(TrainingSearch\Settings\DBTest::class);
        $suite->addTestSuite(TrainingSearch\Settings\SettingsTest::class);

        return $suite;
    }
}
