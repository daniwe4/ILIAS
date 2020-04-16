<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;
use CaT\Plugins\Accounting;

class ilRepositoryObjectAccountingSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/Accounting/tests/Fees/CancellationFee/CancellationFeeTest.php";
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/Accounting/tests/Fees/Fee/FeeTest.php";
        $suite->addTestSuite(Accounting\Config\Cancellation\Scale\ScaleTest::class);
        $suite->addTestSuite(CancellationFeeTest::class);
        $suite->addTestSuite(FeeTest::class);

        return $suite;
    }
}
