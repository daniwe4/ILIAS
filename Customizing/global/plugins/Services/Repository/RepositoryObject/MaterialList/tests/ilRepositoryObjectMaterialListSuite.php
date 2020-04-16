<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;
use CaT\Plugins\MaterialList;

class ilRepositoryObjectMaterialListSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        $suite->addTestSuite(MaterialList\RPC\Procedures\Course\GetTitleTest::class);
        $suite->addTestSuite(MaterialList\RPC\FolderReadTest::class);

        return $suite;
    }
}
