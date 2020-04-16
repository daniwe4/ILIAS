<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\DataSources;

class ilSettingConfigStorage implements ConfigStorage
{
    const KEYWORD_PREFIX = 'part_imp_data_source_';
    const KEYWORD_EXTERN_CRS_ID_COL_TITLE = self::KEYWORD_PREFIX . 'extern_crs_id_col_title';
    const KEYWORD_CRS_TITLE_COL_TITLE = self::KEYWORD_PREFIX . 'crs_title_col_title';
    const KEYWORD_CRS_TYPE_COL_TITLE = self::KEYWORD_PREFIX . 'crs_type_col_title';
    const KEYWORD_CSR_BEGIN_DATE_COL_TITLE = self::KEYWORD_PREFIX . 'crs_begin_date_col_title';
    const KEYWORD_CRS_END_DATE_COL_TITLE = self::KEYWORD_PREFIX . 'crs_end_date_col_title';
    const KEYWORD_CRS_PROVIDER_COL_TITLE = self::KEYWORD_PREFIX . 'crs_provider_col_title';
    const KEYWORD_CRS_VENUE_COL_TITLE = self::KEYWORD_PREFIX . 'crs_venue_col_title';
    const KEYWORD_CRS_IDD_COL_TITLE = self::KEYWORD_PREFIX . 'crs_idd_col_title';
    const KEYWORD_EXTERN_USR_ID_COL_TITLE = self::KEYWORD_PREFIX . 'extern_usr_id_col_title';
    const KEYWORD_PARTICIPATION_STATUS_COL_TITLE = self::KEYWORD_PREFIX . 'part_status_col_title';
    const KEYWORD_BOOKING_STATUS_COL_TITLE = self::KEYWORD_PREFIX . 'book_status_col_title';
    const KEYWORD_BOOKING_DATE_COL_TITLE = self::KEYWORD_PREFIX . 'booking_date_col_title';
    const KEYWORD_PARTICIPATION_DATE_COL_TITLE = self::KEYWORD_PREFIX . 'participation_date_col_title';
    const KEYWORD_PARTICIPATION_IDD_COL_TITLE = self::KEYWORD_PREFIX . 'part_idd_col_title';
    const KEYWORD_CRS_TYPE_DEFAULT = self::KEYWORD_PREFIX . 'crs_type_default';
    const KEYWORD_CRS_TITLE_DEFAULT = self::KEYWORD_PREFIX . 'crs_title_default';
    const KEYWORD_PARTICIPATION_STATUS_DEFAULT = self::KEYWORD_PREFIX . 'part_status_default';
    const KEYWORD_BOOKING_STATUS_DEFAULT = self::KEYWORD_PREFIX . 'book_status_default';
    const KEYWORD_CRS_IDD_DEFAULT = self::KEYWORD_PREFIX . 'crs_idd_default';
    const KEYWORD_PARTICIPATION_IDD_DEFAULT = self::KEYWORD_PREFIX . 'participation_idd_default';
    const KEYWORD_CRS_PROVIDER_DEFAULT = self::KEYWORD_PREFIX . 'crs_provider_default';
    const KEYWORD_CRS_VENUE_DEFAULT = self::KEYWORD_PREFIX . 'crs_venue_default';
    public function __construct(\ilSetting $set)
    {
        $this->set = $set;
    }
    public function loadCurrentConfig() : Config
    {
        return new Config(
            (string) $this->set->get(self::KEYWORD_EXTERN_CRS_ID_COL_TITLE),
            (string) $this->set->get(self::KEYWORD_CRS_TITLE_COL_TITLE),
            (string) $this->set->get(self::KEYWORD_CRS_TYPE_COL_TITLE),
            (string) $this->set->get(self::KEYWORD_CSR_BEGIN_DATE_COL_TITLE),
            (string) $this->set->get(self::KEYWORD_CRS_END_DATE_COL_TITLE),
            (string) $this->set->get(self::KEYWORD_CRS_PROVIDER_COL_TITLE),
            (string) $this->set->get(self::KEYWORD_CRS_VENUE_COL_TITLE),
            (string) $this->set->get(self::KEYWORD_CRS_IDD_COL_TITLE),
            (string) $this->set->get(self::KEYWORD_EXTERN_USR_ID_COL_TITLE),
            (string) $this->set->get(self::KEYWORD_PARTICIPATION_STATUS_COL_TITLE),
            (string) $this->set->get(self::KEYWORD_BOOKING_STATUS_COL_TITLE),
            (string) $this->set->get(self::KEYWORD_BOOKING_DATE_COL_TITLE),
            (string) $this->set->get(self::KEYWORD_PARTICIPATION_DATE_COL_TITLE),
            (string) $this->set->get(self::KEYWORD_PARTICIPATION_IDD_COL_TITLE),
            (string) $this->set->get(self::KEYWORD_CRS_TYPE_DEFAULT),
            (string) $this->set->get(self::KEYWORD_CRS_TITLE_DEFAULT),
            (string) $this->set->get(self::KEYWORD_PARTICIPATION_STATUS_DEFAULT),
            (string) $this->set->get(self::KEYWORD_BOOKING_STATUS_DEFAULT),
            (int) $this->set->get(self::KEYWORD_CRS_IDD_DEFAULT),
            (int) $this->set->get(self::KEYWORD_PARTICIPATION_IDD_DEFAULT),
            (string) $this->set->get(self::KEYWORD_CRS_PROVIDER_DEFAULT),
            (string) $this->set->get(self::KEYWORD_CRS_VENUE_DEFAULT)
        );
    }
    public function storeConfigAsCurrent(Config $cfg)
    {
        $this->set->set(self::KEYWORD_EXTERN_CRS_ID_COL_TITLE, $cfg->externCrsIdColTitle());
        $this->set->set(self::KEYWORD_CRS_TITLE_COL_TITLE, $cfg->crsTitleColTitle());
        $this->set->set(self::KEYWORD_CRS_TYPE_COL_TITLE, $cfg->crsTypeColTitle());
        $this->set->set(self::KEYWORD_CSR_BEGIN_DATE_COL_TITLE, $cfg->crsBeginDateColTitle());
        $this->set->set(self::KEYWORD_CRS_END_DATE_COL_TITLE, $cfg->crsEndDateColTitle());
        $this->set->set(self::KEYWORD_CRS_PROVIDER_COL_TITLE, $cfg->crsProviderColTitle());
        $this->set->set(self::KEYWORD_CRS_VENUE_COL_TITLE, $cfg->crsVenueColTitle());
        $this->set->set(self::KEYWORD_CRS_IDD_COL_TITLE, $cfg->crsIddColTitle());
        $this->set->set(self::KEYWORD_EXTERN_USR_ID_COL_TITLE, $cfg->externUsrIdColTitle());
        $this->set->set(self::KEYWORD_PARTICIPATION_STATUS_COL_TITLE, $cfg->participationStatusColTitle());
        $this->set->set(self::KEYWORD_BOOKING_STATUS_COL_TITLE, $cfg->bookingStatusColTitle());
        $this->set->set(self::KEYWORD_BOOKING_DATE_COL_TITLE, $cfg->bookingDateColTitle());
        $this->set->set(self::KEYWORD_PARTICIPATION_DATE_COL_TITLE, $cfg->participationDateColTitle());
        $this->set->set(self::KEYWORD_PARTICIPATION_IDD_COL_TITLE, $cfg->participationIddColTitle());
        $this->set->set(self::KEYWORD_CRS_TYPE_DEFAULT, $cfg->crsTypeDefault());
        $this->set->set(self::KEYWORD_CRS_TITLE_DEFAULT, $cfg->crsTitleDefault());
        $this->set->set(self::KEYWORD_PARTICIPATION_STATUS_DEFAULT, $cfg->participationStatusDefault());
        $this->set->set(self::KEYWORD_BOOKING_STATUS_DEFAULT, $cfg->bookingStatusDefault());
        $this->set->set(self::KEYWORD_CRS_IDD_DEFAULT, $cfg->crsIddDefault());
        $this->set->set(self::KEYWORD_PARTICIPATION_IDD_DEFAULT, $cfg->participationIddDefault());
        $this->set->set(self::KEYWORD_CRS_PROVIDER_DEFAULT, $cfg->crsProviderDefault());
        $this->set->set(self::KEYWORD_CRS_VENUE_DEFAULT, $cfg->crsVenueDefault());
    }
}
