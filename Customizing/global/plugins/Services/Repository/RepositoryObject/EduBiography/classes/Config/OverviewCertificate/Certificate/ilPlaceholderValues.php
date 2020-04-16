<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\Config\OverviewCertificate\Certificate;

use CaT\Plugins\EduBiography\Config\OverviewCertificate\Schedules;

class ilPlaceholderValues implements \ilCertificatePlaceholderValues
{
    /**
     * @var \ilDefaultPlaceholderValues
     */
    protected $default_placeholder_values;

    /**
     * @var Schedules\DB
     */
    protected $schedules_db;

    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var ilUserSpecificValues
     */
    protected $user_specific_values;

    public function __construct(
        \ilDefaultPlaceholderValues $default_placeholder_values,
        Schedules\DB $schedules_db,
        \ilDBInterface $db,
        ilUserSpecificValues $user_specific_values
    ) {
        $this->default_placeholder_values = $default_placeholder_values;
        $this->schedules_db = $schedules_db;
        $this->db = $db;
        $this->user_specific_values = $user_specific_values;
    }

    /**
     * @inheritDoc
     */
    public function getPlaceholderValues(int $userId, int $objId)
    {
        $placeholder_values = $this->default_placeholder_values->getPlaceholderValues($userId, $objId);

        list($start, $end) = $this->getPeriod($objId);

        $idd_times = $this->getIDDTimes($userId, $start, $end);
        $start = \ilDatePresentation::formatDate(
            new \ilDate(
                $start->format("Y-m-d"),
                IL_CAL_DATE
            )
        );
        $end = \ilDatePresentation::formatDate(
            new \ilDate(
                $end->format("Y-m-d"),
                IL_CAL_DATE
            )
        );
        $placeholder_values = array_merge(
            $placeholder_values,
            [
                strtoupper("total_idd_value") => $idd_times,
                strtoupper("period_start") => $start,
                strtoupper("period_end") => $end
            ]
        );
        return $placeholder_values;
    }

    /**
     * @inheritDoc
     */
    public function getPlaceholderValuesForPreview(int $userId, int $objId)
    {
        $placeholder_preview_values = $this->default_placeholder_values
            ->getPlaceholderValuesForPreview($userId, $objId);

        $placeholder_preview_values = array_merge(
            $placeholder_preview_values,
            [
                strtoupper("total_idd_value") => "total_idd_value",
                strtoupper("period_start") => "period_start",
                strtoupper("period_end") => "period_end"
            ]
        );
        return $placeholder_preview_values;
    }

    protected function getPeriod(int $id)
    {
        $schedule = $this->schedules_db->selectFor($id);
        var_dump($schedule);
        return [
            $schedule->getStart(),
            $schedule->getEnd(),
        ];
    }

    protected function getIDDTimes(int $usr_id, \DateTime $start, \DateTime $end)
    {
        $minutes = $this->user_specific_values->getIDDTimesFor($usr_id, $start, $end);

        return $this->minutesToTimeString($minutes);
    }

    protected function minutesToTimeString(int $minutes)
    {
        $hours = floor($minutes / 60);
        $minutes = $minutes - $hours * 60;

        return str_pad((string) $hours, 2, '0', STR_PAD_LEFT)
            . ':'
            . str_pad((string) $minutes, 2, '0', STR_PAD_LEFT)
        ;
    }
}
