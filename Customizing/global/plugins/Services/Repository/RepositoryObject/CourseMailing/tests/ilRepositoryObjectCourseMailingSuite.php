<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;

class ilRepositoryObjectCourseMailingSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMailing/tests/RoleMappingTest.php";
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMailing/tests/SettingTest.php";
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMailing/tests/SurroundingsTest.php";
        $suite->addTestSuite(RoleMappingTest::class);
        $suite->addTestSuite(SettingTest::class);
        $suite->addTestSuite(SurroundingsTest::class);

        return $suite;
    }
}
