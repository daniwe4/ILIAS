<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\DataSources;

class Config
{
    protected $extern_crs_id_col_title;
    protected $crs_title_col_title;
    protected $crs_type_col_title;
    protected $crs_begin_date_col_title;
    protected $crs_end_date_col_title;
    protected $crs_provider_col_title;
    protected $crs_venue_col_title;
    protected $crs_idd_col_title;

    protected $extern_usr_id_col_title;
    protected $participation_status_col_title;
    protected $booking_status_col_title;
    protected $booking_date_col_title;
    protected $participation_date_col_title;
    protected $participation_idd_col_title;

    protected $crs_type_default;
    protected $crs_title_default;
    protected $participation_status_default;
    protected $booking_status_default;
    protected $idd_default_booked;
    protected $idd_default_achieved;
    protected $crs_provider_default;
    protected $crs_venue_default;

    const NONE_INT = -1;

    public function __construct(
        string $extern_crs_id_col_title,
        string $crs_title_col_title,
        string $crs_type_col_title,
        string $crs_begin_date_col_title,
        string $crs_end_date_col_title,
        string $crs_provider_col_title,
        string $crs_venue_col_title,
        string $crs_idd_col_title,
        string $extern_usr_id_col_title,
        string $participation_status_col_title,
        string $booking_status_col_title,
        string $booking_date_col_title,
        string $participation_date_col_title,
        string $participation_idd_col_title,
        string $crs_type_default,
        string $crs_title_default,
        string $participation_status_default,
        string $booking_status_default,
        int $crs_idd_default,
        int $participation_idd_default,
        string $crs_provider_default,
        string $crs_venue_default
    ) {
        $this->extern_crs_id_col_title = $extern_crs_id_col_title;
        $this->crs_title_col_title = $crs_title_col_title;
        $this->crs_type_col_title = $crs_type_col_title;
        $this->crs_begin_date_col_title = $crs_begin_date_col_title;
        $this->crs_end_date_col_title = $crs_end_date_col_title;
        $this->crs_provider_col_title = $crs_provider_col_title;
        $this->crs_venue_col_title = $crs_venue_col_title;
        $this->crs_idd_col_title = $crs_idd_col_title;

        $this->extern_usr_id_col_title = $extern_usr_id_col_title;
        $this->participation_status_col_title = $participation_status_col_title;
        $this->booking_status_col_title = $booking_status_col_title;
        $this->booking_date_col_title = $booking_date_col_title;
        $this->participation_date_col_title = $participation_date_col_title;
        $this->participation_idd_col_title = $participation_idd_col_title;

        $this->crs_type_default = $crs_type_default;
        $this->crs_title_default = $crs_title_default;
        $this->participation_status_default = $participation_status_default;
        $this->booking_status_default = $booking_status_default;
        $this->crs_idd_default = $crs_idd_default;
        $this->participation_idd_default = $participation_idd_default;
        $this->crs_provider_default = $crs_provider_default;
        $this->crs_venue_default = $crs_venue_default;
    }


    public function externCrsIdColTitle() : string
    {
        return $this->extern_crs_id_col_title;
    }
    public function crsTitleColTitle() : string
    {
        return $this->crs_title_col_title;
    }
    public function crsTypeColTitle() : string
    {
        return $this->crs_type_col_title;
    }
    public function crsBeginDateColTitle() : string
    {
        return $this->crs_begin_date_col_title;
    }
    public function crsEndDateColTitle() : string
    {
        return $this->crs_end_date_col_title;
    }
    public function crsProviderColTitle() : string
    {
        return $this->crs_provider_col_title;
    }
    public function crsVenueColTitle() : string
    {
        return $this->crs_venue_col_title;
    }
    public function crsIddColTitle() : string
    {
        return $this->crs_idd_col_title;
    }

    public function externUsrIdColTitle() : string
    {
        return $this->extern_usr_id_col_title;
    }
    public function participationStatusColTitle() : string
    {
        return $this->participation_status_col_title;
    }
    public function bookingStatusColTitle() : string
    {
        return $this->booking_status_col_title;
    }
    public function bookingDateColTitle() : string
    {
        return $this->booking_date_col_title;
    }
    public function participationDateColTitle() : string
    {
        return $this->participation_date_col_title;
    }
    public function participationIddColTitle() : string
    {
        return $this->participation_idd_col_title;
    }

    public function crsTypeDefault() : string
    {
        return $this->crs_type_default;
    }
    public function crsTitleDefault() : string
    {
        return $this->crs_title_default;
    }
    public function participationStatusDefault() : string
    {
        return $this->participation_status_default;
    }
    public function bookingStatusDefault() : string
    {
        return $this->booking_status_default;
    }
    public function crsIddDefault() : int
    {
        return $this->crs_idd_default;
    }
    public function participationIddDefault() : int
    {
        return $this->participation_idd_default;
    }
    public function crsProviderDefault() : string
    {
        return $this->crs_provider_default;
    }
    public function crsVenueDefault() : string
    {
        return $this->crs_venue_default;
    }
}
