<?php
use CaT\Plugins\CourseMember\DI;

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once(__DIR__ . "/Settings/class.ilCourseMemberSettingsGUI.php");
require_once(__DIR__ . "/Members/class.ilMembersGUI.php");
require_once(__DIR__ . "/LPSettings/class.ilCourseMemberLPSettingsGUI.php");

/**
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS
 *
 * @ilCtrl_isCalledBy ilObjCourseMemberGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjCourseMemberGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjCourseMemberGUI: ilCourseMemberSettingsGUI, ilMembersGUI
 * @ilCtrl_Calls ilObjCourseMemberGUI: ilCourseMemberLPSettingsGUI, ilExportGUI
 */
class ilObjCourseMemberGUI extends ilObjectPluginGUI
{
    use DI;

    const CMD_SHOW_CONTENT = "showContent";
    const TAB_MEMBERS = "tab_members";
    const TAB_SETTINGS = "tab_settings";
    const TAB_LP = "tab_lp";

    /**
     * @var ilObjCourseMember
     */
    public $object;

    /**
     * @var DI
     */
    protected $dic;

    /**
     * Called after parent constructor. It's possible to define some plugin special values
     */
    protected function afterConstructor()
    {
        global $DIC;
        if (!is_null($this->object)) {
            $this->dic = $this->getObjectDIC($this->object, $DIC);
        }
    }

    /**
    * Get type.  Same value as choosen in plugin.php
    */
    final public function getType()
    {
        return "xcmb";
    }

