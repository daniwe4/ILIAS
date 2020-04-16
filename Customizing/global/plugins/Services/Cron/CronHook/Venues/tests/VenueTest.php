<?php

use \CaT\Plugins\Venues\Venues\Venue;
use \CaT\Plugins\Venues\Venues\General;
use \CaT\Plugins\Venues\Venues\Rating;
use \CaT\Plugins\Venues\Venues\Address;
use \CaT\Plugins\Venues\Venues\Contact;
use \CaT\Plugins\Venues\Venues\Conditions;
use \CaT\Plugins\Venues\Venues\Capacity;
use \CaT\Plugins\Venues\Venues\Service;
use \CaT\Plugins\Venues\Venues\Costs;
use PHPUnit\Framework\TestCase;

/**
 * Testing the immutable object venue
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class VenueTest extends TestCase
{
    public function testConstruction()
    {
        $general = $this->getMockBuilder(General\General::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rating = $this->getMockBuilder(Rating\Rating::class)
            ->disableOriginalConstructor()
            ->getMock();
        $address = $this->getMockBuilder(Address\Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contact = $this->getMockBuilder(Contact\Contact::class)
            ->disableOriginalConstructor()
            ->getMock();
        $conditions = $this->getMockBuilder(Conditions\Conditions::class)
            ->disableOriginalConstructor()
            ->getMock();
        $capacity = $this->getMockBuilder(Capacity\Capacity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $service = $this->getMockBuilder(Service\Service::class)
            ->disableOriginalConstructor()
            ->getMock();
        $costs = $this->getMockBuilder(Costs\Costs::class)
            ->disableOriginalConstructor()
            ->getMock();


        $venue = new Venue(
            1,
            $general,
            $rating,
            $address,
            $contact,
            $conditions,
            $capacity,
            $service,
            $costs
        );

        $this->assertEquals(1, $venue->getId());
        $this->assertEquals($general, $venue->getGeneral());
        $this->assertEquals($rating, $venue->getRating());
        $this->assertEquals($address, $venue->getAddress());
        $this->assertEquals($contact, $venue->getContact());
        $this->assertEquals($conditions, $venue->getCondition());
        $this->assertEquals($capacity, $venue->getCapacity());
        $this->assertEquals($service, $venue->getService());
        $this->assertEquals($costs, $venue->getCosts());
    }
}
