<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types = 1);

namespace CaT\Plugins\WBDCommunicator\SOAP;

use CaT\Plugins\WBDCommunicator\Config\Tgic;
use CaT\Plugins\WBDCommunicator\Config\Connection;
use CaT\Plugins\WBDCommunicator\Config\WBD;
use CaT\WBD\SOAPFactory\WBD_3_0_SOAPClientFactory;

class Factory
{
    /**
     * @var Connection\DB
     */
    protected $connection_db;

    /**
     * @var Tgic\DB
     */
    protected $tgic_db;

    /**
     * @var WBD\DB
     */
    protected $system_db;

    public function __construct(
        Connection\DB $connection_db,
        Tgic\DB $tgic_db,
        WBD\DB $system_db
    ) {
        $this->connection_db = $connection_db;
        $this->tgic_db = $tgic_db;
        $this->system_db = $system_db;
    }

    /**
     * @throws \LogicException if wbd system is not configured
     * @return WBD_3_0_SOAPClientFactory
     */
    public function getWBD3SOAP()
    {
        $system = $this->system_db->getActiveWBDSystem();
        if ($system->isLive()) {
            return $this->getWBD30SOAPForLive();
        }

        if ($system->isTest()) {
            return $this->getWBD30SOAPForTest();
        }

        throw new \LogicException("No WBD System is configured to get a valid SOAP-Client");
    }

    protected function getWBD30SOAPForLive()
    {
        $tgic_settings = $this->tgic_db->getTgicSettings();
        $con_settings = $this->connection_db->getConnection();
        $host = $con_settings->getHost();
        if ($host == "") {
            $host = null;
        }
        $port = $con_settings->getPort();
        if ($port == "") {
            $port = null;
        }

        return new WBD_3_0_SOAPClientFactory(
            $tgic_settings->getPartnerId(),
            $tgic_settings->getCertstore(),
            $tgic_settings->getPassword(),
            WBD_3_0_SOAPClientFactory::LIVE,
            $host,
            $port
        );
    }

    protected function getWBD30SOAPForTest()
    {
        $tgic_settings = $this->tgic_db->getTgicSettings();
        $con_settings = $this->connection_db->getConnection();
        $host = $con_settings->getHost();
        if ($host == "") {
            $host = null;
        }
        $port = $con_settings->getPort();
        if ($port == "") {
            $port = null;
        }

        return new WBD_3_0_SOAPClientFactory(
            $tgic_settings->getPartnerId(),
            $tgic_settings->getCertstore(),
            $tgic_settings->getPassword(),
            WBD_3_0_SOAPClientFactory::TEST,
            $host,
            $port
        );
    }
}
