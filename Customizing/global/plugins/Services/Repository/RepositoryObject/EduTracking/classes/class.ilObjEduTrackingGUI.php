<?php
require_once 'Services/Repository/classes/class.ilObjectPluginGUI.php';
require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
require_once __DIR__ . "/Settings/class.ilEduTrackingSettingsGUI.php";

/**
 * @ilCtrl_isCalledBy ilObjEduTrackingGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjEduTrackingGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjEduTrackingGUI: ilEduTrackingSettingsGUI, ilExportGUI
 */

class ilObjEduTrackingGUI extends ilObjectPluginGUI
{
    const CMD_SHOW_CONTENT = "showContent";
    const TAB_SETTINGS = "tab_settings";

    /**
     * Called after parent constructor. It's possible to define some plugin special values
     */
    protected function afterConstructor()
    {
        global $DIC;

        $this->g_tabs = $DIC->tabs();
        $this->g_access = $DIC->access();
        $this->g_ctrl = $DIC->ctrl();
    }

    /**
    * Get type.  Same value as choosen in plugin.php
    */
    final public function getType()
    {
        return "xetr";
    }

    /**
    * Handles all commmands of this class, centralizes permission checks
    */
    public function performCommand($cmd)
    {
        $next_class = $this->g_ctrl->getNextClass();
        switch ($next_class) {
            case "iledutrackingsettingsgui":
                $this->activateTab(self::TAB_SETTINGS);
                $gui = new ilEduTrackingSettingsGUI($this, $this->object, $this->plugin);
                $this->g_ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case ilEduTrackingSettingsGUI::CMD_EDIT_PROPERTIES:
                    case self::CMD_SHOW_CONTENT:
                        if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
                            $this->redirectToSettings();
                        } else {
                            $this->redirectInfoTab();
                        }
                        break;

                    default:
                        throw new Exception("Unknown command: " . $cmd);
                }
        }
    }

    /**
    * After object has been created -> jump to this command
    */
    public function getAfterCreationCmd()
    {
        return ilEduTrackingSettingsGUI::CMD_EDIT_PROPERTIES;
    }

    /**
    * After saving
    * @access	public
    */
    public function afterSave(ilObject $newObj)
    {
        $minutes = 0;
        $parent = $newObj->getParentCourse();
        if(! is_null($parent)) {
            $minutes = $this->plugin->getCourseTrainingtimeInMinutes((int) $parent->getRefId());
        }
        $gti_settings = $newObj->getActionsFor("GTI")->select();
        $gti_settings = $gti_settings->withMinutes($minutes);
        $newObj->getActionsFor("GTI")->update($gti_settings);

        parent::afterSave($newObj);
    }
    

    /**
    * Get standard command
    */
    public function getStandardCmd()
    {
        return self::CMD_SHOW_CONTENT;
    }

    /**
     * Redirect to settings gui to keep next_class options
     *
     * @return void
     */
    protected function redirectToSettings()
    {
        $link = $this->ctrl->getLinkTargetByClass(
            array("ilObjEduTrackingGUI", "ilEduTrackingSettingsGUI"),
            ilEduTrackingSettingsGUI::CMD_EDIT_PROPERTIES,
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
            array("ilObjEduTrackingGUI", "ilInfoScreenGUI"),
            "showSummary",
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    public function setTabs()
    {
        $this->addInfoTab();

        if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
            $link = $this->ctrl->getLinkTargetByClass(array("ilObjEduTrackingGUI", "ilEduTrackingSettingsGUI"), ilEduTrackingSettingsGUI::CMD_EDIT_PROPERTIES);
            $this->g_tabs->addTab(self::TAB_SETTINGS, $this->txt(self::TAB_SETTINGS), $link);
        }

        $this->addExportTab();

        $this->addPermissionTab();
    }

    protected function activateTab($cmd)
    {
        $this->g_tabs->activateTab($cmd);
    }
}
