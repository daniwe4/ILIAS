<?php

/**
 * @ilCtrl_Calls ilSiglistConfigGUI: ilStaticConfigGUI
 * @ilCtrl_Calls ilSiglistConfigGUI: ilConfigurableOverviewGUI
 */
class ilSiglistConfigGUI
{
    const SUBTAB_STATIC_CONFIG = 'static_config';
    const SUBTAB_CONFIGURABLE_CONFIG = 'configurable_config';

    const CMD_SHOW = "cmd_show";

    /**
     * @var \ilCtrl
     */
    protected $ctrl;
    /**
     * @var \ilTabsGUI
     */
    protected $tabs;
    /**
     * @var \ilStaticConfigGUI
     */
    protected $static_config;
    /**
     * @var \ilConfigurableOverviewGUI
     */
    protected $configurable_overview;
    /**
     * @var \ilPlugin
     */
    protected $plugin;

    public function __construct(
        \ilCtrl $ctrl,
        \ilTabsGUI $tabs,
        \ilStaticConfigGUI $static_config,
        \ilConfigurableOverviewGUI $configurable_overview,
        \ilPlugin $plugin
    ) {
        $this->ctrl = $ctrl;
        $this->tabs = $tabs;
        $this->static_config = $static_config;
        $this->configurable_overview = $configurable_overview;
        $this->plugin = $plugin;
    }

    public function executeCommand()
    {
        $this->configureSubtabs();
        $next_class = $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd();
        switch ($next_class) {
            case 'ilstaticconfiggui':
                $this->tabs->activateSubTab(self::SUBTAB_STATIC_CONFIG);
                $this->forwardStaticConfig();
                break;
            case 'ilconfigurableoverviewgui':
                $this->tabs->activateSubTab(self::SUBTAB_CONFIGURABLE_CONFIG);
                $this->forwardConfigurableOverview();
                break;
            default:
                switch ($cmd) {
                    case self::CMD_SHOW:
                        $this->redirectToStaticList();
                        break;
                    default:
                        throw new Exception('unknown cmd ' . $cmd);
                }
        }
    }

    protected function redirectToStaticList()
    {
        require_once __DIR__ . "/StaticList/class.ilStaticConfigGUI.php";
        $link = $this->ctrl->getLinkTarget(
            $this->static_config,
            \ilStaticConfigGUI::CMD_SHOW,
            "",
            false,
            false
        );
        $this->ctrl->redirectToUrl($link);
    }

    protected function forwardStaticConfig()
    {
        $this->ctrl->forwardCommand($this->static_config);
    }

    protected function forwardConfigurableOverview()
    {
        $this->ctrl->forwardCommand($this->configurable_overview);
    }

    protected function configureSubtabs()
    {
        require_once __DIR__ . "/StaticList/class.ilStaticConfigGUI.php";
        $this->tabs->addSubTab(
            self::SUBTAB_STATIC_CONFIG,
            $this->plugin->txt('static_config'),
            $this->ctrl->getLinkTarget(
                $this->static_config,
                \ilStaticConfigGUI::CMD_SHOW
            )
        );

        require_once __DIR__ . "/ConfigurableList/class.ilConfigurableOverviewGUI.php";
        $this->tabs->addSubTab(
            self::SUBTAB_CONFIGURABLE_CONFIG,
            $this->plugin->txt('configurable_config'),
            $this->ctrl->getLinkTarget(
                $this->configurable_overview,
                \ilConfigurableOverviewGUI::CMD_SHOW
            )
        );
    }
}
