<?php declare(strict_types=1);

/**
 * @ilCtrl_Calls ilOrguByMailDomainConfigGUI: DomainConfigurationOverviewGUI
 * @ilCtrl_isCalledBy ilOrguByMailDomainConfigGUI: ilObjComponentSettingsGUI
 */

class ilOrguByMailDomainConfigGUI extends ilPluginConfigGUI
{
    protected $di;
    protected $tabs_gui;
    protected $ctrl;

    const TAB_ID_DOMAIN_CONFIGURATION = 'domain_configuration';

    public function __construct()
    {
        global $DIC;
        $this->di = $DIC;
        $this->tabs_gui = $this->di['ilTabs'];
        $this->ctrl = $this->di['ilCtrl'];
    }


    public function performCommand($cmd)
    {
        $this->getTabs();
        $next_class = $this->ctrl->getNextClass();
        switch ($next_class) {
            case 'domainconfigurationoverviewgui':
                $this->domainConfigurationOverviewGUI();
                break;
            default:
                $this->redirectToDomainConfigurationOverview();
        }
        return true;
    }

    protected function domainConfigurationOverviewGUI()
    {
        $this->tabs_gui->setTabActive(self::TAB_ID_DOMAIN_CONFIGURATION);
        $this->ctrl->forwardCommand($this->di['OrguByMailDomain.DomainConfigurationOverviewGUI']);
    }

    protected function redirectToDomainConfigurationOverview()
    {
        $this->ctrl->redirect($this->di['OrguByMailDomain.DomainConfigurationOverviewGUI'], DomainConfigurationOverviewGUI::CMD_OVERVIEW);
    }

    protected function getTabs()
    {
        $this->tabs_gui->addTab(
            self::TAB_ID_DOMAIN_CONFIGURATION,
            $this->di['OrguByMailDomain.plugin']->txt('domain_configuration_overview'),
            $this->getLinkTarget(self::TAB_ID_DOMAIN_CONFIGURATION)
        );
    }

    protected function getLinkTarget(string $id) : string
    {
        switch ($id) {
            case self::TAB_ID_DOMAIN_CONFIGURATION:
                return $this->ctrl->getLinkTargetByClass(
                    [DomainConfigurationOverviewGUI::class],
                    DomainConfigurationOverviewGUI::CMD_OVERVIEW
                );
            default:
                throw new \InvalidArgumentException('unknown tab id ' . $id);
        }
    }
}
