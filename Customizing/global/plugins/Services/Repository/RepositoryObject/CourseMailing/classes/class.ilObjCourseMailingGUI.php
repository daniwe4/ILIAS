<?php
include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once(__DIR__ . '/RoleMapping/class.ilMappingsGUI.php');
require_once(__DIR__ . '/Settings/class.ilCourseMailingSettingsGUI.php');
require_once(__DIR__ . '/AutomaticMails/class.ilAutomaticMailsGUI.php');

use CaT\Plugins\CourseMailing\DI;

/**
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS
 *
 * @ilCtrl_isCalledBy ilObjCourseMailingGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjCourseMailingGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjCourseMailingGUI: ilMappingsGUI, ilCourseMailingSettingsGUI, ilAutomaticMailsGUI, ilMailLogsGUI
 * @ilCtrl_Calls ilObjCourseMailingGUI: ilMemberMailGUI, ilExportGUI, ilInvitesGUI
 */
class ilObjCourseMailingGUI extends ilObjectPluginGUI
{
    use DI;

    const DEFAULT_CMD_CONTENT = 'showContent';
    const DEFAULT_CMD_EDIT = 'editProperties';
    const TAB_MAPPINGS = \ilMappingsGUI::CMD_EDIT;
    const TAB_AUTOMAILS = \ilAutomaticMailsGUI::CMD_SHOW;
    const CMD_PREVIEW = \ilAutomaticMailsGUI::CMD_PREVIEW;
    const CMD_MANUAL_MAIL = \ilAutomaticMailsGUI::CMD_MANUAL_MAIL;
    const ASYNC_CMD_USER_MODAL = \ilAutomaticMailsGUI::ASYNC_CMD_USER_MODAL;
    const ASYNC_CMD_OBJECT_MODAL = \ilAutomaticMailsGUI::ASYNC_CMD_OBJECT_MODAL;
    const TAB_LOGS = "tab_log";
    const TAB_MEMEBER_MAIL = "tab_member_mail";
    const TAB_INVITES = "tab_invites";

    /**
     * @var \ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var \ilAccess
     */
    protected $g_access;

    /**
     * @var \ilTabsGUI
     */
    protected $g_tabs;

    /**
     * @var ilCourseMailingPlugin
     */
    protected $plugin;

    /**
     * @var ilObjCourseMailing
     */
    public $object;

    /**
    * Get type.  Same value as choosen in plugin.php
    * @return string
    */
    final public function getType()
    {
        return "xcml";
    }

    /**
     * @inheritDoc
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
    * @inheritDoc
    */
    public function performCommand($cmd)
    {
        if ($this->belowCourse() && !$this->checkAttachmentConfigurationSanity()) {
            $this->showAttachmentsMisconfiguredInfo();
        }

        $next_class = $this->g_ctrl->getNextClass();
        switch ($next_class) {
            case 'ilmappingsgui':
                if (!$this->g_access->checkAccess("write", "", $this->object->getRefId())) {
                    $this->redirectInfoTab();
                }
                $this->forwardMappingsGUI();
                break;
            case 'ilcoursemailingsettingsgui':
                if (!$this->g_access->checkAccess("write", "", $this->object->getRefId())) {
                    $this->redirectInfoTab();
                }
                $this->forwardSettingsGUI();
                break;
            case 'ilmaillogsgui':
                if (!$this->g_access->checkAccess("read_log", "", $this->object->getRefId())) {
                    $this->redirectInfoTab();
                }
                $this->forwardLogsGUI();
                break;
            case 'ilautomaticmailsgui':
                if (!$this->g_access->checkAccess("view_auto_mails", "", $this->object->getRefId())) {
                    $this->redirectInfoTab();
                }
                $this->forwardAutomailsGUI();
                break;
            case 'ilmembermailgui':
                if (!$this->g_access->checkAccess("mail_to_members", "", $this->object->getRefId())) {
                    $this->redirectInfoTab();
                }
                $this->forwardMemberMailGUI();
                break;
            case 'ilinvitesgui':
                if (!$this->g_access->checkAccess("view_invites", "", $this->object->getRefId())) {
                    $this->redirectInfoTab();
                }
                $this->forwardInvitesGUI();
                break;
            default:
                switch ($cmd) {
                    case self::DEFAULT_CMD_CONTENT:
                    case self::DEFAULT_CMD_EDIT:
                        if (!$this->g_access->checkAccess("write", "", $this->object->getRefId())) {
                            $this->redirectInfoTab();
                        }
                        $this->redirectSettings();
                        break;
                    default:
                        throw new Exception("Unknown command: $cmd -- $next_class");
                }
        }
    }

