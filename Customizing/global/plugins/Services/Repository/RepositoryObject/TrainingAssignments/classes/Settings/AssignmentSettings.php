<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainingAssignments\Settings;

class AssignmentSettings
{
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var bool
     */
    protected $show_info_tab;

    public function __construct(int $obj_id, bool $show_info_tab = false)
    {
        $this->obj_id = $obj_id;
        $this->show_info_tab = $show_info_tab;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function getShowInfoTab() : bool
    {
        return $this->show_info_tab;
    }

    public function withShowInfoTab(bool $show_info_tab) : AssignmentSettings
    {
        $clone = clone $this;
        $clone->show_info_tab = $show_info_tab;
        return $clone;
    }
}
