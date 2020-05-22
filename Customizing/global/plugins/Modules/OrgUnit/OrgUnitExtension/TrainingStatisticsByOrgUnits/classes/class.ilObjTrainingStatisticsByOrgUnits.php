<?php

declare(strict_types=1);

use CaT\Plugins\TrainingStatisticsByOrgUnits\DI;
use CaT\Plugins\TrainingStatisticsByOrgUnits\Settings\Settings;

class ilObjTrainingStatisticsByOrgUnits extends ilOrgUnitExtension
{
    use DI;

    /**
     * @var Settings
     */
    protected $settings;

    public function initType()
    {
        $this->setType("xtou");
    }

    public function doCreate()
    {
        $this->settings = $this->getDIC()["settings.db"]->createSettingsFor((int) $this->getId());
    }

    public function doRead()
    {
        $this->settings = $this->getDIC()["settings.db"]->selectSettingsFor((int) $this->getId());
    }

    public function doUpdate()
    {
        $this->getDIC()["settings.db"]->updateSettings($this->settings);
    }

    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $fnc = function (Settings $s) {
            return $s->withIsOnline($this->getSettings()->isOnline())
                ->withIsGlobal($this->getSettings()->isGlobal());
        };

        $new_obj->updateSettings($fnc);
        $new_obj->update();
    }

    public function doDelete()
    {
        $this->getDIC()["settings.db"]->deleteSettingsFor((int) $this->getId());
    }

    public function getSettings() : Settings
    {
        return $this->settings;
    }

    public function updateSettings(Closure $fnc)
    {
        $this->settings = $fnc($this->settings);
    }

    protected function getDIC()
    {
        global $DIC;
        return $this->getObjectDIC($this, $DIC);
    }

    public function getPluginDir() : string
    {
        return $this->plugin->getDirectory();
    }

    public function txtClosure()
    {
        return function (string $code) {
            return $this->txt($code);
        };
    }
}
