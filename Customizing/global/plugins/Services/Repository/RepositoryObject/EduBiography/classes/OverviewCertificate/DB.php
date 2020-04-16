<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\OverviewCertificate;

interface DB
{
    public function save(int $usr_id, int $obj_id, int $minutes);

    /**
     * @return ExistingCertificate[]
     */
    public function selectFor(int $usr_id) : array;
}
