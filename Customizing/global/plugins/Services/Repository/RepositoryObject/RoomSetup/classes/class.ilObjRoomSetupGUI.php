<?php
include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once(__DIR__ . "/class.ilRoomSetupSettingsGUI.php");
require_once(__DIR__ . "/Equipment/class.ilEquipmentGUI.php");


/**
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS
 *
 * @ilCtrl_isCalledBy ilObjRoomSetupGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls      ilObjRoomSetupGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls      ilObjRoomSetupGUI: ilRoomSetupSettingsGUI, ilEquipmentGUI, ilExportGUI
 */
class ilObjRoomSetupGUI extends ilObjectPluginGUI
{
    const TAB_SETTINGS = "tab_settings";
    const TAB_EQUIPMENT = "tab_equipment";

    /**
     * @var \ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var \ilTabsGUI
     */
    protected $g_tabs;

    /**
     * @var \ilAccess
     */
    protected $g_access;

    /**
     * @var \ilRoomSetupPlugin
     */
    protected $plugin;

    /**
     * Called after parent constructor. It's possible to define some plugin special values
     */
    protected function afterConstructor()
    {
        global $DIC;

        $this->g_ctrl = $DIC->ctrl();
        $this->g_tabs = $DIC->tabs();
        $this->g_access = $DIC->access();
        $this->plugin = $this->getPlugin();
    }

    /**
    * Get type.  Same value as choosen in plugin.php
    */
    final public function getType()
    {
        return "xrse";
    }

    /**
    * Handles all commmands of this class, centralizes permission checks
    */
    public function performCommand($cmd)
    {
        $next_class = $this->g_ctrl->getNextClass();
        switch ($next_class) {
            case "ilroomsetupsettingsgui":
                if (!$this->g_access->checkAccess("write", "", $this->object->getRefId())) {
                    $this->toParentCourse();
                }

                $this->setActiveTab(self::TAB_SETTINGS);
                $gui = new \ilRoomSetupSettingsGUI($this, $this->object->getActions(), $this->plugin->txtClosure());
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "ilequipmentgui":
                $edit_equipment = $this->g_access->checkAccess("edit_equipment", "", $this->object->getRefId());
                $read = $this->g_access->checkAccess("read", "", $this->object->getRefId());

                if (!$edit_equipment && !$read) {
                    $this->toParentCourse();
                }

                $this->setActiveTab(self::TAB_EQUIPMENT);
                $gui = new \ilEquipmentGUI($this, $this->object->getActions(), $this->plugin->getActions(), $this->plugin->txtClosure(), $edit_equipment);
                $this->g_ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case ilRoomSetupSettingsGUI::CMD_EDIT_PROPERTIES:
                        $this->redirectSettings();
                        break;
                    case ilEquipmentGUI::CMD_SHOW_CONTENT:
                        $this->redirectEquipment();
                        break;
                    default:
                }
        }
    }

    /**
     * Redirect to parent course if permission check failed
     *
     * @return null
     */
    protected function toParentCourse()
    {
        ilUtil::sendInfo($this->txt("not_needed_permissions"), true);
        $crs_ref_id = $this->object->getParentCourse()->getRefId();

        $this->g_ctrl->setParameterByClass('ilobjcoursegui', "ref_id", $crs_ref_id);
        $link = $this->g_ctrl->getLinkTargetByClass(array('ilrepositorygui', 'ilobjcoursegui'), 'view', "", false, false);
        $this->g_ctrl->setParameterByClass('ilobjcoursegui', "ref_id", null);

        \ilUtil::redirect($link);
    }

    /**
     * Redirect to settings gui
     *
     * @return null
     */
    protected function redirectSettings()
    {
        $link = $this->g_ctrl->getLinkTargetByClass(
            array("ilObjRoomSetupGUI", "ilRoomSetupSettingsGUI"),
            ilRoomSetupSettingsGUI::CMD_EDIT_PROPERTIES,
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    /**
     * Redirect to equipment gui
     *
     * @return null
     */
    protected function redirectEquipment()
    {
        $link = $this->g_ctrl->getLinkTargetByClass(
            array("ilObjRoomSetupGUI", "ilEquipmentGUI"),
            ilEquipmentGUI::CMD_SHOW_CONTENT,
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    /**
    * After object has been created -> jump to this command
    */
    public function getAfterCreationCmd()
    {
        return ilRoomSetupSettingsGUI::CMD_EDIT_PROPERTIES;
    }

    /**
    * Get standard command
    */
    public function getStandardCmd()
    {
        return ilEquipmentGUI::CMD_SHOW_CONTENT;
    }

    /**
    * Set tabs
    */
    protected function setTabs()
    {
        if ($this->g_access->checkAccess("write", "", $this->object->getRefId())
                || $this->g_access->checkAccess("read", "", $this->object->getRefId())
        ) {
            $this->g_tabs->addTab(
                self::TAB_EQUIPMENT,
                $this->plugin->txt(self::TAB_EQUIPMENT),
                $this->g_ctrl->getLinkTargetByClass(
                    array("ilObjRoomSetupGUI", "ilEquipmentGUI"),
                    ilEquipmentGUI::CMD_SHOW_CONTENT,
                    "",
                    false,
                    false
                )
            );
        }

        if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
            $this->g_tabs->addTab(
                self::TAB_SETTINGS,
                $this->plugin->txt(self::TAB_SETTINGS),
                $this->g_ctrl->getLinkTargetByClass(
                    array("ilObjRoomSetupGUI", "ilRoomSetupSettingsGUI"),
                    ilRoomSetupSettingsGUI::CMD_EDIT_PROPERTIES,
                    "",
                    false,
                    false
                )
            );
        }

        $this->addExportTab();

        $this->addPermissionTab();

        return true;
    }

    /**
     * Activates active tab
     *
     * @param string 	$active_tab
     */
    protected function setActiveTab($active_tab)
    {
        $this->g_tabs->activateTab($active_tab);
    }
}
