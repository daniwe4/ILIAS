<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainingAssignments\Settings;

interface DB
{
    public function createAssignmentSettings(int $obj_id, bool $show_info_tab = false);
    public function selectByObjId(int $obj_id) : AssignmentSettings;
    public function updateAssignmentsSettings(AssignmentSettings $settings);
}
