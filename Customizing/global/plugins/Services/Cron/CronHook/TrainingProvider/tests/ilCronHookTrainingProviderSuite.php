<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;
use CaT\Plugins\TrainingProvider;

class ilCronHookTrainingProviderSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        $suite->addTestSuite(TrainingProvider\Provider\ProviderTest::class);
        $suite->addTestSuite(TrainingProvider\Provider\ilDBTest::class);
        $suite->addTestSuite(TrainingProvider\ProviderAssignment\CustomAssignmentTest::class);
        $suite->addTestSuite(TrainingProvider\ProviderAssignment\ListAssignmentTest::class);
        $suite->addTestSuite(TrainingProvider\ProviderAssignment\ilDBTest::class);
        $suite->addTestSuite(TrainingProvider\Tags\TagTest::class);
        $suite->addTestSuite(TrainingProvider\Tags\ilDBTest::class);
        $suite->addTestSuite(TrainingProvider\Trainer\TrainerTest::class);
        $suite->addTestSuite(TrainingProvider\Trainer\ilDBTest::class);

        return $suite;
    }
}
