<?php

declare(strict_types=1);

namespace CaT\Plugins\Webinar\Config;

/**
 * Class Config.
 * Dataholding class for the plugin config values.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class Config
{
    const TEL_SRC = "telefon_source";
    const PHONE_DEFAULT = "phone_office";

    public function __construct(\ilSetting $settings)
    {
        $this->settings = $settings;
    }

    public function getPhoneType() : string
    {
        $type = $this->settings->get(self::TEL_SRC, "");

        if ($type === "" || $type === null) {
            return self::PHONE_DEFAULT;
        }

        return $type;
    }

    public function setPhoneType(string $value)
    {
        $this->settings->set(self::TEL_SRC, $value);
    }
}
