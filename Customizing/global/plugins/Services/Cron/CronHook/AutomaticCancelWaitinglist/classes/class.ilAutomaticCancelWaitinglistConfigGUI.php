<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

require_once "./Services/Component/classes/class.ilPluginConfigGUI.php";


use \CaT\Plugins\AutomaticCancelWaitinglist;

/**
 * @ilCtrl_Calls ilAutomaticCancelWaitinglistConfigGUI: ilCancelSuccessGUI, ilCancelFailGUI
 */
class ilAutomaticCancelWaitinglistConfigGUI extends ilPluginConfigGUI
{
    use AutomaticCancelWaitinglist\DI;

    const CMD_CONFIGURE = "configure";
    const TAB_SUCCESS = "success";
    const TAB_FAIL = "fail";

    /**
     * @var AutomaticCancelWaitinglist\DI
     */
    protected $dic;

    public function performCommand($cmd)
    {
        global $DIC;
        $this->dic = $this->getPluginDI($this->plugin_object, $DIC);

        require_once($this->dic["pluginpath"] . "/classes/Log/class.ilCancelSuccessGUI.php");
        require_once($this->dic["pluginpath"] . "/classes/Log/class.ilCancelFailGUI.php");

        $this->setTabs();
        $ctrl = $this->dic["ilCtrl"];
        $tabs = $this->dic["ilTabs"];
        $next_class = $ctrl->getNextClass();

        switch ($next_class) {
            case "ilcancelsuccessgui":
                $tabs->activateTab(self::TAB_SUCCESS);
                $gui = $this->dic["log.successgui"];
                $ctrl->forwardCommand($gui);
                break;
            case "ilcancelfailgui":
                $tabs->activateTab(self::TAB_FAIL);
                $gui = $this->dic["log.failgui"];
                $ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_CONFIGURE:
                        $this->redirectSuccess();
                        break;
                    default:
                        throw new Exception("ilTrainingProviderConfigGUI:: Unknown command: " . $cmd);
                }
        }
    }

    /**
     * Redirect to open requests
     *
     * @return void
     */
    protected function redirectSuccess()
    {
        $ctrl = $this->dic["ilCtrl"];
        $link = $ctrl->getLinkTargetByClass("ilCancelSuccessGUI", ilCancelSuccessGUI::CMD_VIEW_ENTRIES, '', false, false);
        ilUtil::redirect($link);
    }

    /**
     * Sets tabs for provider, trainer and tags
     *
     * @return null
     */
    protected function setTabs()
    {
        $ctrl = $this->dic["ilCtrl"];
        $tabs = $this->dic["ilTabs"];

        $success = $ctrl->getLinkTargetByClass(
            "ilCancelSuccessGUI",
            ilCancelSuccessGUI::CMD_VIEW_ENTRIES
        );
        $tabs->addTab(
            self::TAB_SUCCESS,
            call_user_func($this->dic["txtclsoure"], "conf_success"),
            $success
        );

        $fail = $ctrl->getLinkTargetByClass(
            "ilCancelFailGUI",
            ilCancelFailGUI::CMD_VIEW_ENTRIES
        );
        $tabs->addTab(
            self::TAB_FAIL,
            call_user_func($this->dic["txtclsoure"], "conf_fail"),
            $fail
        );
    }
}
