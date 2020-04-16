<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;

class ilRepositoryObjectCourseMemberSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMember/tests/LPOptionTest.php";
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMember/tests/MemberTest.php";
        $suite->addTestSuite(LPOptionTest::class);
        $suite->addTestSuite(CourseMemberTest::class);

        return $suite;
    }
}
