<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use CaT\Plugins\ParticipationsImport\DataSources as DS;
use CaT\Plugins\ParticipationsImport\Filesystem as FS;
use CaT\Plugins\ParticipationsImport\Data as D;

class DocumentParticipationsSourceTest extends TestCase
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

    protected function extractArrayByParticipation($participation)
    {
        return 	[
                    'extern_crs_id' => $participation->externCrsId(),
                    'extern_usr_id' => $participation->externUsrId(),
                    'participation_status' => $participation->participationStatus(),
                    'booking_status' => $participation->bookingStatus(),
                    'booking_date' => $participation->beginDate() ? $participation->beginDate()->format('Y-m-d') : null,
                    'participation_date' => $participation->endDate() ? $participation->endDate()->format('Y-m-d') : null,
                    'idd' => $participation->idd()
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
                    '',
                    '',
                    '',
                    'extern_usr_id_col_title',
                    'participation_status_col_title',
                    'booking_status_col_title',
                    'booking_date_col_title',
                    'participation_date_col_title',
                    'idd_col_title',
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
        $ps = new DS\DocumentParticipationsSource(
            $this->cs_mock,
            $this->extractor_mock,
            $this->locator_mock
        );
        $this->assertInstanceOf(DS\ParticipationsSource::class, $ps);
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
                    'extern_usr_id' => 'u1',
                    'participation_status' => 'p',
                    'booking_status' => 'b1',
                    'booking_date' => '2018-01-01',
                    'participation_date' => '2018-01-02',
                    'idd' => 5
                ],
                [
                    'extern_crs_id' => 'c1',
                    'extern_usr_id' => 'u2',
                    'participation_status' => 'f',
                    'booking_status' => 'b2',
                    'booking_date' => '2018-01-01',
                    'participation_date' => '2018-01-02',
                    'idd' => 5
                ],
                [
                    'extern_crs_id' => 'c2',
                    'extern_usr_id' => 'u2',
                    'participation_status' => 'p',
                    'booking_status' => 'b1',
                    'booking_date' => '2018-01-01',
                    'participation_date' => '2018-01-02',
                    'idd' => 5
                ],
                [
                    'extern_crs_id' => 'c2',
                    'extern_usr_id' => 'u3',
                    'participation_status' => 'f',
                    'booking_status' => 'b2',
                    'booking_date' => '2018-01-01',
                    'participation_date' => '2018-01-02',
                    'idd' => 5
                ]
            ];
        $this->extractor_mock->method('extractContent')->willReturn(
            $data
        );
        $ps = new DS\DocumentParticipationsSource(
            $this->cs_mock,
            $this->extractor_mock,
            $this->locator_mock
        );
        foreach ($ps->getParticipations() as $participation) {
            $this->assertEquals(
                array_shift($data),
                $this->extractArrayByParticipation($participation)
            );
        }
        $this->assertCount(0, $data);
    }


    public function test_missing_status_and_idd()
    {
        $this->cs_mock->method('loadCurrentConfig')
            ->willReturn(
                new DS\Config(
                    'extern_crs_id',
                    'crs_title',
                    'crs_type',
                    'crs_begin_date',
                    'crs_end_date',
                    '',
                    '',
                    '',
                    'extern_usr_id',
                    '',
                    '',
                    'booking_date',
                    'participation_date',
                    'idd',
                    'crs_type_default',
                    'crs_title_default',
                    'p', //'participation_status_default',
                    'b', //'booking_status_default',
                    10,
                    20,
                    '',
                    ''
                )
            );
        $data = [
                [
                    'extern_crs_id' => 'c1',
                    'extern_usr_id' => 'u1',
                    'booking_date' => '2018-01-01',
                    'participation_date' => '2018-01-02'
                ],
                [
                    'extern_crs_id' => 'c1',
                    'extern_usr_id' => 'u2',
                    'booking_date' => '2018-01-01',
                    'participation_date' => '2018-01-02'
                ],
                [
                    'extern_crs_id' => 'c2',
                    'extern_usr_id' => 'u2',
                    'booking_date' => '2018-01-01',
                    'participation_date' => '2018-01-02'
                ],
                [
                    'extern_crs_id' => 'c2',
                    'extern_usr_id' => 'u3',
                    'booking_date' => '2018-01-01',
                    'participation_date' => '2018-01-02'
                ]
            ];
        $this->extractor_mock->method('extractContent')->willReturn(
            $data
        );
        $ps = new DS\DocumentParticipationsSource(
            $this->cs_mock,
            $this->extractor_mock,
            $this->locator_mock
        );
        $data_ref = array_map(
            function ($row) {
                $row['participation_status'] = 'p';
                $row['booking_status'] = 'b';
                $row['idd'] = 20;
                return $row;
            },
            $data
        );
        foreach ($ps->getParticipations() as $participation) {
            $this->assertEquals(
                array_shift($data_ref),
                $this->extractArrayByParticipation($participation)
            );
        }
        $this->assertCount(0, $data_ref);
    }



    public function test_participation_date_missing()
    {
        $this->cs_mock->method('loadCurrentConfig')
            ->willReturn(
                new DS\Config(
                    'extern_crs_id',
                    'crs_title',
                    'crs_type',
                    'crs_begin_date',
                    'crs_end_date',
                    '',
                    '',
                    '',
                    'extern_usr_id',
                    'participation_status',
                    'booking_status',
                    'booking_date',
                    '',
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
                    'extern_usr_id' => 'u1',
                    'participation_status' => 'p',
                    'booking_status' => 'b1',
                    'booking_date' => '2018-01-01',
                    'idd' => 5
                ],
                [
                    'extern_crs_id' => 'c1',
                    'extern_usr_id' => 'u2',
                    'participation_status' => 'f',
                    'booking_status' => 'b2',
                    'booking_date' => '2018-01-01',
                    'idd' => 5
                ],
                [
                    'extern_crs_id' => 'c2',
                    'extern_usr_id' => 'u2',
                    'participation_status' => 'p',
                    'booking_status' => 'b1',
                    'booking_date' => '2018-01-01',
                    'idd' => 5
                ],
                [
                    'extern_crs_id' => 'c2',
                    'extern_usr_id' => 'u3',
                    'participation_status' => 'f',
                    'booking_status' => 'b2',
                    'booking_date' => '2018-01-01',
                    'idd' => 5
                ]
            ];
        $this->extractor_mock->method('extractContent')->willReturn(
            $data
        );
        $data_ref = array_map(
            function ($row) {
                $row['participation_date'] = $row['booking_date'];
                return $row;
            },
            $data
        );
        $ps = new DS\DocumentParticipationsSource(
            $this->cs_mock,
            $this->extractor_mock,
            $this->locator_mock
        );
        foreach ($ps->getParticipations() as $participation) {
            $this->assertEquals(
                array_shift($data_ref),
                $this->extractArrayByParticipation($participation)
            );
        }
        $this->assertCount(0, $data_ref);
    }

    public function test_booking_date_missing()
    {
        $this->cs_mock->method('loadCurrentConfig')
            ->willReturn(
                new DS\Config(
                    'extern_crs_id',
                    'crs_title',
                    'crs_type',
                    'crs_begin_date',
                    'crs_end_date',
                    '',
                    '',
                    '',
                    'extern_usr_id',
                    'participation_status',
                    'booking_status',
                    '',
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
                    'extern_usr_id' => 'u1',
                    'participation_status' => 'p',
                    'booking_status' => 'b1',
                    'participation_date' => '2018-01-01',
                    'idd' => 5
                ],
                [
                    'extern_crs_id' => 'c1',
                    'extern_usr_id' => 'u2',
                    'participation_status' => 'f',
                    'booking_status' => 'b2',
                    'participation_date' => '2018-01-01',
                    'idd' => 5
                ],
                [
                    'extern_crs_id' => 'c2',
                    'extern_usr_id' => 'u2',
                    'participation_status' => 'p',
                    'booking_status' => 'b1',
                    'participation_date' => '2018-01-01',
                    'idd' => 5
                ],
                [
                    'extern_crs_id' => 'c2',
                    'extern_usr_id' => 'u3',
                    'participation_status' => 'f',
                    'booking_status' => 'b2',
                    'participation_date' => '2018-01-01',
                    'idd' => 5
                ]
            ];
        $this->extractor_mock->method('extractContent')->willReturn(
            $data
        );
        $data_ref = array_map(
            function ($row) {
                $row['booking_date'] = $row['participation_date'];
                return $row;
            },
            $data
        );
        $ps = new DS\DocumentParticipationsSource(
            $this->cs_mock,
            $this->extractor_mock,
            $this->locator_mock
        );
        foreach ($ps->getParticipations() as $participation) {
            $this->assertEquals(
                array_shift($data_ref),
                $this->extractArrayByParticipation($participation)
            );
        }
        $this->assertCount(0, $data_ref);
    }

    public function test_dates_missing()
    {
        $this->cs_mock->method('loadCurrentConfig')
            ->willReturn(
                new DS\Config(
                    'extern_crs_id',
                    'crs_title',
                    'crs_type',
                    'crs_begin_date',
                    'crs_end_date',
                    '',
                    '',
                    '',
                    'extern_usr_id',
                    'participation_status',
                    'booking_status',
                    '',
                    '',
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
                    'extern_usr_id' => 'u1',
                    'participation_status' => 'p',
                    'booking_status' => 'b1',
                    'idd' => 5
                ],
                [
                    'extern_crs_id' => 'c1',
                    'extern_usr_id' => 'u2',
                    'participation_status' => 'f',
                    'booking_status' => 'b2',
                    'idd' => 5
                ],
                [
                    'extern_crs_id' => 'c2',
                    'extern_usr_id' => 'u2',
                    'participation_status' => 'p',
                    'booking_status' => 'b1',
                    'idd' => 5
                ],
                [
                    'extern_crs_id' => 'c2',
                    'extern_usr_id' => 'u3',
                    'participation_status' => 'f',
                    'booking_status' => 'b2',
                    'idd' => 5
                ]
            ];
        $this->extractor_mock->method('extractContent')->willReturn(
            $data
        );
        $data_ref = array_map(
            function ($row) {
                $row['booking_date'] = null;
                $row['participation_date'] = null;
                return $row;
            },
            $data
        );
        $ps = new DS\DocumentParticipationsSource(
            $this->cs_mock,
            $this->extractor_mock,
            $this->locator_mock
        );
        foreach ($ps->getParticipations() as $participation) {
            $this->assertEquals(
                array_shift($data_ref),
                $this->extractArrayByParticipation($participation)
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
                    'crs_title',
                    'crs_type',
                    'crs_begin_date',
                    'crs_end_date',
                    '',
                    '',
                    '',
                    'ext_u_id',
                    'part_stat',
                    'book_stat',
                    'book_dat',
                    'part_dat',
                    'points',
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
                    'ext_u_id' => 'extern_usr_id',
                    'part_stat' => 'participation_status',
                    'book_stat' => 'booking_status',
                    'book_dat' => 'booking_date',
                    'part_dat' => 'participation_date',
                    'points' => 'idd']
                )
            );
        $ps = new DS\DocumentParticipationsSource(
            $this->cs_mock,
            $this->extractor_mock,
            $this->locator_mock
        );
        foreach ($ps->getParticipations() as $obj) {
            # code...
        };
    }

    public function test_proper_mapping_missing_fields()
    {
        $this->cs_mock->method('loadCurrentConfig')
            ->willReturn(
                new DS\Config(
                    'ext_c_id',
                    'crs_title',
                    'crs_type',
                    'crs_begin_date',
                    'crs_end_date',
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
                    'ext_u_id' => 'extern_usr_id',
                    'part_stat' => 'participation_status',
                    'part_dat' => 'participation_date']
                )
            );
        $ps = new DS\DocumentParticipationsSource(
            $this->cs_mock,
            $this->extractor_mock,
            $this->locator_mock
        );
        foreach ($ps->getParticipations() as $obj) {
            # code...
        };
    }
}
