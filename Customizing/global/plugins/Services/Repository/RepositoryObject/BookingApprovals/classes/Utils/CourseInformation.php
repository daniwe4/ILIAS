<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingApprovals\Utils;

use ILIAS\TMS\CourseInfo;
use ILIAS\TMS\CourseInfoHelper;
use CaT\Ente\ILIAS\ilHandlerObjectHelper;

/**
 * wrapper to get to ente-information for the course
 */
class CourseInformation
{
    use ilHandlerObjectHelper;
    use CourseInfoHelper;

    /**
     * @var int
     */
    protected $crs_ref_id;

    public function __construct(int $crs_ref_id)
    {
        $this->crs_ref_id = $crs_ref_id;
    }

    public function get() : array
    {
        return $this->getCourseInfo(CourseInfo::CONTEXT_APPROVALS_OVERVIEW, true, true);
    }

    /**
     * @inheritdoc
     */
    protected function getDIC()
    {
        global $DIC;
        return $DIC;
    }

    /**
     * @inheritdoc
     */
    protected function getEntityRefId()
    {
        return $this->crs_ref_id;
    }
}
