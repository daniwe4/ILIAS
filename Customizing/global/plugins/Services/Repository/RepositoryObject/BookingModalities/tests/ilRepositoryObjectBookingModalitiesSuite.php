<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;

class ilRepositoryObjectBookingModalitiesSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/tests/BookingTest.php";
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/tests/MemberTest.php";
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/tests/ModalitiesMinMaxTest.php";
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/tests/StornoTest.php";
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/tests/WaitinglistTest.php";
        $suite->addTestSuite(BookingTest::class);
        $suite->addTestSuite(MemberTest::class);
        $suite->addTestSuite(ModalitiesMinMaxTest::class);
        $suite->addTestSuite(StornoTest::class);
        $suite->addTestSuite(WaitinglistTest::class);

        return $suite;
    }
}
