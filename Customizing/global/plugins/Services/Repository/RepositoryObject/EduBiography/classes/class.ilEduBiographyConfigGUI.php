<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */



use CaT\Plugins\EduBiography\DI;

/**
 * @ilCtrl_Calls ilEduBiographyConfigGUI: ilScheduleOverviewGUI
 * @ilCtrl_Calls ilEduBiographyConfigGUI: ilXEBRSecurityGUI
 */
class ilEduBiographyConfigGUI extends ilPluginConfigGUI
{
    use DI;

    const ROOT_LOGIN = "root";

    const TAB_SCHEDULES = "schedules";
    const TAB_SECURITY = "security";
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
     * @var Closure
     */
    protected $txt;

    /**
     * @param string $cmd
     * @throws ilCtrlException
     */
    public function performCommand($cmd)
    {
        if (!$this->checkUserName()) {
            ilUtil::sendFailure($this->getPluginObject()->txt("no_permission"), true);
            $this->getDIC()["ilCtrl"]->redirectToURL($this->getDIC()["admin.plugin.link"]);
        }

        $this->ctrl = $this->getDIC()["ilCtrl"];
        $this->tabs = $this->getDIC()["ilTabs"];

        $this->setTabs();

        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
            case "ilscheduleoverviewgui":
                $this->tabs->activateTab(self::TAB_SCHEDULES);
                $gui = $this->getDIC()["config.schedules.overview.gui"];
                $this->ctrl->forwardCommand($gui);
                break;
            case "ilxebrsecuritygui":
                $this->getDIC()["ilTabs"]->activateTab(self::TAB_SECURITY);
                $gui = $this->getDIC()["security.gui"];
                $this->getDIC()["ilCtrl"]->forwardCommand($gui);
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
        $security_link = $this->getDIC()["security.gui.link"];
        $this->tabs->addTab(self::TAB_SECURITY, $this->txt(self::TAB_SECURITY), $security_link);

        $link = $this->getDIC()["config.schedules.overview.link"];
        $this->tabs->addTab(self::TAB_SCHEDULES, $this->txt(self::TAB_SCHEDULES), $link);
    }

    protected function defaultForwarding()
    {
        $link = $this->dic["config.schedules.overview.link"];
        $this->ctrl->redirectToURL($link);
    }

    protected function checkUserName()
    {
        $security_db = $this->getDIC()["security.db"];
        if (!$security_db->loginEnabled($this->getDIC()["plugin.id"])) {
            return true;
        }

        $username = $this->getDIC()["ilUser"]->getLogin();
        if (
            $username == self::ROOT_LOGIN ||
            $security_db->checkUsername($username, $this->getDIC()["plugin.id"])
        ) {
            return true;
        }

        return false;
    }

    protected function txt(string $code) : string
    {
        if (is_null($this->txt)) {
            $this->txt = $this->getDIC()["txtclosure"];
        }
        return call_user_func($this->txt, $code);
    }
}
