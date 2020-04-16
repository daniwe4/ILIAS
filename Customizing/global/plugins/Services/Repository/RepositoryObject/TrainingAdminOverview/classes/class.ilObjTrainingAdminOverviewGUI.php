<?php

declare(strict_types=1);

require_once __DIR__ . "/Settings/class.ilTrainingAdminOverviewSettingsGUI.php";
require_once __DIR__ . "/AdministratedTrainings/class.ilAdministratedTrainingsGUI.php";

use ILIAS\TMS\CourseCreation\ilCourseTemplateDB;

/**
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS
 *
 * @ilCtrl_isCalledBy ilObjTrainingAdminOverviewGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjTrainingAdminOverviewGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjTrainingAdminOverviewGUI: ilTrainingAdminOverviewSettingsGUI, ilAdministratedTrainingsGUI
 */
class ilObjTrainingAdminOverviewGUI extends ilObjectPluginGUI
{
    const CMD_SHOW_CONTENT = "showContent";

    /**
     * Property of parent gui
     *
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilAccess
     */
    protected $access;


    /**
     * Called after parent constructor. It's possible to define some plugin special values
     */
    protected function afterConstructor()
    {
        global $DIC;

        $this->tabs = $DIC->tabs();
        $this->access = $DIC->access();
        $this->tree = $DIC->repositoryTree();
        $this->obj_definition = $DIC["objDefinition"];
    }

    /**
    * Get type.  Same value as choosen in plugin.php
    */
    final public function getType()
    {
        return "xado";
    }

    /**
    * Handles all commmands of this class, centralizes permission checks
    */
    public function performCommand($cmd)
    {
        $next_class = $this->ctrl->getNextClass();
        $this->activateTab($cmd);

        switch ($next_class) {
            case "iltrainingadminoverviewsettingsgui":
                $gui = new ilTrainingAdminOverviewSettingsGUI($this, $this->object->getActions());
                $this->ctrl->forwardCommand($gui);
                break;
            case "iladministratedtrainingsgui":
                if (!$this->access->checkAccess("write", "", $this->object->getRefId())) {
                    $this->tabs->clearTargets();
                }
                $crs_template_db = new ilCourseTemplateDB($this->tree, $this->obj_definition);
                $gui = new ilAdministratedTrainingsGUI($this, $this->object->getActions(), $crs_template_db);
                $this->ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_SHOW_CONTENT:
                        if ($this->access->checkAccess("write", "", $this->object->getRefId())) {
                            $this->redirectSettingsTab(ilTrainingAdminOverviewSettingsGUI::CMD_EDIT_PROPERTIES);
                        } else {
                            $this->redirectInfotab();
                        }
                        break;
                    case ilTrainingAdminOverviewSettingsGUI::CMD_EDIT_PROPERTIES:
                        $this->redirectSettingsTab($cmd);
                        break;
                    case ilAdministratedTrainingsGUI::CMD_SHOW_ASSIGNMENTS:
                        $this->redirectAssignedTrainings($cmd);
                        break;
                    default:
                        throw new Exception(__METHOD__ . " unknown command " . $cmd);
                }
        }
    }

    /**
     * @return int
     */
    public function getObjectRefId()
    {
        return (int) $this->object->getRefId();
    }

    /**
    * After object has been created -> jump to this command
    */
    public function getAfterCreationCmd()
    {
        return ilTrainingAdminOverviewSettingsGUI::CMD_EDIT_PROPERTIES;
    }

    /**
    * Get standard command
    */
    public function getStandardCmd()
    {
        return self::CMD_SHOW_CONTENT;
    }

    /**
     * Redirect via link to settings tab
     *
     * @return null
     */
    protected function redirectSettingsTab($cmd)
    {
        $link = $this->ctrl->getLinkTargetByClass(
            array("ilObjTrainingAdminOverviewGUI", "ilTrainingAdminOverviewSettingsGUI"),
            $cmd,
            "",
            false,
            false
        );

        \ilUtil::redirect($link);
    }

    /**
     * Redirect via link to settings tab
     *
     * @return null
     */
    protected function redirectAssignedTrainings($cmd)
    {
        $link = $this->ctrl->getLinkTargetByClass(
            array("ilObjTrainingAdminOverviewGUI", "ilAdministratedTrainingsGUI"),
            $cmd,
            "",
            false,
            false
        );

        \ilUtil::redirect($link);
    }

    /**
     * Redirect via link to Info tab
     *
     * @return null
     */
    protected function redirectInfoTab()
    {
        $link = $this->ctrl->getLinkTargetByClass(
            array("ilObjTrainingAdminOverviewGUI", "ilInfoScreenGUI"),
            "showSummary",
            "",
            false,
            false
        );

        \ilUtil::redirect($link);
    }

    /**
     * Set the tabs for the site and activate current
     *
     * @param string 	$cmd
     *
     * @return null
     */
    protected function setTabs()
    {
        $settings = $this->object->getSettings();
        if ($settings->getShowInfoTab()) {
            $this->addInfoTab();
        }

        $report = $this->ctrl->getLinkTargetByClass(
            [
                "ilObjTrainingAdminOverviewGUI",
                "ilAdministratedTrainingsGUI"
            ],
            ilAdministratedTrainingsGUI::CMD_SHOW_ASSIGNMENTS
        );

        $settings = $this->ctrl->getLinkTargetByClass(
            [
                "ilObjTrainingAdminOverviewGUI",
                "ilTrainingAdminOverviewSettingsGUI"
            ],
            ilTrainingAdminOverviewSettingsGUI::CMD_EDIT_PROPERTIES
        );

        if ($this->access->checkAccess("write", "", $this->object->getRefId())) {
            $this->tabs->addTab(
                ilAdministratedTrainingsGUI::CMD_SHOW_ASSIGNMENTS,
                $this->txt("tab_report"),
                $report
            );
            $this->tabs->addTab(
                ilTrainingAdminOverviewSettingsGUI::CMD_EDIT_PROPERTIES,
                $this->txt("tab_settings"),
                $settings
            );
        }

        $this->addPermissionTab();
    }

    /**
     * Set current tab active
     *
     * @param string 	$tab_name
     *
     * @return void
     */
    protected function activateTab($cmd)
    {
        $this->tabs->activateTab($cmd);
    }

    /**
    * Goto redirection
    */
    public static function _goto($a_target)
    {
        global $DIC;
        $ctrl = $DIC->ctrl();
        $access = $DIC->access();

        $t = explode("_", $a_target[0]);
        $ref_id = (int) $t[0];
        $class_name = $a_target[1];
        if ($access->checkAccess("visible", "", $ref_id)
            && $access->checkAccess("read", "", $ref_id)
        ) {
            $ctrl->initBaseClass("ilObjPluginDispatchGUI");
            $ctrl->setTargetScript("ilias.php");
            $ctrl->getCallStructure(strtolower("ilObjPluginDispatchGUI"));
            $ctrl->setParameterByClass($class_name, "ref_id", $ref_id);
            $ctrl->redirectByClass(
                [
                    "ilobjplugindispatchgui",
                    $class_name,
                    "iladministratedtrainingsgui"
                ],
                ilAdministratedTrainingsGUI::CMD_SHOW_ASSIGNMENTS
            );
        }

        parent::_goto($a_target);
    }
}