    /**
    * Handles all commmands of this class, centralizes permission checks
    */
    public function performCommand($cmd)
    {
        $next_class = $this->ctrl->getNextClass();
        switch ($next_class) {
            case "ilcoursemembersettingsgui":
                $this->activateTab(self::TAB_SETTINGS);
                $gui = new ilCourseMemberSettingsGUI($this, $this->object->getActions());
                $this->ctrl->forwardCommand($gui);
                break;
            case "ilmembersgui":
                $this->activateTab(self::TAB_MEMBERS);
                if (!$this->belowCourse()) {
                    $this->showNoParentInformation();
                    return;
                }

                $gui = $this->dic["members.gui"];
                $this->ctrl->forwardCommand($gui);
                break;
            case "ilcoursememberlpsettingsgui":
                $this->activateTab(self::TAB_LP);
                if (!$this->belowCourse()) {
                    $this->showNoParentInformation();
                    return;
                }

                $gui = new ilCourseMemberLPSettingsGUI($this, $this->object->getActions(), $this->plugin->getLPOptionActions());
                $this->ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_SHOW_CONTENT:
                        if ($this->dic["ilAccess"]->checkAccess("write", "", $this->object->getRefId())) {
                            $this->redirectToSettings();
                        } else {
                            $this->redirectInfoTab();
                        }
                        break;
                    case ilCourseMemberSettingsGUI::CMD_EDIT_PROPERTIES:
                        $this->redirectToSettings();
                        break;
                    default:
                }
        }
    }

    /**
     * Redirect to settings gui to keep next_class options
     *
     * @return void
     */
    protected function redirectToSettings()
    {
        $link = $this->ctrl->getLinkTargetByClass(
            array("ilObjCourseMemberGUI", "ilCourseMemberSettingsGUI"),
            ilCourseMemberSettingsGUI::CMD_EDIT_PROPERTIES,
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }
    /**
     * Redirect to members gui to keep next_class options
     *
     * @return void
     */
    protected function redirectToMembers()
    {
        $link = $this->ctrl->getLinkTargetByClass(
            array("ilObjCourseMemberGUI", "ilMembersGUI"),
            ilMembersGUI::CMD_SHOW_MEMBERS,
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
            array("ilObjCourseMemberGUI", "ilInfoScreenGUI"),
            "showSummary",
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
        return ilCourseMemberSettingsGUI::CMD_EDIT_PROPERTIES;
    }

    /**
    * Get standard command
    */
    public function getStandardCmd()
    {
        return self::CMD_SHOW_CONTENT;
    }

    /**
     * @inhertidoc
     */
    public function initCreateForm($a_new_type)
    {
        $form = parent::initCreateForm($a_new_type);
        if (\ilPluginAdmin::isPluginActive('xetr')) {
            $ni = new ilNumberInputGUI($this->plugin->txt("credits"), ilCourseMemberSettingsGUI::F_CREDITS);
            $form->addItem($ni);
        }

        return $form;
    }

    /**
     * @inheritdoc
     */
    public function afterSave(\ilObject $newObj)
    {
        if (\ilPluginAdmin::isPluginActive('xetr')) {
            $post = $_POST;

            $credits = $this->replaceComma($post[ilCourseMemberSettingsGUI::F_CREDITS]);
            $fnc = function ($s) use ($credits) {
                return $s->withCredits($credits);
            };

            $newObj->updateSettings($fnc);
            $newObj->update();
        }

        parent::afterSave($newObj);
    }

    /**
     * Set the tabs for the site and activate current
     *
     * @param string 	$cmd
     *
     * @return void
     */
    protected function setTabs()
    {
        $this->addInfoTab();

        if ($this->dic["ilAccess"]->checkAccess("view_lp", "", $this->object->getRefId())) {
            $this->dic["ilTabs"]->addTab(
                self::TAB_MEMBERS,
                $this->txt("tab_members"),
                $this->dic["members.gui.link"]
            );
        }

        if ($this->dic["ilAccess"]->checkAccess("write", "", $this->object->getRefId())) {
            $settings = $this->ctrl->getLinkTargetByClass(
                array("ilObjCourseMemberGUI", "ilCourseMemberSettingsGUI"),
                ilCourseMemberSettingsGUI::CMD_EDIT_PROPERTIES
            );
            $this->dic["ilTabs"]->addTab(
                self::TAB_SETTINGS,
                $this->txt("tab_settings"),
                $settings
            );
        }

        if ($this->dic["ilAccess"]->checkAccess("edit_lp_mode", "", $this->object->getRefId())) {
            $lp_settings = $this->ctrl->getLinkTargetByClass(
                array("ilObjCourseMemberGUI", "ilCourseMemberLPSettingsGUI"),
                ilCourseMemberLPSettingsGUI::CMD_LP
            );
            $this->dic["ilTabs"]->addTab(
                self::TAB_LP,
                $this->txt("tab_lp"),
                $lp_settings
            );
        }

        $this->addExportTab();

        $this->addPermissionTab();
    }

    protected function activateTab($tab)
    {
        $this->dic["ilTabs"]->activateTab($tab);
    }

    /**
     * Check object is below a course object
     *
     * @return bool
     */
    protected function belowCourse()
    {
        return $this->object->getParentCourse() !== null;
    }

    /**
     * Show information accomodation object is not below course
     *
     * @return void
     */
    protected function showNoParentInformation()
    {
        ilUtil::sendInfo($this->plugin->txt("not_below_course"));
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
        $get = $_GET;

        if ($access->checkAccess("read", "", $ref_id)
            && $access->checkAccess("view_lp", "", $ref_id)
            && isset($get["cmd"])
            && ($get["cmd"] == "showMembers" || $get["cmd"] == "exportSignatureList" || $get["cmd"] == "downloadFile")
        ) {
            $ctrl->initBaseClass("ilObjPluginDispatchGUI");
            $ctrl->setTargetScript("ilias.php");
            $ctrl->getCallStructure(strtolower("ilObjPluginDispatchGUI"));
            $ctrl->setParameterByClass($class_name, "ref_id", $ref_id);
            $ctrl->redirectByClass(array("ilobjplugindispatchgui", $class_name, "ilmembersgui"), $get["cmd"]);
        }

        parent::_goto($a_target);
    }

    /**
     * Replace last comma of value with an dot
     *
     * @param string 	$value
     *
     * @return string
     */
    protected function replaceComma($value)
    {
        if ($value == "") {
            return null;
        }

        $pos = strrpos($value, ",");

        if ($pos !== false) {
            $value = substr_replace($value, ".", $pos, strlen(","));
        }

        return floatval($value);
    }
}
