<?php

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once(__DIR__ . "/Settings/class.ilUserBookingsSettingsGUI.php");
require_once(__DIR__ . "/UserBooking/class.ilUserBookingsGUI.php");
require_once(__DIR__ . "/SuperiorView/class.ilSuperiorViewGUI.php");

use CaT\Plugins\UserBookings\Helper;

/**
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS
 *
 * @ilCtrl_isCalledBy ilObjUserBookingsGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjUserBookingsGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjUserBookingsGUI: ilUserBookingsSettingsGUI, ilUserBookingsGUI, ilSuperiorViewGUI
 */
class ilObjUserBookingsGUI extends ilObjectPluginGUI
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
    protected $g_tabs;

    /**
     * @var ilAccess
     */
    protected $g_access;

    /**
     * Called after parent constructor. It's possible to define some plugin special values
     */
    protected function afterConstructor()
    {
        global $DIC;

        $this->g_tabs = $DIC->tabs();
        $this->g_access = $DIC->access();
    }

    /**
    * Get type.  Same value as choosen in plugin.php
    */
    final public function getType()
    {
        return "xubk";
    }

    /**
    * Handles all commmands of this class, centralizes permission checks
    */
    public function performCommand($cmd)
    {
        $next_class = $this->ctrl->getNextClass();
        $this->activateTab($cmd);
        switch ($next_class) {
            case "iluserbookingssettingsgui":
                $gui = new ilUserBookingsSettingsGUI($this, $this->object->getActions());
                $this->ctrl->forwardCommand($gui);
                break;
            case "iluserbookingsgui":
                $helper = new Helper();
                $gui = new ilUserBookingsGUI($this, $this->object->getActions(), $helper);
                $this->ctrl->forwardCommand($gui);
                break;
            case "ilsuperiorviewgui":
                $helper = new Helper();
                $gui = new ilSuperiorViewGUI($this, $this->object->getActions(), $helper);
                $this->ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_SHOW_CONTENT:
                        if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
                            $this->redirectSettingsTab(ilUserBookingsSettingsGUI::CMD_EDIT_PROPERTIES);
                        } else {
                            $this->redirectInfotab();
                        }
                        break;
                    case ilUserBookingsSettingsGUI::CMD_EDIT_PROPERTIES:
                        $this->redirectSettingsTab($cmd);
                        break;
                    case ilUserBookingsGUI::CMD_SHOW_BOOKINGS:
                        $this->redirectBookings($cmd);
                        break;
                    case ilSuperiorViewGUI::CMD_SHOW_BOOKINGS:
                        $this->redirectSuperiorView($cmd);
                        break;
                    default:
                        throw new Exception(__METHOD__ . " unknown command " . $cmd);
                }
        }
    }

    /**
    * After object has been created -> jump to this command
    */
    public function getAfterCreationCmd()
    {
        return ilUserBookingsSettingsGUI::CMD_EDIT_PROPERTIES;
    }

    /**
    * Get standard command
    */
    public function getStandardCmd()
    {
        if($this->object->getSettings()->getSuperiorView()) {
            return ilSuperiorViewGUI::CMD_SHOW_BOOKINGS;
        }
        return ilUserBookingsGUI::CMD_SHOW_BOOKINGS;
    }

    /**
     * Redirect via link to settings tab
     *
     * @return null
     */
    protected function redirectSettingsTab($cmd)
    {
        $link = $this->ctrl->getLinkTargetByClass(
            array("ilObjUserBookingsGUI", "ilUserBookingsSettingsGUI"),
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
    protected function redirectBookings($cmd)
    {
        $link = $this->ctrl->getLinkTargetByClass(
            array("ilObjUserBookingsGUI", "ilUserBookingsGUI"),
            $cmd,
            "",
            false,
            false
        );

        \ilUtil::redirect($link);
    }

    /**
     * Redirect via link to superior view
     *
     * @return null
     */
    protected function redirectSuperiorView($cmd)
    {
        $link = $this->ctrl->getLinkTargetByClass(
            array("ilObjUserBookingsGUI", "ilSuperiorViewGUI"),
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
            array("ilObjUserBookingsGUI", "ilInfoScreenGUI"),
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
        $this->addInfoTab();
        $settings = $this->ctrl->getLinkTargetByClass(array("ilObjUserBookingsGUI", "ilUserBookingsSettingsGUI"), ilUserBookingsSettingsGUI::CMD_EDIT_PROPERTIES);

        if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
            $this->g_tabs->addTab(ilUserBookingsSettingsGUI::CMD_EDIT_PROPERTIES, $this->txt("tab_settings"), $settings);
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
        $this->g_tabs->activateTab($cmd);
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
        $class_name = "ilUserBookingsGUI";
        $cmd = ilUserBookingsGUI::CMD_SHOW_BOOKINGS;
        $get = $_GET;

        if ($access->checkAccess("read", "", $ref_id)
            && $access->checkAccess("visible", "", $ref_id)
        ) {
            $object = ilObjectFactory::getInstanceByRefID($ref_id);
            $settings = $object->getSettings();

            if ($settings->getSuperiorView()) {
                $class_name = "ilSuperiorViewGUI";
                $cmd = ilSuperiorViewGUI::CMD_SHOW_BOOKINGS;
            }

            $ctrl->initBaseClass("ilObjPluginDispatchGUI");
            $ctrl->setTargetScript("ilias.php");
            $ctrl->getCallStructure(strtolower("ilObjPluginDispatchGUI"));
            $ctrl->setParameterByClass($class_name, "ref_id", $ref_id);
            $ctrl->redirectByClass(array("ilobjplugindispatchgui", "ilObjUserBookingsGUI", $class_name), $cmd);
        }

        parent::_goto($a_target);
    }
}
