<?php

use \CaT\Plugins\Venues\ObjectFactory;
use \CaT\Plugins\Venues\Venues\Costs\Costs;
use PHPUnit\Framework\TestCase;

/**
 * Test the settings of Venue
 *
  * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class CostsTest extends TestCase
{
    use ObjectFactory;

    public function testConstruction()
    {
        $id = 1;
        $fixed_rate_day = 1.1;
        $fixed_rate_all_inclusive = 1.2;
        $bed_and_breakfast = 1.3;
        $bed = 1.4;
        ;
        $fixed_rate_conference = 1.5;
        ;
        $room_usage = 1.6;
        $other = 1.7;
        $terms = 'these are the terms.';

        $cst = $this->getCostsObject(
            $id,
            $fixed_rate_day,
            $fixed_rate_all_inclusive,
            $bed_and_breakfast,
            $bed,
            $fixed_rate_conference,
            $room_usage,
            $other,
            $terms
        );

        $this->assertInstanceOf(Costs::class, $cst);

        $this->assertEquals($id, $cst->getId());
        $this->assertEquals($fixed_rate_day, $cst->getFixedRateDay());
        $this->assertEquals($fixed_rate_all_inclusive, $cst->getFixedRateAllInclusiv());
        $this->assertEquals($bed_and_breakfast, $cst->getBedAndBreakfast());
        $this->assertEquals($bed, $cst->getBed());
        $this->assertEquals($fixed_rate_conference, $cst->getFixedRateConference());
        $this->assertEquals($room_usage, $cst->getRoomUsage());
        $this->assertEquals($other, $cst->getOther());
        $this->assertEquals($terms, $cst->getTerms());
    }
}
