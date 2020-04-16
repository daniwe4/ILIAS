<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types = 1);

namespace CaT\Plugins\WBDCommunicator\Config\UDF;

class UDFDefinition
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @var int
     */
    protected $field_id;

    public function __construct(string $field, int $field_id)
    {
        $this->field = $field;
        $this->field_id = $field_id;
    }

    public function getField() : string
    {
        return $this->field;
    }

    public function getFieldId() : int
    {
        return $this->field_id;
    }
}
