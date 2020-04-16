<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use CaT\Plugins\ParticipationsImport\DataSources as DS;
use CaT\Plugins\ParticipationsImport\Filesystem as FS;
use CaT\Plugins\ParticipationsImport\Data as D;

class DocumentCoursesSourceTest extends TestCase
{
    public function setUp() : void
    {
        $this->cs_mock = $this->getMockBuilder(DS\ConfigStorage::class)
                        ->setMethods(['loadCurrentConfig', 'storeConfigAsCurrent'])
                        ->getMockForAbstractClass();
        $this->extractor_mock = $this->getMockBuilder(D\DataExtractor::class)
                        ->setMethods(['extractContent'])
                        ->getMockForAbstractClass();
        $this->locator_mock = $this->getMockBuilder(FS\Locator::class)
                        ->setMethods(['getCurrentFilePath'])
                        ->getMockForAbstractClass();
        $this->locator_mock->method('getCurrentFilePath')
                        ->willReturn('some_path');
    }

    protected function extractArrayByParticipation($crs)
    {
        return 	[
                    'extern_crs_id' => $crs->crsId(),
                    'crs_type' => $crs->crsType(),
                    'title' => $crs->title(),
                    'begin_date' => $crs->beginDate() ? $crs->beginDate()->format('Y-m-d') : null,
                    'end_date' => $crs->endDate() ? $crs->endDate()->format('Y-m-d') : null,
                    'idd' => $crs->idd(),
                    'crs_provider' => $crs->provider(),
                    'crs_venue' => $crs->venue()
                ];
    }

    public function test_init()
    {
        $this->cs_mock->method('loadCurrentConfig')
            ->willReturn(
                new DS\Config(
                    'extern_crs_id_col_title',
                    'crs_title_col_title',
                    'crs_type_col_title',
                    'crs_begin_date_col_title',
                    'crs_end_date_col_title',
                    'crs_provider_col_title',
                    'crs_venue_col_title',
                    'crs_idd_col_title',
                    'extern_usr_id_col_title',
                    'participation_status_col_title',
                    'booking_status_col_title',
                    'booking_date_col_title',
                    'participation_date_col_title',
                    'participation_idd_col_title',
                    'crs_type_default',
                    'crs_title_default',
                    'participation_status_default',
                    'booking_status_default',
                    10,
                    20,
                    '',
                    ''
                )
            );
        $ps = new DS\DocumentCoursesSource(
            $this->cs_mock,
            $this->extractor_mock,
            $this->locator_mock
        );
        $this->assertInstanceOf(DS\CoursesSource::class, $ps);
    }

    public function test_complete()
    {
        $this->cs_mock->method('loadCurrentConfig')
            ->willReturn(
                new DS\Config(
                    'extern_crs_id',
                    'crs_title',
                    'crs_type',
                    'crs_begin_date',
                    'crs_end_date',
                    'crs_provider_col_title',
                    'crs_venue_col_title',
                    'crs_idd_col_title',
                    'extern_usr_id',
                    'participation_status',
                    'booking_status',
                    'booking_date',
                    'participation_date',
                    'idd',
                    'crs_type_default',
                    'crs_title_default',
                    'b', //'participation_status_default',
                    'p', //'booking_status_default',
                    10,
                    20,
                    '',
                    ''
                )
            );
        $data = [
                [
                    'extern_crs_id' => 'c1',
                    'title' => 'tc1',
                    'crs_type' => 'typ1',
                    'begin_date' => '2018-01-01',
                    'end_date' => '2018-01-02',
                    'idd' => 5,
                    'crs_provider' => 'provider',
                    'crs_venue' => 'venue'
                ],
                [
                    'extern_crs_id' => 'c2',
                    'title' => 'tc2',
                    'crs_type' => 'typ2',
                    'begin_date' => '2018-01-01',
                    'end_date' => '2018-01-02',
                    'idd' => 5,
                    'crs_provider' => 'provider',
                    'crs_venue' => 'venue'
                ],
                [
                    'extern_crs_id' => 'c3',
                    'title' => 'tc3',
                    'crs_type' => 'typ3',
                    'begin_date' => '2018-01-01',
                    'end_date' => '2018-01-02',
                    'idd' => 5,
                    'crs_provider' => 'provider',
                    'crs_venue' => 'venue'
                ]
            ];
        $this->extractor_mock->method('extractContent')->willReturn(
            $data
        );
        $ps = new DS\DocumentCoursesSource(
            $this->cs_mock,
            $this->extractor_mock,
            $this->locator_mock
        );
        foreach ($ps->getCourses() as $course) {
            $this->assertEquals(
                array_shift($data),
                $this->extractArrayByParticipation($course)
            );
        }
        $this->assertCount(0, $data);
    }

