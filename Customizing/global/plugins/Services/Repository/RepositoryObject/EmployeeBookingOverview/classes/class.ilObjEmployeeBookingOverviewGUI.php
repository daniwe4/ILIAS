<?php

declare(strict_types=1);

use CaT\Plugins\EmployeeBookingOverview\DI;

require_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/EmployeeBookingOverview/classes/Settings/class.ilEmployeeBookingOverviewSettingsGUI.php';
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/EmployeeBookingOverview/classes/class.ilEmployeeBookingOverviewReportGUI.php';
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/EmployeeBookingOverview/classes/class.ilEmployeeBookingOverviewUserAutoComplete.php';
/**
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS.
 *
 * @ilCtrl_isCalledBy ilObjEmployeeBookingOverviewGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjEmployeeBookingOverviewGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjEmployeeBookingOverviewGUI: ilEmployeeBookingOverviewSettingsGUI, ilEmployeeBookingOverviewReportGUI
 *
 * @author
 * @copyright Extended GPL, see LICENSE
 */
class ilObjEmployeeBookingOverviewGUI extends ilObjectPluginGUI
{
    use DI;

    const TAB_SETTINGS = 'setings';
    const TAB_REPORT = 'report';

    const CMD_TO_SETTINGS = 'to_settings';
    const CMD_TO_REPORT = 'to_report';

    /**
     * @var \Pimple\Container
     */
    protected $dic;

    /**
     * @var ilObjEmployeeBookingOverview
     */
    public $object;

    /**
    * Get type.  Same value as choosen in plugin.php
    */
    final public function getType()
    {
        return "xebo";
    }

    /**
    * Handles all commmands of this class, centralizes permission checks
    */
    public function performCommand()
    {
        $cmd = $this->getDic()["ilCtrl"]->getCmd();
        $next_class = $this->getDic()["ilCtrl"]->getNextClass();
        switch ($next_class) {
            case "ilemployeebookingoverviewsettingsgui":
                $this->toSettings();
                break;
            case "ilemployeebookingoverviewreportgui":
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
                        throw new Exception("Unknown command: " . $cmd);
                }
        }
        $this->setTitleAndDescription();
    }


    protected function toReport()
    {
        $this->activateTab(self::TAB_REPORT);
        $gui = $this->getDic()["report.base.gui"];
        $this->getDic()["ilCtrl"]->forwardCommand($gui);
    }

    protected function toSettings()
    {
        $this->activateTab(self::TAB_SETTINGS);
        $gui = $this->getDIC()["settings.gui"];
        $this->getDic()["ilCtrl"]->forwardCommand($gui);
    }

    protected function redirectToReport()
    {
        $link = $this->getDic()["ilCtrl"]->getLinkTargetByClass(
            'ilEmployeeBookingOverviewReportGUI',
            ilEmployeeBookingOverviewReportGUI::CMD_VIEW,
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    protected function redirectToSettings()
    {
        $link = $this->getDic()["settings.gui.link"];
        $this->getDIC()["ilCtrl"]->redirectToUrl($link);
    }

    public function setTabs()
    {
        $write = $this->getDic()["access.checker"]->canWrite();
        $edit_permission = $this->getDic()["access.checker"]->canEditPermissions();
        if ($write || $edit_permission) {
            // standard info screen tab
            $this->addInfoTab();
            // tab for the "show content" command
            if ($this->getDic()["access.checker"]->canRead()) {
                $this->getDic()["ilTabs"]->addTab(
                    self::TAB_REPORT,
                    $this->txt("content"),
                    $this->getDic()["ilCtrl"]->getLinkTargetByClass('ilEmployeeBookingOverviewReportGUI', ilEmployeeBookingOverviewReportGUI::CMD_VIEW)
                );
            }
            if ($write) {
                // a "properties" tab
                $this->getDic()["ilTabs"]->addTab(
                    self::TAB_SETTINGS,
                    $this->txt("properties"),
                    $this->getDic()["settings.gui.link"]
                );
            }
            // standard epermission tab
            $this->addPermissionTab();
        }
    }


    protected function activateTab($cmd)
    {
        $this->getDic()["ilTabs"]->activateTab($cmd);
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

    protected function getDIC() : \Pimple\Container
    {
        if (is_null($this->dic)) {
            global $DIC;
            $this->dic = $this->getObjectDIC($this->object, $DIC);
        }

        return $this->dic;
    }

    public static function _goto($a_target)
    {
        $ref_id = (int) $a_target[0];

        global $DIC;
        $ctrl = $DIC["ilCtrl"];
        $ctrl->setTargetScript("ilias.php");
        $ctrl->initBaseClass("ilobjplugindispatchgui");
        $ctrl->getCallStructure(strtolower("ilObjPluginDispatchGUI"));

        $ctrl->setParameterByClass("ilObjEmployeeBookingOverviewGUI", "ref_id", $ref_id);
        $link = $ctrl->getLinkTargetByClass(
            ["ilObjPluginDispatchGUI", "ilObjEmployeeBookingOverviewGUI"],
            ilObjEmployeeBookingOverviewGUI::CMD_TO_REPORT,
            "",
            false,
            false
        );

        $ctrl->clearParametersByClass("ilObjEmployeeBookingOverviewGUI");
        $ctrl->redirectToURL($link);
    }
}
