<?php
include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once(__DIR__ . "/class.ilMaterialListSettingsGUI.php");
require_once(__DIR__ . "/Lists/class.ilListsGUI.php");
require_once(__DIR__ . "/../vendor/autoload.php");

use \CaT\Plugins\MaterialList;

/**
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS
 *
 * @ilCtrl_isCalledBy ilObjMaterialListGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls      ilObjMaterialListGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls      ilObjMaterialListGUI: ilMaterialListSettingsGUI, ilListsGUI, ilExportGUI
 */
class ilObjMaterialListGUI extends ilObjectPluginGUI
{
    const TAB_SETTINGS = "tab_settings";
    const TAB_MATERIAL = "tab_material";

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
     * @var \ilMaterialListPlugin
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
        return "xmat";
    }

    /**
    * Handles all commmands of this class, centralizes permission checks
    */
    public function performCommand($cmd)
    {
        $next_class = $this->g_ctrl->getNextClass();
        switch ($next_class) {
            case "ilmateriallistsettingsgui":
                $this->setActiveTab(self::TAB_SETTINGS);
                $object_actions = $this->object->getActions();
                $gui = new \ilMaterialListSettingsGUI($this, $object_actions, $this->plugin->txtClosure());
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "illistsgui":
                $this->setActiveTab(self::TAB_MATERIAL);
                $object_actions = $this->object->getActions();
                $plugin_actions = $this->plugin->getActions();
                $list_entry_validate = new MaterialList\Materials\ListEntryValidate($plugin_actions);
                $gui = new \ilListsGUI($this, $object_actions, $plugin_actions, $this->plugin, $this->plugin->txtClosure(), $list_entry_validate);
                $this->g_ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case ilMaterialListSettingsGUI::CMD_EDIT_PROPERTIES:
                        $this->forwardSettings();
                        break;
                    case ilListsGUI::CMD_SHOW_CONTENT:
                        $this->forwardMaterial();
                        break;
                    default:
                        throw new Exception(__METHOD__ . ":: Unknown command: " . $cmd);
                }
        }
    }

    /**
     * Forward to material gui
     *
     * @return null
     */
    protected function forwardSettings()
    {
        $link = $this->g_ctrl->getLinkTargetByClass(
            array("ilObjMaterialListGUI", "ilMaterialListSettingsGUI"),
            ilMaterialListSettingsGUI::CMD_EDIT_PROPERTIES,
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    /**
     * Forward to material gui
     *
     * @return null
     */
    protected function forwardMaterial()
    {
        $link = $this->g_ctrl->getLinkTargetByClass(
            array("ilObjMaterialListGUI", "ilListsGUI"),
            ilListsGUI::CMD_SHOW_CONTENT,
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
        return ilListsGUI::CMD_SHOW_CONTENT;
    }

    /**
    * Get standard command
    */
    public function getStandardCmd()
    {
        return ilListsGUI::CMD_SHOW_CONTENT;
    }

    /**
    * Set tabs
    */
    protected function setTabs()
    {
        $this->addInfoTab();
        if ($this->g_access->checkAccess("read", "", $this->object->getRefId())) {
            $this->g_tabs->addTab(
                self::TAB_MATERIAL,
                $this->plugin->txt(self::TAB_MATERIAL),
                $this->g_ctrl->getLinkTargetByClass(
                    array("ilObjMaterialListGUI", "ilListsGUI"),
                    ilListsGUI::CMD_SHOW_CONTENT,
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
                    array("ilObjMaterialListGUI", "ilMaterialListSettingsGUI"),
                    ilMaterialListSettingsGUI::CMD_EDIT_PROPERTIES,
                    "",
                    false,
                    false
                )
            );
        }

        $this->addExportTab();

        $this->addPermissionTab();
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
