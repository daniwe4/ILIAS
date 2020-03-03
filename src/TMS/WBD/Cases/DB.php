<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace ILIAS\TMS\WBD\Cases;

use CaT\WBD\Cases as WBD_CASES;

interface DB
{
    /**
     * @return WBD_CASES\ReportParticipation[]
     */
    public function getParticipationsToReport(
        int $gutberaten_udf_id,
        int $announce_wbd_id,
        \DateTime $start_date
    ) : array;
    /**
     * @param int $gutberaten_udf_id
     * @param int $announce_wbd_id
     * @return WBD_CASES\RequestParticipations[]
     */
    public function getIdsForParticipationRequest(int $gutberaten_udf_id, int $announce_wbd_id) : array;
    /**
     * @return WBD_CASES\ReportParticipation[]
     */
    public function getParticipationsToCancel() : array;
}
