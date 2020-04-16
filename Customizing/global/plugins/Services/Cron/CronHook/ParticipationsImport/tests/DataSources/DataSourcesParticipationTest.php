<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use CaT\Plugins\ParticipationsImport\DataSources\Participation;

class DataSourcesParticipationTest extends TestCase
{
    public function test_init_and_read()
    {
        $p = new Participation(
            'extern_crs_id',
            'extern_usr_id',
            'booking_status',
            'participation_status',
            \DateTime::createFromFormat('Ymd', '20181001'),
            \DateTime::createFromFormat('Ymd', '20181002'),
            5
        );

        $this->assertEquals($p->externCrsId(), 'extern_crs_id');
        $this->assertEquals($p->externUsrId(), 'extern_usr_id');
        $this->assertEquals($p->bookingStatus(), 'booking_status');
        $this->assertEquals($p->participationStatus(), 'participation_status');
        $this->assertEquals($p->beginDate()->format('Ymd'), '20181001');
        $this->assertEquals($p->endDate()->format('Ymd'), '20181002');
        $this->assertEquals($p->idd(), 5);
    }

    public function test_init_and_read_null_dates()
    {
        $p = new Participation(
            'extern_crs_id',
            'extern_usr_id',
            'booking_status',
            'participation_status',
            null,
            null,
            5
        );

        $this->assertEquals($p->externCrsId(), 'extern_crs_id');
        $this->assertEquals($p->externUsrId(), 'extern_usr_id');
        $this->assertEquals($p->bookingStatus(), 'booking_status');
        $this->assertEquals($p->participationStatus(), 'participation_status');
        $this->assertNull($p->beginDate());
        $this->assertNull($p->endDate());
        $this->assertEquals($p->idd(), 5);
    }
}
