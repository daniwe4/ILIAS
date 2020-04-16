<?php

declare(strict_types=1);

namespace CaT\Plugins\Accounting\Config\Cancellation\Roles;

use ilSetting;

class ilDB implements DB
{
    const SETTINGS_KEY = "xacc_cancel_roles";

    /**
     * @var ilSetting
     */
    protected $settings;

    public function __construct(ilSetting $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param int[] $roles
     */
    public function saveRoles(array $roles)
    {
        $this->settings->set(self::SETTINGS_KEY, serialize($roles));
    }

    /**
     * @return int[]
     */
    public function getRoles() : array
    {
        $roles = $this->settings->get(self::SETTINGS_KEY, null);

        if (is_null($roles)) {
            return [];
        }

        return unserialize($roles);
    }
}
