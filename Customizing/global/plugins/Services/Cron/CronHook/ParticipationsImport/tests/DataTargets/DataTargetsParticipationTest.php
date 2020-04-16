<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use CaT\Plugins\ParticipationsImport\DataTargets\Participation;

class DataTargetsParticipationTest extends TestCase
{
    public function test_init_and_read()
    {
        $p = new Participation(
            321,
            123,
            'booking_status',
            'participation_status',
            \DateTime::createFromFormat('Ymd', '20181001'),
            \DateTime::createFromFormat('Ymd', '20181002'),
            5
        );

        $this->assertEquals($p->crsId(), 321);
        $this->assertEquals($p->usrId(), 123);
        $this->assertEquals($p->bookingStatus(), 'booking_status');
        $this->assertEquals($p->participationStatus(), 'participation_status');
        $this->assertEquals($p->beginDate()->format('Ymd'), '20181001');
        $this->assertEquals($p->endDate()->format('Ymd'), '20181002');
        $this->assertEquals($p->idd(), 5);
    }

    public function test_init_and_read_null_dates()
    {
        $p = new Participation(
            321,
            123,
            'booking_status',
            'participation_status',
            null,
            null,
            5
        );

        $this->assertEquals($p->crsId(), 321);
        $this->assertEquals($p->usrId(), 123);
        $this->assertEquals($p->bookingStatus(), 'booking_status');
        $this->assertEquals($p->participationStatus(), 'participation_status');
        $this->assertNull($p->beginDate());
        $this->assertNull($p->endDate());
        $this->assertEquals($p->idd(), 5);
    }
}
