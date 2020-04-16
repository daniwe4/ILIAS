<?php
include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/TrainingStatistics/classes/Settings/class.ilTrainingStatisticsSettingsGUI.php';
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/TrainingStatistics/classes/class.ilTrainingStatisticsReportGUI.php';
/**
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS.
 *
 * @ilCtrl_isCalledBy ilObjTrainingStatisticsGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjTrainingStatisticsGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI, ilTrainingStatisticsSettingsGUI
 * @ilCtrl_Calls ilObjTrainingStatisticsGUI: ilTrainingStatisticsSettingsGUI, ilTrainingStatisticsReportGUI
 *
 * @author
 * @copyright Extended GPL, see LICENSE
 */
class ilObjTrainingStatisticsGUI extends ilObjectPluginGUI
{
    const TAB_SETTINGS = 'setings';
    const TAB_REPORT = 'report';

    const CMD_TO_SETTINGS = 'to_settings';
    const CMD_TO_REPORT = 'to_report';

    /**
     * @var ilTemplate
     */
    protected $g_tpl;

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ilTabs
     */
    protected $g_tabs;

    /**
     * @var ilToolbarGUI
     */
    protected $g_toolbar;

    /**
     * @var ilObjUser
     */
    protected $g_usr;

    /**
     * @var ilAccessHandler
     */
    protected $g_access;

    /**
     * @var ilLanguage
     */
    protected $g_lng;

    /**
     * Called after parent constructor. It's possible to define some plugin special values.
     *
     * @return 	void
     */
    protected function afterConstructor()
    {
        global $DIC;

        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tabs = $DIC->tabs();
        $this->g_toolbar = $DIC->toolbar();
        $this->g_usr = $DIC->user();
        $this->g_access = $DIC->access();
        $this->g_lng = $DIC->language();
    }
    /**
    * Get type.  Same value as choosen in plugin.php
    */
    final public function getType()
    {
        return "xrts";
    }

    /**
    * Handles all commmands of this class, centralizes permission checks
    */
    public function performCommand()
    {
        $cmd = $this->g_ctrl->getCmd();
        $this->cmd = $cmd;
        switch ($this->g_ctrl->getNextClass()) {
            case "iltrainingstatisticssettingsgui":
                $this->toSettings();
                break;
            case "iltrainingstatisticsreportgui":
                $this->toReport();
                break;
            default:
                switch ($cmd) {
                    case self::CMD_TO_SETTINGS:
                        $this->redirectToSettings();
                        break;
                    case self::CMD_TO_REPORT:
                        $this->redirectToReport();
                        break;
                    default:
                        $this->redirectToReport();
                }
        }
        $this->setTitleAndDescription();
    }


    protected function toReport()
    {
        $this->activateTab(self::TAB_REPORT);
        $gui = new ilTrainingStatisticsReportGUI($this, $this->plugin, (int) $this->object->getRefId(), $this->object->report());
        $this->g_ctrl->forwardCommand($gui);
    }

    protected function toSettings()
    {
        $this->activateTab(self::TAB_SETTINGS);
        $gui = new ilTrainingStatisticsSettingsGUI($this, $this->plugin, $this->object);
        $this->g_ctrl->forwardCommand($gui);
    }

    protected function redirectToReport()
    {
        $link = $this->ctrl->getLinkTargetByClass(
            'ilTrainingStatisticsReportGUI',
            ilTrainingStatisticsReportGUI::CMD_VIEW,
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    protected function redirectToSettings()
    {
        $link = $this->ctrl->getLinkTargetByClass(
            'ilTrainingStatisticsSettingsGUI',
            ilTrainingStatisticsSettingsGUI::CMD_VIEW,
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    public function setTabs()
    {
        $write = $this->g_access->checkAccess("write", "", $this->object->getRefId());
        $edit_permission = $this->g_access->checkAccess("edit_permission", "", $this->object->getRefId());
        if ($write || $edit_permission) {
            // standard info screen tab
            $this->addInfoTab();
            // tab for the "show content" command
            if ($this->g_access->checkAccess("read", "", $this->object->getRefId())) {
                $this->g_tabs->addTab(
                    self::TAB_REPORT,
                    $this->plugin->txt("content"),
                    $this->g_ctrl->getLinkTargetByClass('ilTrainingStatisticsReportGUI', ilTrainingStatisticsReportGUI::CMD_VIEW)
                );
            }
            if ($write) {
                // a "properties" tab
                $this->g_tabs->addTab(
                    self::TAB_SETTINGS,
                    $this->plugin->txt("properties"),
                    $this->g_ctrl->getLinkTargetByClass('ilTrainingStatisticsSettingsGUI', ilTrainingStatisticsSettingsGUI::CMD_VIEW)
                );
            }
            // standard epermission tab
            $this->addPermissionTab();
        }
    }


    protected function activateTab($cmd)
    {
        $this->g_tabs->activateTab($cmd);
    }

    /**
    * After object has been created -> jump to this command
    */
    public function getAfterCreationCmd()
    {
        return self::CMD_TO_SETTINGS;
    }

    /**
    * Get standard command
    */
    public function getStandardCmd()
    {
        return self::CMD_TO_REPORT;
    }
}
