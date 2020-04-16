<?php

declare(strict_types=1);

namespace CaT\Plugins\RoomSetup\ServiceOptions;

use PHPUnit\Framework\TestCase;

/**
 * Testing the immutable object service option ist really immutable
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ServiceOptionTest extends TestCase
{
    public function test_withName()
    {
        $service_option = new ServiceOption(1, "Bread");
        $new_service_option = $service_option->withName("Cockies");

        $this->assertEquals(1, $new_service_option->getId());
        $this->assertEquals("Cockies", $new_service_option->getName());

        $this->assertEquals("Bread", $service_option->getName());
    }
}
