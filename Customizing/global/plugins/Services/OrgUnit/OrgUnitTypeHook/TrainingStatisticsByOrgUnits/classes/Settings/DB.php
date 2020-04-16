<?php

namespace CaT\Plugins\TrainingStatisticsByOrgUnits\Settings;

interface DB
{
    public function createSettingsFor(int $id) : Settings;
    public function updateSettings(Settings $settings);
    public function selectSettingsFor(int $id) : Settings;
    public function deleteSettingsFor(int $id);
}
