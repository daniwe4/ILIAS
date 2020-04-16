<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use CaT\Plugins\ParticipationsImport\DataTargets\Course;

class DataTargetsCourseTest extends TestCase
{
    public function test_init_and_read()
    {
        $crs = new Course(
            123,
            'title',
            'crs_type',
            \DateTime::createFromFormat('Ymd', '20181001'),
            \DateTime::createFromFormat('Ymd', '20181002'),
            5,
            'provider',
            'venue'
        );
        $this->assertEquals($crs->crsTitle(), 'title');
        $this->assertEquals($crs->crsId(), 123);
        $this->assertEquals($crs->crsType(), 'crs_type');
        $this->assertEquals($crs->beginDate()->format('Ymd'), '20181001');
        $this->assertEquals($crs->endDate()->format('Ymd'), '20181002');
        $this->assertEquals($crs->idd(), 5);
        $this->assertEquals($crs->provider(), 'provider');
        $this->assertEquals($crs->venue(), 'venue');
    }

    public function test_init_and_read_null_dates()
    {
        $crs = new Course(
            123,
            'title',
            'crs_type',
            null,
            null,
            5,
            'provider',
            'venue'
        );
        $this->assertEquals($crs->crsTitle(), 'title');
        $this->assertEquals($crs->crsId(), 123);
        $this->assertEquals($crs->crsType(), 'crs_type');
        $this->assertNull($crs->beginDate());
        $this->assertNull($crs->endDate());
        $this->assertEquals($crs->idd(), 5);
        $this->assertEquals($crs->provider(), 'provider');
        $this->assertEquals($crs->venue(), 'venue');
    }
}