    public function test_missing_values()
    {
        $this->cs_mock->method('loadCurrentConfig')
            ->willReturn(
                new DS\Config(
                    'extern_crs_id',
                    '',
                    '',
                    'crs_begin_date',
                    'crs_end_date',
                    '',
                    '',
                    '',
                    'extern_usr_id',
                    'participation_status',
                    'booking_status',
                    'booking_date',
                    'participation_date',
                    '',
                    'type_default',
                    'title_default',
                    'b', //'participation_status_default',
                    'p', //'booking_status_default',
                    10,
                    20,
                    'prov',
                    'ven'
                )
            );
        $data = [
                [
                    'extern_crs_id' => 'c1',
                    'begin_date' => '2018-01-01',
                    'end_date' => '2018-01-02'
                ],
                [
                    'extern_crs_id' => 'c2',
                    'begin_date' => '2018-01-01',
                    'end_date' => '2018-01-02'
                ],
                [
                    'extern_crs_id' => 'c3',
                    'begin_date' => '2018-01-01',
                    'end_date' => '2018-01-02'
                ]
            ];
        $this->extractor_mock->method('extractContent')->willReturn(
            $data
        );
        $data_ref = array_map(
            function ($row) {
                $row['crs_type'] = 'type_default';
                $row['title'] = 'title_default';
                $row['idd'] = 10;
                $row['crs_provider'] = 'prov';
                $row['crs_venue'] = 'ven';
                return $row;
            },
            $data
        );
        $ps = new DS\DocumentCoursesSource(
            $this->cs_mock,
            $this->extractor_mock,
            $this->locator_mock
        );
        foreach ($ps->getCourses() as $course) {
            $this->assertEquals(
                array_shift($data_ref),
                $this->extractArrayByParticipation($course)
            );
        }
        $this->assertCount(0, $data_ref);
    }

    public function test_no_begin_date()
    {
        $this->cs_mock->method('loadCurrentConfig')
            ->willReturn(
                new DS\Config(
                    'extern_crs_id',
                    'crs_title',
                    'crs_type',
                    '',
                    'crs_end_date',
                    '',
                    '',
                    '',
                    'extern_usr_id',
                    'participation_status',
                    'booking_status',
                    'booking_date',
                    'participation_date',
                    'idd',
                    'crs_type_default',
                    'crs_title_default',
                    'b', //'participation_status_default',
                    'p', //'booking_status_default',
                    10,
                    20,
                    '',
                    ''
                )
            );
        $data = [
                [
                    'extern_crs_id' => 'c1',
                    'title' => 'tc1',
                    'crs_type' => 'typ1',
                    'end_date' => '2018-01-02',
                    'idd' => 5
                ],
                [
                    'extern_crs_id' => 'c2',
                    'title' => 'tc2',
                    'crs_type' => 'typ2',
                    'end_date' => '2018-01-02',
                    'idd' => 5
                ],
                [
                    'extern_crs_id' => 'c3',
                    'title' => 'tc3',
                    'crs_type' => 'typ3',
                    'end_date' => '2018-01-02',
                    'idd' => 5
                ]
            ];
        $this->extractor_mock->method('extractContent')->willReturn(
            $data
        );
        $data_ref = array_map(
            function ($row) {
                $row['begin_date'] = $row['end_date'];
                $row['crs_provider'] = '';
                $row['crs_venue'] = '';
                return $row;
            },
            $data
        );
        $ps = new DS\DocumentCoursesSource(
            $this->cs_mock,
            $this->extractor_mock,
            $this->locator_mock
        );
        foreach ($ps->getCourses() as $course) {
            $this->assertEquals(
                array_shift($data_ref),
                $this->extractArrayByParticipation($course)
            );
        }
        $this->assertCount(0, $data_ref);
    }

