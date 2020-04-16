<?php
include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once(__DIR__ . "/Children/class.ilChildrenSettingsGUI.php");
require_once(__DIR__ . "/Settings/class.ilCopySettingsGUI.php");

/**
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS
 *
 * @ilCtrl_isCalledBy ilObjCopySettingsGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjCopySettingsGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjCopySettingsGUI: ilChildrenSettingsGUI, ilCopySettingsGUI, ilExportGUI
 */
class ilObjCopySettingsGUI extends ilObjectPluginGUI
{
    const TAB_SETTINGS = "tab_settings";
    const TAB_CHILDREN_SETTINGS = "tab_children_settings";

    const CMD_SHOW_CONTENT = "showContent";

    /**
     * @var ilAccess
     */
    protected $g_access;

    /**
     * @var ilTabsGUI
     */
    protected $g_tabs;

    /**
     * Called after parent constructor. It's possible to define some plugin special values
     */
    protected function afterConstructor()
    {
        global $DIC;
        $this->g_access = $DIC->access();
        $this->g_tabs = $DIC->tabs();
    }

    /**
    * Get type.  Same value as choosen in plugin.php
    */
    final public function getType()
    {
        return "xcps";
    }

    /**
    * Handles all commmands of this class, centralizes permission checks
    */
    public function performCommand($cmd)
    {
        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
            case "ilchildrensettingsgui":
                $this->activateTab(self::TAB_CHILDREN_SETTINGS);
                $gui = new ilChildrenSettingsGUI($this->object->getActions(), $this->object->txtClosure());
                $this->ctrl->forwardCommand($gui);
                break;
            case "ilcopysettingsgui":
                $this->activateTab(self::TAB_SETTINGS);
                $gui = new ilCopySettingsGUI($this->object->getActions(), $this->object->txtClosure());
                $this->ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_SHOW_CONTENT:
                        if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
                            $this->redirectSettings(ilCopySettingsGUI::CMD_EDIT_PROPERTIES);
                        }
                        $this->redirectInfoTab();
                        break;
                    case ilCopySettingsGUI::CMD_EDIT_PROPERTIES:
                        $this->redirectSettings($cmd);
                        break;
                    case ilChildrenSettingsGUI::CMD_SHOW_SETTINGS:
                        $this->redirectCopySettings($cmd);
                        break;
                    default:
                }
        }
    }

    /**
    * After object has been created -> jump to this command
    */
    public function getAfterCreationCmd()
    {
        return ilCopySettingsGUI::CMD_EDIT_PROPERTIES;
    }

    /**
    * Get standard command
    */
    public function getStandardCmd()
    {
        return self::CMD_SHOW_CONTENT;
    }

    /**
     * Redirect to settings
     *
     * @param string 	$cmd
     *
     * @return void
     */
    protected function redirectSettings($cmd)
    {
        $link = $this->ctrl->getLinkTargetByClass(
            array("ilObjCopySettingsGUI", "ilCopySettingsGUI"),
            $cmd,
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    /**
     * Redirect to copy settings
     *
     * @param string 	$cmd
     *
     * @return void
     */
    protected function redirectCopySettings($cmd)
    {
        $link = $this->ctrl->getLinkTargetByClass(
            array("ilObjCopySettingsGUI", "ilChildrenSettingsGUI"),
            $cmd,
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

        if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
            $settings = $this->ctrl->getLinkTargetByClass(array("ilObjCopySettingsGUI", "ilCopySettingsGUI"), ilCopySettingsGUI::CMD_EDIT_PROPERTIES);
            $this->g_tabs->addTab(self::TAB_SETTINGS, $this->txt(self::TAB_SETTINGS), $settings);

            $copy_settings = $this->ctrl->getLinkTargetByClass(array("ilObjCopySettingsGUI", "ilChildrenSettingsGUI"), ilChildrenSettingsGUI::CMD_SHOW_SETTINGS);
            $this->g_tabs->addTab(self::TAB_CHILDREN_SETTINGS, $this->txt(self::TAB_CHILDREN_SETTINGS), $copy_settings);
        }

        $this->addExportTab();

        $this->addPermissionTab();
    }

    /**
     * Set tab active
     *
     * @param string 	$tab_name
     *
     * @return void
     */
    protected function activateTab($tab_name)
    {
        $this->g_tabs->activateTab($tab_name);
    }

    /**
     * Redirect via link to Info tab
     *
     * @return null
     */
    protected function redirectInfoTab()
    {
        $link = $this->ctrl->getLinkTargetByClass(
            array("ilObjCopySettingsGUI", "ilInfoScreenGUI"),
            "showSummary",
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    /**
     * @inheritdoc
     */
    public function afterSave(\ilObject $newObj)
    {
        $parent = $newObj->getParentContainer();
        if ($parent != null) {
            $title = $parent->getTitle();
            $parent->setTitle($this->txt("template_prefix") . ": " . $title);
            $parent->update();

            $newObj->getTemplateCoursesDB()->create((int) $newObj->getId(), (int) $parent->getId(), (int) $parent->getRefId());
        }
        /**
         * Imo the following does not belong here, but we seem to have no
         * choice due to ilias architecture.
         */
        $newObj->raiseEvent('createCopySettings');
        parent::afterSave($newObj);
    }

    public function afterImport(ilObject $a_new_object)
    {
        $parent = $a_new_object->getParentContainer();
        if ($parent != null) {
            $title = $parent->getTitle();
            $parent->setTitle($this->txt("template_prefix") . ": " . $title);
            $parent->update();

            $a_new_object->getTemplateCoursesDB()->create((int) $a_new_object->getId(), (int) $parent->getId(), (int) $parent->getRefId());
        }
        /**
         * Imo the following does not belong here, but we seem to have no
         * choice due to ilias architecture.
         */
        $a_new_object->raiseEvent('createCopySettings');
        parent::afterImport($a_new_object);
    }
}
