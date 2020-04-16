<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\ParticipationDocument;

use CaT\Plugins\EduBiography\Config\OverviewCertificate\Schedules\Schedule;

interface Generator
{
    public function createPdf(
        int $user_id,
        Schedule $schedule,
        int $received_idd_min,
        string $logo_path
    ) : array;
}
