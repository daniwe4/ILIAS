<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\Config\OverviewCertificate\Schedules;

interface DB
{
    public function create(
        string $title,
        \DateTime $start,
        \DateTime $end,
        int $min_idd_value,
        bool $part_document
    );
    public function update(Schedule $schedule);
    /**
     * @return Schedule[]
     */
    public function selectAllBy(
        string $field,
        string $direction,
        int $limit,
        int $offset
    ) : array;
    public function selectFor(int $id) : Schedule;
    public function isTitleInUse(string $title) : bool;
    public function setActiveStatus(int $id, bool $active);
    public function deleteFor(int $id);
    /**
     * @return Schedule[]
     */
    public function getAllActiveScheduled(array $obj_ids) : array;
}
