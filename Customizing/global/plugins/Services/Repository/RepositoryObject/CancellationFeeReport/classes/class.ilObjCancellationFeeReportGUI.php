<?php

/**
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS.
 *
 * @ilCtrl_isCalledBy ilObjCancellationFeeReportGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjCancellationFeeReportGUI: ilPermissionGUI, ilInfoScreenGUI
 * @ilCtrl_Calls ilObjCancellationFeeReportGUI: ilObjectCopyGUI,ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjCancellationFeeReportGUI: ilCancellationFeeReportSettingsGUI
 * @ilCtrl_Calls ilObjCancellationFeeReportGUI: ilCancellationFeeReportGUI
 *
 * @author
 * @copyright Extended GPL, see LICENSE
 */
class ilObjCancellationFeeReportGUI extends ilObjectPluginGUI
{
    const CMD_EDIT_PROPERTIES = "editProperties";
    const CMD_VIEW_REPORT = "viewReport";

    const TAB_SETTINGS = "settings";
    const TAB_REPORT = "report";

    /**
     * @var ilGlobalTemplateInterface
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
    * Get type. Same value as choosen in plugin.php.
    *
    * @return 	void
    */
    final public function getType()
    {
        return "xcfr";
    }

    /**
     * Handles all commmands of this class, centralizes permission checks.
     *
     * @param 	string 	$cmd
     * @return 	void
     */
    public function performCommand($cmd)
    {
        $cmd = $this->g_ctrl->getCmd();
        $next_class = $this->g_ctrl->getNextClass();

        $this->setTitleByline();
        switch ($next_class) {
            case 'ilcancellationfeereportsettingsgui':
                $this->forwardSettings();
                break;
            case 'ilcancellationfeereportgui':
                $this->forwardReport();
                break;
            default:
                switch ($cmd) {
                    case self::CMD_EDIT_PROPERTIES:
                        $this->redirectSettings();
                        break;
                    case self::CMD_VIEW_REPORT:
                        $this->redirectReport();
                        break;
                    default:
                        $this->redirectReport();
                }
        }
    }

    /**
     * After object has been created -> jump to this command.
     *
     * @return 	string
     */
    public function getAfterCreationCmd()
    {
        return self::CMD_EDIT_PROPERTIES;
    }

    /**
     * Get standard command.
     *
     * @return 	string
     */
    public function getStandardCmd()
    {
        return self::CMD_VIEW_REPORT;
    }

    /**
     * Forward to settings gui.
     *
     * @return 	void
     */
    protected function forwardSettings()
    {
        $this->g_tabs->activateTab(self::TAB_SETTINGS);
        $this->g_ctrl->forwardCommand(
            ilCancellationFeeReportPlugin::dic()['Settings.ilCancellationFeeReportSettingsGUI']
                ->withObject($this->object)
        );
    }

    /**
     * Forward to settings gui.
     *
     * @return 	void
     */
    protected function forwardReport()
    {
        $this->g_tabs->activateTab(self::TAB_REPORT);
        $this->g_ctrl->forwardCommand(
            ilCancellationFeeReportPlugin::dic()['ilCancellationFeeReportGUI']
                ->withObject($this->object)
        );
    }

    /**
     * Set the title byline to offline if object is offline.
     *
     * @return 	void
     */
    protected function setTitleByline()
    {
        if (ilObjCancellationFeeReportAccess::_isOffline($this->object->getId())) {
            $this->g_tpl->setAlertProperties(array(
            [
                "alert" => true,
                "property" => $this->g_lng->txt("status"),
                "value" => $this->g_lng->txt("offline")
            ]));
        }
    }

    protected function redirectSettings()
    {
        $link = $this->ctrl->getLinkTargetByClass(
            'ilCancellationFeeReportSettingsGUI',
            ilCancellationFeeReportSettingsGUI::CMD_VIEW,
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }


    protected function redirectReport()
    {
        $link = $this->ctrl->getLinkTargetByClass(
            'ilCancellationFeeReportGUI',
            ilCancellationFeeReportGUI::CMD_VIEW,
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
                    $this->g_ctrl->getLinkTargetByClass(
                        'ilCancellationFeeReportGUI',
                        ilCancellationFeeReportGUI::CMD_VIEW
                    )
                );
            }

            if ($write) {
                // a "properties" tab
                $this->g_tabs->addTab(
                    self::TAB_SETTINGS,
                    $this->plugin->txt("properties"),
                    $this->g_ctrl->getLinkTargetByClass(
                        'ilCancellationFeeReportSettingsGUI',
                        ilCancellationFeeReportSettingsGUI::CMD_VIEW
                    )
                );
            }

            // standard epermission tab
            $this->addPermissionTab();
        }
    }
}
