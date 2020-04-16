<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;

class ilRepositoryObjectAgendaSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/Agenda/tests/AgendaEntry/AgendaEntryDBTest.php";
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/Agenda/tests/AgendaEntry/AgendaEntryTest.php";
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/Agenda/tests/Config/Blocks/BlockTest.php";
        $suite->addTestSuite(AgendaEntryDBTest::class);
        $suite->addTestSuite(AgendaEntryTest::class);
        $suite->addTestSuite(BlockTest::class);

        return $suite;
    }
}
