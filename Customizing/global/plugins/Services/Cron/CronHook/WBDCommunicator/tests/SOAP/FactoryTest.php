<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDCommunicator\SOAP;

use PHPUnit\Framework\TestCase;
use CaT\Plugins\WBDCommunicator\Config\WBD;
use CaT\WBD\SOAPFactory\WBD_3_0_SOAPClientFactory;

class UDFDefinitionTest extends TestCase
{
    public function test_create_instance()
    {
        $connection_db = $this->createMock(\CaT\Plugins\WBDCommunicator\Config\Connection\DB::class);
        $tgic_db = $this->createMock(\CaT\Plugins\WBDCommunicator\Config\Tgic\DB::class);
        $system_db = $this->createMock(\CaT\Plugins\WBDCommunicator\Config\WBD\DB::class);

        $factory = new Factory(
            $connection_db,
            $tgic_db,
            $system_db
        );

        $this->assertInstanceOf(Factory::class, $factory);
    }

    /**
     * @group needsInstalledILIAS
     */
    public function test_get_soap_for_test_system()
    {
        $connection_db = $this->createMock(\CaT\Plugins\WBDCommunicator\Config\Connection\DB::class);
        $tgic_db = $this->createMock(\CaT\Plugins\WBDCommunicator\Config\Tgic\DB::class);
        $system_db = $this->createMock(\CaT\Plugins\WBDCommunicator\Config\WBD\DB::class);
        $system = new WBD\System(WBD\System::WBD_TEST);

        $system_db->expects($this->never())
            ->method("saveActiveWBDSystem")
        ;

        $system_db->expects($this->once())
            ->method("getActiveWBDSystem")
            ->willReturn($system)
        ;

        $factory = new Factory(
            $connection_db,
            $tgic_db,
            $system_db
        );

        $soap = $factory->getWBD3SOAP();
        $this->assertInstanceOf(WBD_3_0_SOAPClientFactory::class, $soap);
    }

    /**
     * @group needsInstalledILIAS
     */
    public function test_get_soap_for_live_system()
    {
        $connection_db = $this->createMock(\CaT\Plugins\WBDCommunicator\Config\Connection\DB::class);
        $tgic_db = $this->createMock(\CaT\Plugins\WBDCommunicator\Config\Tgic\DB::class);
        $system_db = $this->createMock(\CaT\Plugins\WBDCommunicator\Config\WBD\DB::class);
        $system = new WBD\System(WBD\System::WBD_LIVE);

        $system_db->expects($this->never())
            ->method("saveActiveWBDSystem")
        ;

        $system_db->expects($this->once())
            ->method("getActiveWBDSystem")
            ->willReturn($system)
        ;

        $factory = new Factory(
            $connection_db,
            $tgic_db,
            $system_db
        );

        $soap = $factory->getWBD3SOAP();
        $this->assertInstanceOf(WBD_3_0_SOAPClientFactory::class, $soap);
    }

    /**
     * @group needsInstalledILIAS
     */
    public function test_get_soap_failed()
    {
        try {
            $connection_db = $this->createMock(\CaT\Plugins\WBDCommunicator\Config\Connection\DB::class);
            $tgic_db = $this->createMock(\CaT\Plugins\WBDCommunicator\Config\Tgic\DB::class);
            $system_db = $this->createMock(\CaT\Plugins\WBDCommunicator\Config\WBD\DB::class);
            $system = $this->createMock(WBD\System::class);
            $system->expects($this->exactly(2))
                ->method("getName")
                ->willReturn("joke")
            ;

            $system_db->expects($this->never())
                ->method("saveActiveWBDSystem")
            ;

            $system_db->expects($this->never())
                ->method("getActiveWBDSystem")
                ->willReturn($system)
            ;

            $factory = new Factory(
                $connection_db,
                $tgic_db,
                $system_db
            );

            $soap = $factory->getWBD3SOAP();
            $this->assertFalse(true);
        } catch (\LogicException $e) {
            $this->assertTrue(true);
        }
    }
}
