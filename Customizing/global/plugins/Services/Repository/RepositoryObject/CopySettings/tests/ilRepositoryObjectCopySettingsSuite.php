<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;

class ilRepositoryObjectCopySettingsSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/CopySettings/tests/CopySettingsForChildrenTest.php";
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/CopySettings/tests/DetermineParentContainerTest.php";
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/CopySettings/tests/RenameContainerTitleTest.php";
        $suite->addTestSuite(CopySettingsForChildrenTest::class);
        $suite->addTestSuite(DetermineParentContainerTest::class);
        $suite->addTestSuite(RenameContainerTitleTest::class);

        return $suite;
    }
}
