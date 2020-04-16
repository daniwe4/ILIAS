<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDManagement\Config\UserDefinedFields;

use PHPUnit\Framework\TestCase;

class WBDManagementUDFTest extends TestCase
{
    public function test_create()
    {
        $name = "Testname";
        $udf_id = 33;

        $wbd_management_udf = new WBDManagementUDF($name, $udf_id);

        $this->assertEquals($name, $wbd_management_udf->getName());
        $this->assertEquals($udf_id, $wbd_management_udf->getUdfId());
    }
}
