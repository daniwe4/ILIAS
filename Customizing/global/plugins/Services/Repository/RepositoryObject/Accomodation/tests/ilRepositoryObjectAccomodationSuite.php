<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;

class ilRepositoryObjectAccomodationSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/Accomodation/tests/ObjectAccomodationTest.php";
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/Accomodation/tests/ObjSettingsDBTest.php";
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/Accomodation/tests/ObjSettingsTest.php";
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/Accomodation/tests/OvernightsTest.php";
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/Accomodation/tests/ReservationDBTest.php";
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/Accomodation/tests/ReservationTest.php";
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/Accomodation/tests/VenueTest.php";
        $suite->addTestSuite(AccomodationTest::class);
        $suite->addTestSuite(ObjSettingsDBTest::class);
        $suite->addTestSuite(ObjSettingsTest::class);
        $suite->addTestSuite(OvernightsTest::class);
        $suite->addTestSuite(ReservationDBTest::class);
        $suite->addTestSuite(ReservationTest::class);
        $suite->addTestSuite(AccomodationVenueTest::class);

        return $suite;
    }
}
