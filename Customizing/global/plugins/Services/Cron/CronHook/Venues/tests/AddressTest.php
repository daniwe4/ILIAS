<?php

use \CaT\Plugins\Venues\ObjectFactory;
use \CaT\Plugins\Venues\Venues\Address\Address;
use PHPUnit\Framework\TestCase;

/**
 * Test the settings of Venue
 *
  * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class AddressTest extends TestCase
{
    use ObjectFactory;

    public function testConstruction()
    {
        $id = 1;
        $address1 = 'Adresse 1';
        $country = 'Land';
        $address2 = 'Addresse 2';
        $postcode = '12345';
        $city = 'Stadt';
        $latitude = 50.870717;
        $longitude = 7.140734;
        $zoom = 3;

        $add = $this->getAddressObject(
            $id,
            $address1,
            $country,
            $address2,
            $postcode,
            $city,
            $latitude,
            $longitude,
            $zoom
        );

        $this->assertInstanceOf(Address::class, $add);

        $this->assertEquals($id, $add->getId());
        $this->assertEquals($address1, $add->getAddress1());
        $this->assertEquals($country, $add->getCountry());
        $this->assertEquals($address2, $add->getAddress2());
        $this->assertEquals($postcode, $add->getPostcode());
        $this->assertEquals($city, $add->getCity());
        $this->assertEquals($latitude, $add->getLatitude());
        $this->assertEquals($longitude, $add->getLongitude());
        $this->assertEquals($zoom, $add->getZoom());
    }
}
