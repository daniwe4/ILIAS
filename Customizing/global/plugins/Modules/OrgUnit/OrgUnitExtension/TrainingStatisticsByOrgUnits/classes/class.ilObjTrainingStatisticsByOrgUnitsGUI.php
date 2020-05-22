<?php

declare(strict_types=1);

use CaT\Plugins\TrainingStatisticsByOrgUnits\DI;

/**
* @ilCtrl_isCalledBy ilObjTrainingStatisticsByOrgUnitsGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjTrainingStatisticsByOrgUnitsGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
* @ilCtrl_Calls ilObjTrainingStatisticsByOrgUnitsGUI: ilTSBOUSettingsGUI, ilTSBOUSingleViewGUI, ilTSBOUSplittedViewGUI
*/
class ilObjTrainingStatisticsByOrgUnitsGUI extends ilOrgUnitExtensionGUI
{
    use DI;

    const ID = "xtou";
    const CMD_EDIT_SETTINGS = "editSettings";
    const CMD_SHOW_REPORT = "showReport";

    const TAB_SETTINGS = "tab_settings";
    const TAB_REPORT_SINGLE_VIEW = "tab_report_single_view";
    const TAB_REPORT_SPLITTED_VIEW = "tab_report_splitted_view";

    /**
     * @var ilObjTrainingStatisticsByOrgUnits
     */
    public $object;
    /**
     * @var ArrayAccess
     */
    protected $dic;

    /**
     * @param $cmd
     * @throws Exception    if next_class or cmd are unknown
     */
    public function performCommand($cmd)
    {
        $next_class = $this->getDIC()["ilCtrl"]->getNextClass();
        switch ($next_class) {
            case "iltsbousettingsgui":
                $this->getDIC()["ilTabs"]->activateTab(self::TAB_SETTINGS);
                $gui = $this->getDIC()["settings.gui"];
                $this->getDIC()["ilCtrl"]->forwardCommand($gui);
                break;
            case "iltsbousingleviewgui":
                $this->getDIC()["ilTabs"]->activateTab(self::TAB_REPORT_SINGLE_VIEW);
                $gui = $this->getDIC()["report.singleview.gui"];
                $this->getDIC()["ilCtrl"]->forwardCommand($gui);
                break;
            case "iltsbousplittedviewgui":
                $this->getDIC()["ilTabs"]->activateTab(self::TAB_REPORT_SPLITTED_VIEW);
                $gui = $this->getDIC()["report.splittedview.gui"];
                $this->getDIC()["ilCtrl"]->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_EDIT_SETTINGS:
                        if ($this->getDIC()["ilAccess"]->checkAccess("write", "", $this->object->getRefId())) {
                            $this->redirectToSettings();
                            break;
                        }
                        $this->redirectToInfo();
                        break;
                    default:
                        throw new Exception("Unknown command: " . $cmd);
                }
        }
    }

    public function getType()
    {
        return self::ID;
    }

    public function getAfterCreationCmd()
    {
        return self::CMD_EDIT_SETTINGS;
    }

    public function getStandardCmd()
    {
        return self::CMD_EDIT_SETTINGS;
    }

    protected function setTabs()
    {
        $this->addInfoTab();

        $this->getDIC()["ilTabs"]->addTab(
            self::TAB_REPORT_SINGLE_VIEW,
            $this->txt(self::TAB_REPORT_SINGLE_VIEW),
            $this->getDIC()["report.singleview.gui.link"]
        );

        $this->getDIC()["ilTabs"]->addTab(
            self::TAB_REPORT_SPLITTED_VIEW,
            $this->txt(self::TAB_REPORT_SPLITTED_VIEW),
            $this->getDIC()["report.splittedview.gui.link"]
        );

        if ($this->getDIC()["ilAccess"]->checkAccess("write", "", $this->object->getRefId())) {
            $this->getDIC()["ilTabs"]->addTab(
                self::TAB_SETTINGS,
                $this->txt(self::TAB_SETTINGS),
                $this->getDIC()["settings.gui.link"]
            );
        }

        $this->addPermissionTab();
    }

    protected function redirectToSettings()
    {
        $this->getDic()["ilCtrl"]->redirectToUrl($this->getDic()["settings.gui.link"]);
    }

    protected function redirectToInfo()
    {
        $this->getDic()["ilCtrl"]->redirectToUrl($this->getDic()["info.link"]);
    }

    protected function getDic() : ArrayAccess
    {
        if (is_null($this->dic)) {
            global $DIC;
            $this->dic = $this->getObjectDIC($this->object, $DIC);
        }
        return $this->dic;
    }
}
