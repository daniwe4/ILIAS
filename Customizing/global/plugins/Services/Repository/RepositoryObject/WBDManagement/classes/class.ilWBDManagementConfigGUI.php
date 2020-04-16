<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

require_once __DIR__ . "/../vendor/autoload.php";

use CaT\Plugins\WBDManagement\DI;

/**
 * @ilCtrl_Calls ilWBDManagementConfigGUI: ilWBDManagementUDFConfigGUI
 */
class ilWBDManagementConfigGUI extends ilPluginConfigGUI
{
    use DI;

    const TAB_UDF = "udf_tab";

    const CMD_CONFIGURE = "configure";

    /**
     * @var Pimple\Container
     */
    protected $dic;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @param string $cmd
     * @throws ilCtrlException
     */
    public function performCommand($cmd)
    {
        $this->ctrl = $this->getDIC()["ilCtrl"];
        $this->tabs = $this->getDIC()["ilTabs"];

        $this->setTabs();

        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
            case "ilwbdmanagementudfconfiggui":
                $this->tabs->activateTab(self::TAB_UDF);
                $gui = $this->getDIC()["config.user_defined_fields.gui"];
                $this->ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_CONFIGURE:
                        $this->defaultForwarding();
                        break;
                    default:
                        throw new Exception("Unknown command: " . $cmd);
                }
        }
    }

    protected function getDIC()
    {
        if (is_null($this->dic)) {
            global $DIC;
            $this->dic = $this->getPluginDIC($this->plugin_object, $DIC);
        }
        return $this->dic;
    }

    protected function setTabs()
    {
        $link = $this->getDIC()["config.user_defined_fields.link"];
        $this->tabs->addTab(self::TAB_UDF, $this->txt(self::TAB_UDF), $link);
    }

    protected function defaultForwarding()
    {
        $link = $this->dic["config.user_defined_fields.link"];
        $this->ctrl->redirectToURL($link);
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->getDIC()["txtclosure"], $code);
    }
}
