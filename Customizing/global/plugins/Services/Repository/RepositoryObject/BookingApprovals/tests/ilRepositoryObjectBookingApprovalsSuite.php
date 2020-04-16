<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;

class ilRepositoryObjectBookingApprovalsSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/BookingApprovals/tests/ApprovalActionsTest.php";
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/BookingApprovals/tests/DataObjectsTest.php";
        $suite->addTestSuite(ApprovalActionsTest::class);
        $suite->addTestSuite(Approval_DataObjectsTest::class);

        return $suite;
    }
}
