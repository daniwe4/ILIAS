<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainingAdminOverview\Settings;

interface DB
{
    public function createSettings(int $obj_id, bool $show_info_tab = false);
    public function selectByObjId(int $obj_id) : Settings;
    public function updateSettings(Settings $settings);
    public function delete(int $obj_id);
}
