<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use CaT\Plugins\ParticipationsImport\DataSources\Config;

class DataSourcesConfigTest extends TestCase
{
    public function test_init_and_read()
    {
        $cfg = new Config(
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
            'provider_default',
            'venue_default'
        );
        $this->assertEquals('extern_crs_id_col_title', $cfg->externCrsIdColTitle());
        $this->assertEquals('crs_title_col_title', $cfg->crsTitleColTitle());
        $this->assertEquals('crs_type_col_title', $cfg->crsTypeColTitle());
        $this->assertEquals('crs_begin_date_col_title', $cfg->crsBeginDateColTitle());
        $this->assertEquals('crs_end_date_col_title', $cfg->crsEndDateColTitle());
        $this->assertEquals('extern_usr_id_col_title', $cfg->externUsrIdColTitle());
        $this->assertEquals('participation_status_col_title', $cfg->participationStatusColTitle());
        $this->assertEquals('booking_status_col_title', $cfg->bookingStatusColTitle());
        $this->assertEquals('booking_date_col_title', $cfg->bookingDateColTitle());
        $this->assertEquals('participation_date_col_title', $cfg->participationDateColTitle());
        $this->assertEquals('participation_idd_col_title', $cfg->participationIddColTitle());
        $this->assertEquals('crs_type_default', $cfg->crsTypeDefault());
        $this->assertEquals('crs_title_default', $cfg->crsTitleDefault());
        $this->assertEquals('participation_status_default', $cfg->participationStatusDefault());
        $this->assertEquals('booking_status_default', $cfg->bookingStatusDefault());
        $this->assertEquals(10, $cfg->crsIddDefault());
        $this->assertEquals(20, $cfg->participationIddDefault());
        $this->assertEquals('crs_provider_col_title', $cfg->crsProviderColTitle());
        $this->assertEquals('crs_venue_col_title', $cfg->crsVenueColTitle());
        $this->assertEquals('provider_default', $cfg->crsProviderDefault());
        $this->assertEquals('venue_default', $cfg->crsVenueDefault());
        $this->assertEquals('crs_idd_col_title', $cfg->crsIddColTitle());
    }
}
