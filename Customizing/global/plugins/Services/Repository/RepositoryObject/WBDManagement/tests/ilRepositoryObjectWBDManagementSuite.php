<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;
use CaT\Plugins\WBDManagement;

class ilRepositoryObjectWBDManagementSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        $suite->addTestSuite(WBDManagement\Config\UserDefinedFields\WBDManagementUDFTest::class);
        $suite->addTestSuite(WBDManagement\GutBeraten\WBDDataTest::class);
        $suite->addTestSuite(WBDManagement\Settings\WBDManagementTest::class);

        return $suite;
    }
}
