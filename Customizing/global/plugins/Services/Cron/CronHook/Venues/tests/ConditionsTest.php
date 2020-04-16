<?php

use \CaT\Plugins\Venues\ObjectFactory;
use \CaT\Plugins\Venues\Venues\Conditions\Conditions;
use PHPUnit\Framework\TestCase;

/**
 * Test the settings of Venue
 *
  * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ConditionsTest extends TestCase
{
    use ObjectFactory;

    public function testConstruction()
    {
        $id = 1;
        $general_agreement = true;
        $terms = 'these are the terms.';
        $valuta = 'valuta';

        $con = $this->getConditionsObject(
            $id,
            $general_agreement,
            $terms,
            $valuta
        );

        $this->assertInstanceOf(Conditions::class, $con);

        $this->assertEquals($id, $con->getId());
        $this->assertEquals($general_agreement, $con->getGeneralAgreement());
        $this->assertEquals($terms, $con->getTerms());
        $this->assertEquals($valuta, $con->getValuta());
    }
}
