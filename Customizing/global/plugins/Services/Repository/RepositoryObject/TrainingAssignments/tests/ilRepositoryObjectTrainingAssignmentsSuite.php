<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;
use CaT\Plugins\TrainingAssignments;

class ilRepositoryObjectTrainingAssignmentsSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/TrainingAssignments/tests/AssignedTrainingsTest.php";
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/TrainingAssignments/tests/DBTest.php";
        $suite->addTestSuite(AssignedTrainingsTest::class);
        $suite->addTestSuite(DBTest::class);

        return $suite;
    }
}
