<?php
/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;

class ilServicesSetupSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        require_once __DIR__ . '/ilSetupConfigTest.php';
        $suite->addTestSuite(ilSetupConfigTest::class);

        return $suite;
    }
}
