<?php
require_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once(__DIR__ . "/AgendaItem/class.ilAgendaItemsGUI.php");
require_once(__DIR__ . "/Settings/class.ilAIPSettingsGUI.php");

/**
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS
 *
 * @ilCtrl_isCalledBy ilObjAgendaItemPoolGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjAgendaItemPoolGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjAgendaItemPoolGUI: ilAgendaItemsGUI, ilAIPSettingsGUI
 */
class ilObjAgendaItemPoolGUI extends ilObjectPluginGUI
{
    const CMD_SHOW_CONTENT = "showContent";
    const CMD_EDIT_PROPERTIES = "editProperties";
    const TAB_AGENDA_ITEMS = "agendaItems";
    const TAB_SETTINGS = "settings";

    /**
     * Called after parent constructor. It's possible to define some plugin special values
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

        $this->g_lng->loadLanguageModule("xaip");
    }

    /**
     * Get type.  Same value as choosen in plugin.php
     */
    final public function getType()
    {
        return "xaip";
    }

    /**
     * Handles all commmands of this class, centralizes permission checks
     */
    public function performCommand($cmd)
    {
        $cmd = $this->g_ctrl->getCmd();
        if ($cmd == null) {
            $cmd = self::CMD_SHOW_CONTENT;
        }

        if (!$this->object->getSettings()->getIsOnline()) {
            $this->g_tpl->setAlertProperties(array(
            [
                "alert" => true,
                "property" => $this->g_lng->txt("status"),
                "value" => $this->g_lng->txt("offline")
            ]));
        }
        $next_class = $this->g_ctrl->getNextClass();
        switch ($next_class) {
            case 'ilaipsettingsgui':
                $this->forwardAIPSettings();
                break;
            case 'ilagendaitemsgui':
                $this->forwardAgendaItems();
                break;
            default:
                switch ($cmd) {
                    case self::CMD_SHOW_CONTENT:
                        if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
                            $this->redirectAIPSettings($cmd);
                        }
                        if ($this->g_access->checkAccess("edit_agenda_item", "", $this->object->getRefId())) {
                            $this->redirectAgendaItems($cmd);
                        }
                        $this->redirectInfoTab();
                        break;
                    case self::CMD_EDIT_PROPERTIES:
                        $this->forwardAIPSettings();
                        break;
                    default:
                        throw new Exception("ilObjAgendaItemPoolGUI: Unknown command: " . $cmd);
                }
        }
    }

    /**
     * After object has been created -> jump to this command
     *
     * @return void
     */
    public function getAfterCreationCmd()
    {
        return self::CMD_SHOW_CONTENT;
    }

    /**
     * Get standard command
     *
     * @return void
     */
    public function getStandardCmd()
    {
        return self::CMD_SHOW_CONTENT;
    }

    /**
     * Forward to ilAgendaItemsGUI.
     *
     * @return void
     */
    protected function forwardAgendaItems()
    {
        $this->g_tabs->activateTab(self::TAB_AGENDA_ITEMS);
        $gui = new \ilAgendaItemsGUI(
            $this,
            $this->object->getObjectActions(),
            $this->plugin->txtClosure()
        );
        $this->g_ctrl->forwardCommand($gui);
    }

    /**
     * Redirect via link to agenda items gui.
     *
     * @return void
     */
    protected function redirectAgendaItems()
    {
        $link = $this->ctrl->getLinkTargetByClass(
            array("ilObjAgendaItemPoolGUI", "ilAgendaItemsGUI"),
            ilAgendaItemsGUI::CMD_SHOW_AGENDA_ITEMS,
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    /**
     * Forward to ilAgendaItemsGUI.
     *
     * @return void
     */
    protected function forwardAIPSettings()
    {
        $this->g_tabs->activateTab(self::TAB_SETTINGS);
        $gui = new \ilAIPSettingsGUI(
            $this,
            $this->object->getObjectActions(),
            $this->plugin->txtClosure()
        );
        $this->g_ctrl->forwardCommand($gui);
    }

    /**
     * Redirect via link to ilAIPSettings.
     *
     * @return void
     */
    protected function redirectAIPSettings()
    {
        $link = $this->g_ctrl->getLinkTargetByClass(
            array("ilObjAgendaItemPoolGUI", "ilAIPSettingsGUI"),
            ilAIPSettingsGUI::CMD_SETTINGS,
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    /**
     * Redirect via link to InfoTab.
     *
     * @return void
     */
    protected function redirectInfoTab()
    {
        $link = $this->g_ctrl->getLinkTargetByClass(
            array("ilObjAgendaItemPoolGUI", "ilInfoScreenGUI"),
            "showSummary",
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    /**
     * Set the tabs for the site.
     *
     * @return 	void
     */
    protected function setTabs()
    {
        $this->addInfoTab();
        $agenda_items = $this->g_ctrl->getLinkTargetByClass(
            array("ilObjAgendaItemPoolGUI", "ilAgendaItemsGUI"),
            ilAgendaItemsGUI::CMD_SHOW_AGENDA_ITEMS
        );
        $settings = $this->g_ctrl->getLinkTargetByClass(
            array("ilObjAgendaItemPoolGUI", "ilAIPSettingsGUI"),
            ilAIPSettingsGUI::CMD_SETTINGS
        );

        if ($this->g_access->checkAccess("edit_agenda_item", "", $this->object->getRefId())) {
            $this->g_tabs->addTab(self::TAB_AGENDA_ITEMS, $this->txt("agenda_items"), $agenda_items);
        }
        if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
            $this->g_tabs->addTab(self::TAB_SETTINGS, $this->txt("settings"), $settings);
        }
        $this->addPermissionTab();
    }
}
