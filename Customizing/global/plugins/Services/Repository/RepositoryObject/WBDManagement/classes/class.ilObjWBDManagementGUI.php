<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

require_once "./Services/Repository/classes/class.ilObjectPluginGUI.php";

use CaT\Plugins\WBDManagement\DI;

/**
 * @ilCtrl_isCalledBy ilObjWBDManagementGUI: ilRepositoryGUI
 * @ilCtrl_isCalledBy ilObjWBDManagementGUI: ilAdministrationGUI
 * @ilCtrl_isCalledBy ilObjWBDManagementGUI: ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjWBDManagementGUI: ilPermissionGUI
 * @ilCtrl_Calls ilObjWBDManagementGUI: ilInfoScreenGUI
 * @ilCtrl_Calls ilObjWBDManagementGUI: ilObjectCopyGUI
 * @ilCtrl_Calls ilObjWBDManagementGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjWBDManagementGUI: ilGutBeratenGUI
 * @ilCtrl_Calls ilObjWBDManagementGUI: ilWBDManagementSettingsGUI
 * @ilCtrl_Calls ilObjWBDManagementGUI: ilWBDReportGUI
 * @ilCtrl_Calls ilObjWBDManagementGUI: ilNotYetReportedGUI
 * @ilCtrl_Calls ilObjWBDManagementGUI: ilNotYetCancelledGUI
 */
class ilObjWBDManagementGUI extends ilObjectPluginGUI
{
    use DI;

    const CMD_EDIT_PROPERTIES = "editProperties";
    const CMD_PERMISSIONS = "showPermissions";
    const CMD_RENDER_REPORT = "renderReport";
    const CMD_SHOW = "showContent";
    const CMD_INFO = "infoScreen";

    const TAB_GUT_BERATEN = "gut_beraten";
    const TAB_REPORT = "report";
    const TAB_NOT_YET_REPORTED = "not_yet_reported";
    const TAB_NOT_YET_CANCELLED = "not_yet_cancelled";
    const TAB_SETTINGS = "settings";

    /**
     * @var Pimple\Container
     */
    protected $dic;

    protected function afterConstructor()
    {
        global $DIC;

        if (!is_null($this->object)) {
            $this->dic = $this->getObjectDIC($this->object, $DIC);
            $this->dic["lng"]->loadLanguageModule("xwbm");
        }

        $this->lng->loadLanguageModule("xwbm");
    }

    final public function getType()
    {
        return "xwbm";
    }

    /**
     * @throws Exception
     */
    public function performCommand()
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass();

        if ($cmd == null) {
            $cmd = self::CMD_SHOW;
        }

