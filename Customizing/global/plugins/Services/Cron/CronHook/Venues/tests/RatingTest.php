<?php

use \CaT\Plugins\Venues\ObjectFactory;
use \CaT\Plugins\Venues\Venues\Rating\Rating;
use PHPUnit\Framework\TestCase;

/**
 * Test the settings of Venue
 *
  * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class RatingTest extends TestCase
{
    use ObjectFactory;

    public function testConstruction()
    {
        $id = 1;
        $rating = 1.2;
        $info = 'info';

        $rat = $this->getRatingObject(
            $id,
            $rating,
            $info
        );

        $this->assertInstanceOf(Rating::class, $rat);
        $this->assertEquals($id, $rat->getId());
        $this->assertEquals($rating, $rat->getRating());
        $this->assertEquals($info, $rat->getInfo());
    }
}
