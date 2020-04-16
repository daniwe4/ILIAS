<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;
use CaT\Plugins\AgendaItemPool;

class ilRepositoryObjectAgendaItemPoolSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        $suite->addTestSuite(AgendaItemPool\AgendaItem\AgendaItemTest::class);
        $suite->addTestSuite(AgendaItemPool\AgendaItem\ilDBTest::class);
        $suite->addTestSuite(AgendaItemPool\Options\OptionsTest::class);
        $suite->addTestSuite(AgendaItemPool\Settings\ilDBTest::class);
        $suite->addTestSuite(AgendaItemPool\Settings\SettingsTest::class);

        return $suite;
    }
}
