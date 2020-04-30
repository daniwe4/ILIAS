<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types = 1);

use CaT\Plugins\WBDCommunicator\DI;

/**
 *
 * @ilCtrl_Calls ilWBDCommunicatorConfigGUI: ilWBDOperationLimitsGUI, ilWBDConnectionGUI, ilWBDUDFGUI, ilTgicGUI
 * @ilCtrl_Calls ilWBDCommunicatorConfigGUI: ilWBDCSecurityGUI, ilSystemConfigurationGUI
 */
class ilWBDCommunicatorConfigGUI extends \ilPluginConfigGUI
{
    use DI;

    const CMD_CONFIGURE = "configure";

    const TAB_OPERATION_LIMITS = "tab_operation_limits";
    const TAB_UDF = "tab_udf";
    const TAB_CONNECTION = "tab_connection";
    const TAB_TGIC = "tab_tgic";
    const TAB_SECURITY = "tab_security";
    const TAB_WBD_SYSTEM = "tab_wbd_system";

    const ROOT_LOGIN = "root";


    /**
     * @var Pimple\Container
     */
    protected $dic;

    public function performCommand($cmd)
    {
        global $DIC;
        $this->dic = $this->getPluginDIC($this->plugin_object, $DIC);

        if (!$this->checkUserName()) {
            ilUtil::sendFailure($this->getPluginObject()->txt("no_permission"), true);
            $this->dic["ilCtrl"]->redirectToURL($this->dic["admin.plugin.link"]);
        }

        $this->setTabs();
        $next_class = $this->dic["ilCtrl"]->getNextClass();

        switch ($next_class) {
            case "ilwbdoperationlimitsgui":
                $this->dic["ilTabs"]->activateTab(self::TAB_OPERATION_LIMITS);
                $gui = $this->dic["config.oplimits.gui"];
                $this->dic["ilCtrl"]->forwardCommand($gui);
                break;
            case "ilwbdudfgui":
                $this->dic["ilTabs"]->activateTab(self::TAB_UDF);
                $gui = $this->dic["config.udf.gui"];
                $this->dic["ilCtrl"]->forwardCommand($gui);
                break;
            case "ilwbdconnectiongui":
                $this->dic["ilTabs"]->activateTab(self::TAB_CONNECTION);
                $gui = $this->dic["config.connection.gui"];
                $this->dic["ilCtrl"]->forwardCommand($gui);
                break;
            case "iltgicgui":
                $this->dic["ilTabs"]->activateTab(self::TAB_TGIC);
                $gui = $this->dic["config.tgic.gui"];
                $this->dic["ilCtrl"]->forwardCommand($gui);
                break;
            case "ilwbdcsecuritygui":
                $this->dic["ilTabs"]->activateTab(self::TAB_SECURITY);
                $this->dic["ilCtrl"]->forwardCommand($this->dic["security.gui"]);
                break;
            case "ilsystemconfigurationgui":
                $this->dic["ilTabs"]->activateTab(self::TAB_WBD_SYSTEM);
                $this->dic["ilCtrl"]->forwardCommand($this->dic["config.system.gui"]);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_CONFIGURE:
                        $this->redirectConnectionSettings();
                        break;
                    default:
                        throw new Exception("Unknown command: " . $cmd);
                }
        }
    }


    protected function redirectConnectionSettings()
    {
        $this->dic["ilCtrl"]->redirectToURL($this->dic["config.connection.gui.link"]);
    }

    protected function setTabs()
    {
        $this->dic["ilTabs"]->addTab(
            self::TAB_CONNECTION,
            $this->getPluginObject()->txt(self::TAB_CONNECTION),
            $this->dic["config.connection.gui.link"]
        );

        $this->dic["ilTabs"]->addTab(
            self::TAB_WBD_SYSTEM,
            $this->getPluginObject()->txt(self::TAB_WBD_SYSTEM),
            $this->dic["config.system.gui.link"]
        );

        $this->dic["ilTabs"]->addTab(
            self::TAB_OPERATION_LIMITS,
            $this->getPluginObject()->txt(self::TAB_OPERATION_LIMITS),
            $this->dic["config.oplimits.gui.link"]
        );

        $this->dic["ilTabs"]->addTab(
            self::TAB_UDF,
            $this->getPluginObject()->txt(self::TAB_UDF),
            $this->dic["config.udf.gui.link"]
        );

        $this->dic["ilTabs"]->addTab(
            self::TAB_TGIC,
            $this->getPluginObject()->txt(self::TAB_TGIC),
            $this->dic["config.tgic.gui.link"]
        );

        $this->dic["ilTabs"]->addTab(
            self::TAB_SECURITY,
            $this->getPluginObject()->txt(self::TAB_SECURITY),
            $this->dic["security.gui.link"]
        );
    }

    protected function checkUserName()
    {
        $security_db = $this->dic["security.db"];
        if (!$security_db->loginEnabled($this->dic["plugin.id"])) {
            return true;
        }

        $username = $this->dic["ilUser"]->getLogin();
        if (
            $username == self::ROOT_LOGIN ||
            $security_db->checkUsername($username, $this->dic["plugin.id"])
        ) {
            return true;
        }

        return false;
    }
}
