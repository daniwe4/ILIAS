<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types = 1);

namespace CaT\Plugins\WBDCommunicator\Config\UDF;

interface DB
{
    const KEY_GUTBERATEN_ID = "gutberaten_id";
    const KEY_ANNOUNCE_ID = "announce_id";

    /**
     * @return UDFDefinition|null
     */
    public function getUDFFieldIdForWBDID();
    public function saveUDFFieldIdForWBDID(int $field_id);

    /**
     * @return UDFDefinition|null
     */
    public function getUDFFieldIdForStatus();
    public function saveUDFFieldIdForStatus(int $field_id);

    /**
     * @return UDFDefinition[]
     */
    public function getUDFDefinitions() : array;
}
