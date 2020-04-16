<?php
use CaT\Plugins\Accomodation\Venue as V;
use PHPUnit\Framework\TestCase;

/**
 * Testing the VenueObject
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class AccomodationVenueTest extends TestCase
{
    /**
     * @var Venue
     */
    protected $obj;

    public function test_construction_and_getters()
    {
        $venue = $this
            ->getMockBuilder("Venue")
            ->setMethods(array('getObjId', 'getGeneral'))
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $general = $this
            ->getMockBuilder("General")
            ->setMethods(array('getName'))
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $general
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue("test"))
        ;

        $venue
            ->expects($this->once())
            ->method('getObjId')
            ->will($this->returnValue(5))
        ;

        $venue
            ->expects($this->once())
            ->method('getGeneral')
            ->will($this->returnValue($general))
        ;

        $obj = new V\Venue($venue, "test");

        $this->assertEquals(5, $obj->getObjId());
        $this->assertEquals("test", $obj->getName());
    }

    public function test_construction_and_getters_with_venue_is_null()
    {
        $obj = new V\Venue(null, "test");

        $this->assertNull($obj->getObjId());
        $this->assertEquals("test", $obj->getName());
    }
}
