<?php

declare(strict_types=1);

use \CaT\Plugins\Webinar\DI;
use \CaT\Plugins\Webinar\Config;

/**
 * Config GUI class.
 *
 * @ilCtrl_Calls ilWebinarConfigGUI: ilConfigWebinarGUI, ilWebinarNotFinalizedGUI
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE
 */
class ilWebinarConfigGUI extends ilPluginConfigGUI
{
    use DI;

    const TAB_BASE_CONFIG = "tab_base_config";
    const TAB_REMINDER = "tab_reminder";

    /**
     * @var Config\DI
     */
    protected $dic;

    public function performCommand($cmd)
    {
        global $DIC;
        $this->dic = $this->getPluginDIC($this->plugin_object, $DIC);

        $this->setTabs();

        $next_class = $this->dic["ilCtrl"]->getNextClass();

        switch ($next_class) {
            case "ilconfigwebinargui":
                $this->dic["ilTabs"]->activateTab(self::TAB_BASE_CONFIG);
                $gui = $this->dic["config.gui"];
                $this->dic["ilCtrl"]->forwardCommand($gui);
                break;
            case "ilwebinarnotfinalizedgui":
                $this->dic["ilTabs"]->activateTab(self::TAB_REMINDER);
                $gui = $this->dic["config.notfinalized.gui"];
                $this->dic["ilCtrl"]->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case "configure":
                        ilUtil::redirect($this->dic["config.gui.link"]);
                        break;
                    default:
                        throw new Exception("unknown command " . $cmd);
                }
        }
    }

    /**
     * Sets tabs for telefon config
     *
     * @return void
     */
    protected function setTabs()
    {
        $this->dic["ilTabs"]->addTab(
            self::TAB_BASE_CONFIG,
            $this->txt("config"),
            $this->dic["config.gui.link"]
        );

        $this->dic["ilTabs"]->addTab(
            self::TAB_REMINDER,
            $this->txt("reminder"),
            $this->dic["config.notfinalized.link"]
        );
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->dic["txtclosure"], $code);
    }
}
