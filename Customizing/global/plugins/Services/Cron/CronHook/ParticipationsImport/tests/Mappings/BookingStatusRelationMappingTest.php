<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase as TestCase;
use CaT\Plugins\ParticipationsImport\Mappings\BookingStatusRelationMapping;
use CaT\Plugins\ParticipationsImport\Mappings\BookingStatusMapping;
use CaT\Plugins\ParticipationsImport\Mappings\Mapping;

class BookingStatusRelationMappingTest extends TestCase
{
    public function test_init()
    {
        $psm = new BookingStatusRelationMapping();
        $this->assertInstanceOf(BookingStatusMapping::class, $psm);
    }

    public function test_add_and_read_values()
    {
        $psm = new BookingStatusRelationMapping();
        $psm->addRelation('part_1', 'participant');
        $psm->addRelation('part_2', 'participant');
        $psm->addRelation('cancelled', 'cancelled');
        $psm->addRelation('cancelled_after_deadline1', 'cancelled_after_deadline');
        $psm->addRelation('waiting1', 'waiting');
        $psm->addRelation('waiting_cancelled1', 'waiting_cancelled');
        $psm->addRelation('waiting_self_cancelled1', 'waiting_self_cancelled');
        $psm->addRelation('approval_pending1', 'approval_pending');
        $psm->addRelation('approval_declined1', 'approval_declined');
        $this->assertEquals(
            $psm->ilBookingStatusForExternStatus('part_1'),
            'participant'
        );
        $this->assertEquals(
            $psm->ilBookingStatusForExternStatus('part_2'),
            'participant'
        );
        $this->assertEquals(
            $psm->ilBookingStatusForExternStatus('cancelled'),
            'cancelled'
        );
        $this->assertEquals(
            $psm->ilBookingStatusForExternStatus(
                'cancelled_after_deadline1'
            ),
            'cancelled_after_deadline'
        );
        $this->assertEquals(
            $psm->ilBookingStatusForExternStatus(
                'waiting1'
            ),
            'waiting'
        );
        $this->assertEquals(
            $psm->ilBookingStatusForExternStatus(
                'waiting_cancelled1'
            ),
            'waiting_cancelled'
        );
        $this->assertEquals(
            $psm->ilBookingStatusForExternStatus(
                'waiting_self_cancelled1'
            ),
            'waiting_self_cancelled'
        );
        $this->assertEquals(
            $psm->ilBookingStatusForExternStatus(
                'approval_pending1'
            ),
            'approval_pending'
        );
        $this->assertEquals(
            $psm->ilBookingStatusForExternStatus(
                'approval_declined1'
            ),
            'approval_declined'
        );
        $this->assertEquals(
            $psm->ilBookingStatusForExternStatus('foo'),
            Mapping::NO_MAPPING_FOUND_STRING
        );
    }

    public function test_invalid_status()
    {
        $psm = new BookingStatusRelationMapping();
        try {
            $psm->addRelation('foo', 'bar');
            $this->assertTrue(false);
        } catch (CaT\Plugins\ParticipationsImport\Mappings\RelationException $e) {
            $this->assertTrue(true);
        }
    }
}
