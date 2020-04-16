<?php

use \CaT\Plugins\Venues\Venues\General\General;
use \CaT\Plugins\Venues\Tags\Tag;
use PHPUnit\Framework\TestCase;

/**
 * Test the settings of Venue
 *
  * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class GeneralTest extends TestCase
{
    public function testConstruction()
    {
        $tag = $this->getMockBuilder(Tags\Tag::class)
            ->disableOriginalConstructor()
            ->getMock();

        $name = 'name';
        $homepage = 'homepage';
        $tags = array($tag);

        $gen = new General(
            1,
            $name,
            $homepage,
            $tags
        );

        $this->assertEquals(1, $gen->getId());
        $this->assertEquals($name, $gen->getName());
        $this->assertEquals($homepage, $gen->getHomepage());
        $this->assertEquals($tags, $gen->getTags());
    }
}
