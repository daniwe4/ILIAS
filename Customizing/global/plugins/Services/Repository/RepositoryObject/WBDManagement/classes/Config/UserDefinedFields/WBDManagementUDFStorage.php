<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDManagement\Config\UserDefinedFields;

interface WBDManagementUDFStorage
{
    /**
     * @param WBDManagementUDF[] $udfs
     */
    public function save(array $udfs);

    /**
     * @param string[] $keys
     *
     * @return WBDManagementUDF[]
     */
    public function readAll(array $keys) : array;

    public function read(string $key) : WBDManagementUDF;
}
