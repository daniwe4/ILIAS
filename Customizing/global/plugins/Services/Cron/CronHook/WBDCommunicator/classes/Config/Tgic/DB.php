<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types = 1);

namespace CaT\Plugins\WBDCommunicator\Config\Tgic;

interface DB
{
    public function saveTgicSettings(
        string $partner_id,
        string $certstore,
        string $password
    ) : Tgic;

    /**
     * @throws \LogicException if no settings where found
     */
    public function getTgicSettings() : Tgic;
}
