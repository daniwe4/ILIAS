<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDManagement\Config\UserDefinedFields;

class WBDManagementUDF
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $udf_id;

    public function __construct(string $name, int $udf_id)
    {
        $this->name = $name;
        $this->udf_id = $udf_id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getUdfId() : int
    {
        return $this->udf_id;
    }
}