        $this->setTitleByline();
        switch ($next_class) {
            case "ilgutberatengui":
                $this->forwardGutBeraten();
                break;
            case "ilwbdreportgui":
                $this->forwardReport();
                break;
            case "ilnotyetreportedgui":
                $this->forwardNotYetReported();
                break;
            case "ilnotyetcancelledgui":
                $this->forwardNotYetCancelled();
                break;
            case 'ilwbdmanagementsettingsgui':
                $this->forwardSettings();
                break;
            default:
                switch ($cmd) {
                    case self::CMD_SHOW:
                    case self::CMD_EDIT_PROPERTIES:
                        if ($this->dic["ilAccess"]->checkAccess("write", "", $this->object->getRefId())) {
                            $this->forwardSettings();
                        } elseif ($this->dic["ilAccess"]->checkAccess("order_time_transmission", "", $this->object->getRefId())) {
                            $this->forwardGutBeraten();
                        } elseif ($this->dic["ilAccess"]->checkAccess("view_wbd_report", "", $this->object->getRefId())) {
                            $this->forwardReport();
                        } else {
                            $this->redirectInfo();
                        }
                        break;
                    default:
                        throw new Exception("ilObjWBDManagementGUI: Unknown command: " . $cmd);
                }
        }
    }

    public function getAfterCreationCmd()
    {
        return self::CMD_SHOW;
    }

    public function getStandardCmd()
    {
        return self::CMD_SHOW;
    }

    /**
     * @throws ilCtrlException
     */
    protected function forwardGutBeraten()
    {
        $this->tabs->activateTab(self::TAB_GUT_BERATEN);
        $gui = $this->dic["gut.beraten.gui"];
        $this->ctrl->forwardCommand($gui);
    }

    /**
     * @throws ilCtrlException
     */
    protected function forwardReport()
    {
        $this->tabs->activateTab(self::TAB_REPORT);
        $gui = $this->dic["reports.gui"];
        $this->ctrl->forwardCommand($gui);
    }

    /**
     * @throws ilCtrlException
     */
    protected function forwardNotYetReported()
    {
        $this->tabs->activateTab(self::TAB_NOT_YET_REPORTED);
        $gui = $this->dic["reports.not_yet_reported.gui"];
        $this->ctrl->forwardCommand($gui);
    }

    /**
     * @throws ilCtrlException
     */
    protected function forwardNotYetCancelled()
    {
        $this->tabs->activateTab(self::TAB_NOT_YET_CANCELLED);
        $gui = $this->dic["reports.not_yet_cancelled.gui"];
        $this->ctrl->forwardCommand($gui);
    }

    /**
     * @throws ilCtrlException
     */
    protected function forwardSettings()
    {
        $this->tabs->activateTab(self::TAB_SETTINGS);
        $gui = $this->dic["settings.gui"];
        $this->ctrl->forwardCommand($gui);
    }

    protected function redirectInfo()
    {
        $link = $this->dic["info.gui.link"];
        $this->dic["ilCtrl"]->redirectToUrl($link);
    }

    /**
     * Set the title byline to offline if object is offline.
     *
     * @return 	void
     */
    protected function setTitleByline()
    {
        require_once(__DIR__ . "/class.ilObjWBDManagementAccess.php");

        if (ilObjWBDManagementAccess::_isOffline($this->obj_id)) {
            $this->tpl->setAlertProperties(array(
            [
                "alert" => true,
                "property" => $this->lng->txt("status"),
                "value" => $this->lng->txt("offline")
            ]));
        }
    }

    protected function setTabs()
    {
        $this->addInfoTab();

        $gut_beraten = $this->dic["gut.beraten.gui.link"];
        $report = $this->dic["reports.gui.link"];
        $not_yet_reported = $this->dic["reports.not_yet_reported.link"];
        $not_yet_cancelled = $this->dic["reports.not_yet_cancelled.link"];
        $settings = $this->dic["settings.gui.link"];

        if (
            $this->dic["ilAccess"]->checkAccess("visible", "", $this->object->getRefId()) &&
            $this->dic["ilAccess"]->checkAccess("read", "", $this->object->getRefId())
        ) {
            $this->tabs->addTab(self::TAB_GUT_BERATEN, $this->txt("gut_beraten"), $gut_beraten);
        }

        if ($this->dic["ilAccess"]->checkAccess("view_wbd_report", "", $this->object->getRefId())) {
            $this->tabs->addTab(self::TAB_REPORT, $this->txt("report"), $report);
        }

        if ($this->dic["ilAccess"]->checkAccess("view_wbd_report", "", $this->object->getRefId())) {
            $this->tabs->addTab(self::TAB_NOT_YET_REPORTED, $this->txt("not_yet_reported"), $not_yet_reported);
        }

        if ($this->dic["ilAccess"]->checkAccess("view_wbd_report", "", $this->object->getRefId())) {
            $this->tabs->addTab(self::TAB_NOT_YET_CANCELLED, $this->txt("not_yet_cancelled"), $not_yet_cancelled);
        }

        if ($this->dic["ilAccess"]->checkAccess("write", "", $this->object->getRefId())) {
            $this->tabs->addTab(self::TAB_SETTINGS, $this->txt("settings"), $settings);
        }

        $this->addPermissionTab();
    }

    public static function _goto($a_target)
    {
        $ref_id = (int) $a_target[0];

        $script = self::getForwardScript($ref_id);
        ilUtil::redirect($script);
    }

    protected static function getForwardScript($ref_id)
    {
        global $DIC;
        $ctrl = $DIC["ilCtrl"];
        $ctrl->setTargetScript("ilias.php");
        $ctrl->initBaseClass("ilobjplugindispatchgui");

        $ctrl->setParameterByClass("ilGutBeratenGUI", "cmd", self::CMD_SHOW);
        $ctrl->setParameterByClass("ilGutBeratenGUI", "ref_id", $ref_id);
        $link = $ctrl->getLinkTargetByClass(array("ilobjplugindispatchgui", "ilObjWBDManagementGUI", "ilGutBeratenGUI"), "", "", false, false);
        $ctrl->clearParametersByClass("ilGutBeratenGUI");

        return $link;
    }
}
