<?php

declare(strict_types=1);

namespace CaT\Plugins\RoomSetup\Equipment;

use PHPUnit\Framework\TestCase;

/**
 * Testing the immutable object service option ist really immutable
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class EquipmentTest extends TestCase
{
    public function test_WithServiceOptions()
    {
        $equipment = new Equipment(1);
        $new_equipment = $equipment->withServiceOptions(array(2,3,4));

        $this->assertEquals(1, $new_equipment->getObjId());
        $this->assertEquals(array(2,3,4), $new_equipment->getServiceOptions());
        $this->assertEmpty($new_equipment->getRoomInformation());
        $this->assertEmpty($new_equipment->getSeatOrder());

        $this->assertEquals(1, $equipment->getObjId());
        $this->assertEmpty($equipment->getServiceOptions());
        $this->assertEmpty($equipment->getRoomInformation());
        $this->assertEmpty($equipment->getSeatOrder());

        return array($new_equipment);
    }

    /**
     * @depends test_WithServiceOptions
     */
    public function test_WithRoomInformations($equipment)
    {
        $text = "Der Raum ist toll und groß und besitzt 2 Beamer";
        $equipment = $equipment[0];
        $new_equipment = $equipment->withRoomInformation($text);

        $this->assertEquals(1, $new_equipment->getObjId());
        $this->assertEquals(array(2,3,4), $new_equipment->getServiceOptions());
        $this->assertEquals($text, $new_equipment->getRoomInformation());
        $this->assertEmpty($new_equipment->getSeatOrder());

        $this->assertEquals(1, $equipment->getObjId());
        $this->assertEquals(array(2,3,4), $equipment->getServiceOptions());
        $this->assertEmpty($equipment->getRoomInformation());
        $this->assertEmpty($equipment->getSeatOrder());

        return array($new_equipment, $text);
    }

    /**
     * @depends test_WithRoomInformations
     */
    public function test_WithSeatOrder($equipment)
    {
        $text = "Bitte im U und so viel wie rein passt.";
        $info_text = $equipment[1];
        $equipment = $equipment[0];
        $new_equipment = $equipment->withSeatOrder($text);

        $this->assertEquals(1, $new_equipment->getObjId());
        $this->assertEquals(array(2,3,4), $new_equipment->getServiceOptions());
        $this->assertEquals($info_text, $new_equipment->getRoomInformation());
        $this->assertEquals($text, $new_equipment->getSeatOrder());

        $this->assertEquals(1, $equipment->getObjId());
        $this->assertEquals(array(2,3,4), $equipment->getServiceOptions());
        $this->assertEquals($info_text, $equipment->getRoomInformation());
        $this->assertEmpty($equipment->getSeatOrder());
    }
}
