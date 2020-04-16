<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types = 1);

namespace CaT\Plugins\WBDCommunicator\Config\Tgic;

class Tgic
{
    /**
     * @var string
     */
    protected $partner_id;

    /**
     * @var string
     */
    protected $certstore;

    /**
     * @var string
     */
    protected $password;

    public function __construct(
        string $partner_id,
        string $certstore,
        string $password
    ) {
        $this->partner_id = $partner_id;
        $this->certstore = $certstore;
        $this->password = $password;
    }

    public function getPartnerId() : string
    {
        return $this->partner_id;
    }

    public function getCertstore() : string
    {
        return $this->certstore;
    }

    public function getPassword() : string
    {
        return $this->password;
    }
}
