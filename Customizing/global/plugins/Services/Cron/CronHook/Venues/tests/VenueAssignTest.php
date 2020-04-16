<?php
use \CaT\Plugins\Venues\VenueAssignment;
use PHPUnit\Framework\TestCase;

/**
 * Testing the assignment of venues to (course-) ids
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class VenueAssignTest extends TestCase
{
    public function test_construction_list()
    {
        $va = new VenueAssignment\ListAssignment(1, 1);
        $this->assertInstanceOf(VenueAssignment\ListAssignment::class, $va);
    }
    public function test_construction_custom()
    {
        $va = new VenueAssignment\CustomAssignment(1, 'a text');
        $this->assertInstanceOf(VenueAssignment\CustomAssignment::class, $va);
    }

    public function test_attributes_list()
    {
        $va = new VenueAssignment\ListAssignment(101, 102);
        $this->assertTrue($va->isListAssignment());
        $this->assertEquals(101, $va->getCrsId());
        $this->assertEquals(102, $va->getVenueId());
        $va = $va->withVenueId(103);
        $this->assertEquals(103, $va->getVenueId());
    }

    public function test_attributes_custom()
    {
        $va = new VenueAssignment\CustomAssignment(101, 'a text');
        $this->assertTrue($va->isCustomAssignment());
        $this->assertEquals(101, $va->getCrsId());
        $this->assertEquals('a text', $va->getVenueText());
        $va = $va->withVenueText('another text');
        $this->assertEquals('another text', $va->getVenueText());
    }

    public function test_disallowed_mixing_list()
    {
        $va = new VenueAssignment\ListAssignment(101, 102);
        try {
            $x = $va->getVenueText();
            $this->assertFalse("This should not happen");
        } catch (\LogicException $e) {
            $this->assertTrue(true);
        }

        try {
            $x = $va->withVenueText('text');
            $this->assertFalse("This should not happen");
        } catch (\LogicException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_disallowed_mixing_custom()
    {
        $va = new VenueAssignment\CustomAssignment(101, 'text');
        try {
            $x = $va->getVenueId();
            $this->assertFalse("This should not happen");
        } catch (\LogicException $e) {
            $this->assertTrue(true);
        }

        try {
            $x = $va->withVenueId(1);
            $this->assertFalse("This should not happen");
        } catch (\LogicException $e) {
            $this->assertTrue(true);
        }
    }
}
