<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\ParticipationDocument;

interface DB
{
    /**
     * @inheritDoc
     */
    public function getSuccessfulCourseInformationsFor(
        int $usr_id,
        \DateTime $start,
        \DateTime $end
    ) : array;
}
