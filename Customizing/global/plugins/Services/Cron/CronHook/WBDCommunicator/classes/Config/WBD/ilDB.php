<?php

declare(strict_types=1);

namespace CaT\Plugins\WBDCommunicator\Config\WBD;

class ilDB implements DB
{
    const KEY = "active_wbd_system";

    /**
     * @param \ilSetting
     */
    protected $il_setting;

    public function __construct(\ilSetting $il_setting)
    {
        $this->il_setting = $il_setting;
    }

    /**
     * @inheritDoc
     */
    public function saveActiveWBDSystem(string $system_name)
    {
        $system = new System($system_name);
        $this->il_setting->set(self::KEY, $system->getName());
    }

    /**
     * @inheritDoc
     */
    public function getActiveWBDSystem() : System
    {
        $active_system = $this->il_setting->get(self::KEY, System::WBD_TEST);
        return new System($active_system);
    }
}