    protected function setTabs()
    {
        $this->addInfoTab();

        if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
            $this->g_tabs->addTab(
                self::DEFAULT_CMD_EDIT,
                $this->plugin->txt(self::DEFAULT_CMD_EDIT),
                $this->g_ctrl->getLinkTargetByClass("ilCourseMailingSettingsGUI", ilCourseMailingSettingsGUI::CMD_EDIT_SETTINGS)
            );

            $this->g_tabs->addTab(
                self::TAB_MAPPINGS,
                $this->plugin->txt(self::TAB_MAPPINGS),
                $this->g_ctrl->getLinkTargetByClass("ilMappingsGUI", ilMappingsGUI::CMD_EDIT)
            );
        }

        if ($this->g_access->checkAccess("view_auto_mails", "", $this->object->getRefId())) {
            $this->g_tabs->addTab(
                self::TAB_AUTOMAILS,
                $this->plugin->txt(self::TAB_AUTOMAILS),
                $this->g_ctrl->getLinkTargetByClass("ilAutomaticMailsGUI", ilAutomaticMailsGUI::CMD_SHOW)
            );
        }

        if ($this->g_access->checkAccess("read_log", "", $this->object->getRefId())) {
            $this->g_tabs->addTab(
                self::TAB_LOGS,
                $this->plugin->txt(self::TAB_LOGS),
                $this->getDIC()["log.gui.link"]
            );
        }

        if ($this->g_access->checkAccess("mail_to_members", "", $this->object->getRefId())) {
            $this->g_tabs->addTab(
                self::TAB_MEMEBER_MAIL,
                $this->plugin->txt(self::TAB_MEMEBER_MAIL),
                $this->getDIC()["membermail.gui.link"]
            );
        }

        if ($this->g_access->checkAccess("view_invites", "", $this->object->getRefId())) {
            $this->g_tabs->addTab(
                self::TAB_INVITES,
                $this->plugin->txt(self::TAB_INVITES),
                $this->getDIC()["invites.gui.link"]
            );
        }

        $this->addExportTab();

