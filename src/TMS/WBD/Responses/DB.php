<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace ILIAS\TMS\WBD\Responses;

use CaT\WBD\Responses as WBD_RESPONSE;

interface DB
{
    const WBDA_STATUS_ERROR = 'error';
    const WBDA_STATUS_RETRY = 'retry';
    const WBDA_STATUS_ANNOUNCED = 'announced';
    const WBDA_STATUS_STORNO = 'storno';
    const WBDA_STATUS_CANCELLED = 'cancelled';

    public function cancelParticipation(int $crs_id, int $usr_id);
    public function importParticipation(WBD_RESPONSE\WBDParticipation $participation);
    public function setAsReported(int $crs_id, int $usr_id);
    public function announceWBDBookingId(int $crs_id, int $usr_id, string $wbd_booking_id);
    public function removeWBDBookingId(int $crs_id, int $usr_id, string $source_wbd_booking_id);
    public function setBookingStatusSuccess(
        int $crs_id,
        int $usr_id,
        string $wbd_booking_id
    );
    public function setBookingStatusCancelled(
        int $crs_id,
        int $usr_id,
        string $wbd_booking_id
    );

    public function updateParticipation(
        int $crs_id,
        int $usr_id,
        string $wbd_booking_id,
        \DateTime $start_date,
        \DateTime $end_date,
        int $minutes
    );

    public function setParticipationCancelled(
        int $crs_id,
        int $usr_id,
        string $wbd_booking_id
    );

    /**
     * @param string $wbd_booking_id
     * @return int | null
     */
    public function getCourseIdOf(string $wbd_booking_id);

    public function saveAnnouncedValues(WBD_RESPONSE\Participation $participation);
}
