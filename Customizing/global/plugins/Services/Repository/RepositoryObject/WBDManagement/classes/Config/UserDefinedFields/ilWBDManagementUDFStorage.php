<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDManagement\Config\UserDefinedFields;

class ilWBDManagementUDFStorage implements WBDManagementUDFStorage
{
    /**
     * @var \ilSetting
     */
    protected $settings;

    public function __construct(\ilSetting $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @inheritDoc
     */
    public function save(array $udfs)
    {
        foreach ($udfs as $udf) {
            $this->settings->set($udf->getName(), $udf->getUdfId());
        }
    }

    /**
     * @inheritDoc
     */
    public function readAll(array $keys) : array
    {
        $udfs = array();
        foreach ($keys as $key) {
            $udf_id = (int) $this->settings->get($key, 0);
            $udfs[] = new WBDManagementUDF($key, $udf_id);
        }
        return $udfs;
    }

    public function read(string $key) : WBDManagementUDF
    {
        $udf_id = (int) $this->settings->get($key, 0);
        return new WBDManagementUDF($key, $udf_id);
    }
}
