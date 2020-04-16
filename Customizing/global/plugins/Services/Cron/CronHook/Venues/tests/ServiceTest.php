<?php

use \CaT\Plugins\Venues\ObjectFactory;
use \CaT\Plugins\Venues\Venues\Service\Service;
use PHPUnit\Framework\TestCase;

/**
 * Test the settings of Venue
 *
  * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ServiceTest extends TestCase
{
    use ObjectFactory;

    public function testConstruction()
    {
        $id = 1;
        $mail_service_list = "list1@mail.com";
        $mail_room_setup = "list2@mail.com";
        $days_send_service = 1;
        $days_send_room_setup = 2;
        $mail_material_list = "list3@mail.com";
        $days_send_material_list = 3	;
        $mail_accomodation_list = "list4@mail.com";
        $days_send_accomodation_list = 4;
        $days_remind_accomodation_list = 5;

        $srv = $this->getServiceObject(
            $id,
            $mail_service_list,
            $mail_room_setup,
            $days_send_service,
            $days_send_room_setup,
            $mail_material_list,
            $days_send_material_list,
            $mail_accomodation_list,
            $days_send_accomodation_list,
            $days_remind_accomodation_list
        );

        $this->assertInstanceOf(Service::class, $srv);

        $this->assertEquals($id, $srv->getId());
        $this->assertEquals($mail_service_list, $srv->getMailServiceList());
        $this->assertEquals($mail_room_setup, $srv->getMailRoomSetup());
        $this->assertEquals($days_send_service, $srv->getDaysSendService());
        $this->assertEquals($days_send_room_setup, $srv->getDaysSendRoomSetup());
        $this->assertEquals($mail_material_list, $srv->getMailMaterialList());
        $this->assertEquals($days_send_material_list, $srv->getDaysSendMaterial());
        $this->assertEquals($mail_accomodation_list, $srv->getMailAccomodationList());
        $this->assertEquals($days_send_accomodation_list, $srv->getDaysSendAccomodation());
        $this->assertEquals($days_remind_accomodation_list, $srv->getDaysRemindAccomodation());
    }
}
