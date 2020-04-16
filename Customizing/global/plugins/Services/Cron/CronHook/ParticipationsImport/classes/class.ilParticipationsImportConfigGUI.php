<?php

/**
 * @ilCtrl_Calls ilParticipationsImportConfigGUI: ilPIFilesystemConfiguratorGUI
 * @ilCtrl_Calls ilParticipationsImportConfigGUI: ilPIDataSourcesConfiguratorGUI
 * @ilCtrl_Calls ilParticipationsImportConfigGUI: ilPIMappingsConfiguratorGUI
 * @ilCtrl_isCalledBy ilParticipationsImportConfigGUI: ilObjComponentSettingsGUI
 */

class ilParticipationsImportConfigGUI extends ilPluginConfigGUI
{
    const TAB_FILESYSTEM_CONFIG = 'filesystem_config';
    const TAB_DATA_SOURCES_CONFIG = 'data_sources_config';
    const TAB_MAPPINGS_CONFIG = 'mappings_config';

    public function __construct()
    {
        global $DIC;
        $this->tabs_gui = $DIC['ilTabs'];
        $this->ctrl = $DIC['ilCtrl'];
    }

    protected function txt($key)
    {
        return $this->getPluginObject()->txt($key);
    }

    public function performCommand($cmd)
    {
        $this->getTabs();
        $next_class = $this->ctrl->getNextClass();
        switch ($next_class) {
            case 'ilpifilesystemconfiguratorgui':
                $this->filesystemConfig();
                break;
            case 'ilpidatasourcesconfiguratorgui':
                $this->dataSourcesConfig();
                break;
            case 'ilpimappingsconfiguratorgui':
                $this->mappingConfig();
                break;
            default:
                switch ($cmd) {
                    case "configure":
                        $this->redirectFilesystem();
                        break;
                    default:
                        throw new Exception("invalid command $cmd");
                }
                break;
        }
        return true;
    }

    protected function redirectFilesystem()
    {
        $this->ctrl->redirectByClass(
            ['ilParticipationsImportConfigGUI','ilPIFilesystemConfiguratorGUI'],
            ilPIFilesystemConfiguratorGUI::CMD_SHOW
        );
    }

    protected function filesystemConfig()
    {
        $this->tabs_gui->setTabActive(self::TAB_FILESYSTEM_CONFIG);
        $this->ctrl->forwardCommand(
            $this->getPluginObject()->dic()['ilPIFilesystemConfiguratorGUI']
        );
    }

    protected function dataSourcesConfig()
    {
        $this->tabs_gui->setTabActive(self::TAB_DATA_SOURCES_CONFIG);
        $this->ctrl->forwardCommand(
            $this->getPluginObject()->dic()['ilPIDataSourcesConfiguratorGUI']
        );
    }

    protected function mappingConfig()
    {
        $this->tabs_gui->setTabActive(self::TAB_MAPPINGS_CONFIG);
        $this->ctrl->forwardCommand(
            $this->getPluginObject()->dic()['ilPIMappingsConfiguratorGUI']
        );
    }

    protected function getTabs()
    {
        $this->tabs_gui->addTab(
            self::TAB_FILESYSTEM_CONFIG,
            $this->txt(self::TAB_FILESYSTEM_CONFIG),
            $this->getLinkTarget(self::TAB_FILESYSTEM_CONFIG)
        );
        $this->tabs_gui->addTab(
            self::TAB_MAPPINGS_CONFIG,
            $this->txt(self::TAB_MAPPINGS_CONFIG),
            $this->getLinkTarget(self::TAB_MAPPINGS_CONFIG)
        );
        $this->tabs_gui->addTab(
            self::TAB_DATA_SOURCES_CONFIG,
            $this->txt(self::TAB_DATA_SOURCES_CONFIG),
            $this->getLinkTarget(self::TAB_DATA_SOURCES_CONFIG)
        );
    }


    protected function getLinkTarget($target)
    {
        switch ($target) {
            case self::TAB_MAPPINGS_CONFIG:
                return $this->ctrl->getLinkTargetByClass(
                    'ilPIMappingsConfiguratorGUI',
                    ilPIMappingsConfiguratorGUI::CMD_SHOW
                );
            case self::TAB_FILESYSTEM_CONFIG:
                return $this->ctrl->getLinkTargetByClass(
                    'ilPIFilesystemConfiguratorGUI',
                    ilPIFilesystemConfiguratorGUI::CMD_SHOW
                );
            case self::TAB_DATA_SOURCES_CONFIG:
                return $this->ctrl->getLinkTargetByClass(
                    'ilPIDataSourcesConfiguratorGUI',
                    ilPIDataSourcesConfiguratorGUI::CMD_SHOW
                );
            default:
                throw new InvalidArgumentException('invalid target ' . $target);
        }
    }
}