    public function test_no_end_date()
    {
        $this->cs_mock->method('loadCurrentConfig')
            ->willReturn(
                new DS\Config(
                    'extern_crs_id',
                    'crs_title',
                    'crs_type',
                    'crs_begin_date',
                    '',
                    '',
                    '',
                    '',
                    'extern_usr_id',
                    'participation_status',
                    'booking_status',
                    'booking_date',
                    'participation_date',
                    'idd',
                    'crs_type_default',
                    'crs_title_default',
                    'b', //'participation_status_default',
                    'p', //'booking_status_default',
                    10,
                    20,
                    '',
                    ''
                )
            );
        $data = [
                [
                    'extern_crs_id' => 'c1',
                    'title' => 'tc1',
                    'crs_type' => 'typ1',
                    'begin_date' => '2018-01-01',
                    'idd' => 5
                ],
                [
                    'extern_crs_id' => 'c2',
                    'title' => 'tc2',
                    'crs_type' => 'typ2',
                    'begin_date' => '2018-01-01',
                    'idd' => 5
                ],
                [
                    'extern_crs_id' => 'c3',
                    'title' => 'tc3',
                    'crs_type' => 'typ3',
                    'begin_date' => '2018-01-01',
                    'idd' => 5
                ]
            ];
        $this->extractor_mock->method('extractContent')->willReturn(
            $data
        );
        $data_ref = array_map(
            function ($row) {
                $row['end_date'] = $row['begin_date'];
                $row['crs_provider'] = '';
                $row['crs_venue'] = '';
                return $row;
            },
            $data
        );
        $ps = new DS\DocumentCoursesSource(
            $this->cs_mock,
            $this->extractor_mock,
            $this->locator_mock
        );
        foreach ($ps->getCourses() as $course) {
            $this->assertEquals(
                array_shift($data_ref),
                $this->extractArrayByParticipation($course)
            );
        }
        $this->assertCount(0, $data_ref);
    }
    public function test_no_date()
    {
        $this->cs_mock->method('loadCurrentConfig')
            ->willReturn(
                new DS\Config(
                    'extern_crs_id',
                    'crs_title',
                    'crs_type',
                    '',
                    '',
                    '',
                    '',
                    '',
                    'extern_usr_id',
                    'participation_status',
                    'booking_status',
                    'booking_date',
                    'participation_date',
                    'idd',
                    'crs_type_default',
                    'crs_title_default',
                    'b', //'participation_status_default',
                    'p', //'booking_status_default',
                    10,
                    20,
                    '',
                    ''
                )
            );
        $data = [
                [
                    'extern_crs_id' => 'c1',
                    'title' => 'tc1',
                    'crs_type' => 'typ1',
                    'idd' => 5
                ],
                [
                    'extern_crs_id' => 'c2',
                    'title' => 'tc2',
                    'crs_type' => 'typ2',
                    'idd' => 5
                ],
                [
                    'extern_crs_id' => 'c3',
                    'title' => 'tc3',
                    'crs_type' => 'typ3',
                    'idd' => 5
                ]
            ];
        $this->extractor_mock->method('extractContent')->willReturn(
            $data
        );
        $data_ref = array_map(
            function ($row) {
                $row['end_date'] = null;
                $row['begin_date'] = null;
                $row['crs_provider'] = '';
                $row['crs_venue'] = '';
                return $row;
            },
            $data
        );
        $ps = new DS\DocumentCoursesSource(
            $this->cs_mock,
            $this->extractor_mock,
            $this->locator_mock
        );
        foreach ($ps->getCourses() as $course) {
            $this->assertEquals(
                array_shift($data_ref),
                $this->extractArrayByParticipation($course)
            );
        }
        $this->assertCount(0, $data_ref);
    }

    public function test_proper_mapping_all()
    {
        $this->cs_mock->method('loadCurrentConfig')
            ->willReturn(
                new DS\Config(
                    'ext_c_id',
                    'c_tit',
                    'c_typ',
                    'c_beg_dat',
                    'c_end_dat',
                    '',
                    '',
                    'points',
                    'ext_u_id',
                    'part_stat',
                    'book_stat',
                    'book_dat',
                    'part_dat',
                    '',
                    'crs_type_default',
                    'crs_title_default',
                    'b', //'participation_status_default',
                    'p', //'booking_status_default',
                    10,
                    20,
                    '',
                    ''
                )
            );
        $this->extractor_mock->expects($this->once())
            ->method('extractContent')->with(
                $this->equalTo('some_path'),
                $this->equalTo(
                    ['ext_c_id' => 'extern_crs_id',
                    'c_beg_dat' => 'begin_date',
                    'c_end_dat' => 'end_date',
                    'c_tit' => 'title',
                    'c_typ' => 'crs_type',
                    'points' => 'idd']
                )
            );
        $ps = new DS\DocumentCoursesSource(
            $this->cs_mock,
            $this->extractor_mock,
            $this->locator_mock
        );
        foreach ($ps->getCourses() as $obj) {
            # code...
        };
    }

    public function test_proper_mapping_missing_fields()
    {
        $this->cs_mock->method('loadCurrentConfig')
            ->willReturn(
                new DS\Config(
                    'ext_c_id',
                    '',
                    '',
                    'c_beg_dat',
                    'c_end_dat',
                    '',
                    '',
                    '',
                    'ext_u_id',
                    'part_stat',
                    '',
                    '',
                    'part_dat',
                    '',
                    'crs_type_default',
                    'crs_title_default',
                    'b', //'participation_status_default',
                    'p', //'booking_status_default',
                    10,
                    20,
                    '',
                    ''
                )
            );
        $this->extractor_mock->expects($this->once())
            ->method('extractContent')->with(
                $this->equalTo('some_path'),
                $this->equalTo(
                    ['ext_c_id' => 'extern_crs_id',
                    'c_beg_dat' => 'begin_date',
                    'c_end_dat' => 'end_date']
                )
            );
        $ps = new DS\DocumentCoursesSource(
            $this->cs_mock,
            $this->extractor_mock,
            $this->locator_mock
        );
        foreach ($ps->getCourses() as $obj) {
            # code...
        };
    }
}