        $this->addPermissionTab();
    }

    private function forwardMappingsGUI()
    {
        $this->g_tabs->activateTab(self::TAB_MAPPINGS);
        if (!$this->belowCourse()) {
            $this->showNoParentInformation();
            return;
        }
        $gui = new ilMappingsGUI(
            $this,
            $this->object->getActions(),
            $this->plugin->txtClosure()
        );
        $this->g_ctrl->forwardCommand($gui);
    }

    private function forwardSettingsGUI()
    {
        $this->g_tabs->activateTab(self::DEFAULT_CMD_EDIT);
        $gui = new ilCourseMailingSettingsGUI(
            $this,
            $this->object->getActions(),
            $this->plugin->txtClosure()
        );
        $this->g_ctrl->forwardCommand($gui);
    }

    private function forwardAutomailsGUI()
    {
        $this->g_tabs->activateTab(self::TAB_AUTOMAILS);
        if (!$this->belowCourse()) {
            $this->showNoParentInformation();
            return;
        }
        $gui = new ilAutomaticMailsGUI(
            $this,
            $this->object->getActions(),
            $this->plugin->txtClosure()
        );
        $this->g_ctrl->forwardCommand($gui);
    }

    private function forwardMemberMailGUI()
    {
        $this->g_tabs->activateTab(self::TAB_MEMEBER_MAIL);
        if (!$this->belowCourse()) {
            $this->showNoParentInformation();
            return;
        }
        $gui = $this->getDIC()["membermail.gui"];
        $this->g_ctrl->forwardCommand($gui);
    }

    private function forwardInvitesGUI()
    {
        $this->g_tabs->activateTab(self::TAB_INVITES);
        if (!$this->belowCourse()) {
            $this->showNoParentInformation();
            return;
        }
        $gui = $this->getDIC()["invites.gui"];
        $this->g_ctrl->forwardCommand($gui);
    }

    private function forwardLogsGUI()
    {
        $this->g_tabs->activateTab(self::TAB_LOGS);
        if (!$this->belowCourse()) {
            $this->showNoParentInformation();
            return;
        }
        $gui = $this->getDIC()["log.gui"];
        $this->g_ctrl->forwardCommand($gui);
    }

    /**
     * @inheritdoc
     */
    public function afterSave(\ilObject $newObj)
    {
        parent::afterSave($newObj);
    }

    /**
    * After object has been created -> jump to this command
    */
    public function getAfterCreationCmd()
    {
        return self::DEFAULT_CMD_EDIT;
    }

    /**
    * Get standard command
    */
    public function getStandardCmd()
    {
        return self::DEFAULT_CMD_EDIT;
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
            && $access->checkAccess("visible", "", $ref_id)
            && isset($get["cmd"])
            && ($get["cmd"] == "show_logs")
        ) {
            $ctrl->initBaseClass("ilObjPluginDispatchGUI");
            $ctrl->setTargetScript("ilias.php");
            $ctrl->getCallStructure(strtolower("ilObjPluginDispatchGUI"));
            $ctrl->setParameterByClass($class_name, "ref_id", $ref_id);
            $ctrl->redirectByClass(array("ilobjplugindispatchgui", $class_name, "ilMailLogsGUI"), $get["cmd"]);
        }

        if ($access->checkAccess("read", "", $ref_id)
            && $access->checkAccess("visible", "", $ref_id)
            && isset($get["cmd"])
            && ($get["cmd"] == "show_mail")
        ) {
            $ctrl->initBaseClass("ilObjPluginDispatchGUI");
            $ctrl->setTargetScript("ilias.php");
            $ctrl->getCallStructure(strtolower("ilObjPluginDispatchGUI"));
            $ctrl->setParameterByClass($class_name, "ref_id", $ref_id);
            $ctrl->redirectByClass(array("ilobjplugindispatchgui", $class_name, "ilMemberMailGUI"), "showMembers");
        }

        if ($access->checkAccess("read", "", $ref_id)
            && $access->checkAccess("view_invites", "", $ref_id)
            && isset($get["cmd"])
            && ($get["cmd"] == "viewInvited")
        ) {
            $ctrl->initBaseClass("ilObjPluginDispatchGUI");
            $ctrl->setTargetScript("ilias.php");
            $ctrl->getCallStructure(strtolower("ilObjPluginDispatchGUI"));
            $ctrl->setParameterByClass($class_name, "ref_id", $ref_id);
            $ctrl->redirectByClass(array("ilobjplugindispatchgui", $class_name, "ilInvitesGUI"), "viewInvited");
        }

        parent::_goto($a_target);
    }

    protected function redirectSettings()
    {
        $link = $this->ctrl->getLinkTargetByClass(
            "ilCourseMailingSettingsGUI",
            ilCourseMailingSettingsGUI::CMD_EDIT_SETTINGS,
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
            "ilInfoScreenGUI",
            "showSummary",
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    /**
     * Show information that the attachments are mis-configured due to copy-action.
     *
     * @return void
     */
    protected function showAttachmentsMisconfiguredInfo()
    {
        ilUtil::sendInfo($this->plugin->txt("attachments_misconfigured"));
    }
    /**
     * Show information that the attachments are mis-configured due to copy-action.
     *
     * @return bool
     */
    protected function checkAttachmentConfigurationSanity()
    {
        $actions = $this->object->getActions();

        $valid_options = array_keys($actions->getAttachmentOptions());
        $mappings = $actions->getRoleMappings();

        foreach ($mappings as $mapping) {
            $current_atachments = $mapping->getAttachmentIds();
            foreach ($current_atachments as $attachment) {
                if (!in_array($attachment, $valid_options)) {
                    return false;
                }
            }
        }
        return true;
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

    protected function getDIC()
    {
        global $DIC;
        return $this->getObjectDIC($this->object, $DIC);
    }
}
