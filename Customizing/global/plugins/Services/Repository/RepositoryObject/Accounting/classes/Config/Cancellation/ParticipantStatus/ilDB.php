<?php

declare(strict_types=1);

namespace CaT\Plugins\Accounting\Config\Cancellation\ParticipantStatus;

use ilSetting;

class ilDB implements DB
{
    const SETTINGS_KEY = "xacc_cancel_states";

    /**
     * @var ilSetting
     */
    protected $settings;

    public function __construct(ilSetting $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param int[] $states
     */
    public function saveStates(array $states)
    {
        $this->settings->set(self::SETTINGS_KEY, serialize($states));
    }

    /**
     * @return int[]
     */
    public function getStates() : array
    {
        $states = $this->settings->get(self::SETTINGS_KEY, null);

        if (is_null($states)) {
            return [];
        }

        return unserialize($states);
    }

    public function getILIASStatesNum() : array
    {
        $states = $this->getStates();
        return array_map(
            function ($state_key) {
                $sp = explode("_", $state_key);
                return (int) $sp[1];
            },
            $states
        );
    }
}
