<?php

declare(strict_types=1);

require_once "./Services/Repository/classes/class.ilObjectPluginGUI.php";
require_once __DIR__ . "/Settings/class.ilBookingApprovalsSettingsGUI.php";
require_once __DIR__ . "/Approvals/class.ilApprovalsOverviewGUI.php";
require_once __DIR__ . "/Approvals/class.ilFinishedApprovalsGUI.php";
require_once __DIR__ . "/Approvals/class.ilMyApprovalsOverviewGUI.php";
require_once __DIR__ . "/Approvals/class.ilBlockedApprovalsGUI.php";
require_once __DIR__ . "/Booking/class.ilSelfBookingWaitingWithApproveGUI.php";
require_once __DIR__ . "/Booking/class.ilSelfBookingWithApproveGUI.php";
require_once __DIR__ . "/Booking/class.ilSuperiorBookingWaitingWithApproveGUI.php";
require_once __DIR__ . "/Booking/class.ilSuperiorBookingWithApproveGUI.php";

use CaT\Plugins\BookingApprovals\Approvals\ApprovalGUI;

/**
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS.
 *
 * @ilCtrl_isCalledBy ilObjBookingApprovalsGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjBookingApprovalsGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
 * @ilCtrl_Calls ilObjBookingApprovalsGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjBookingApprovalsGUI: ilBookingApprovalsSettingsGUI, ilApprovalsOverviewGUI
 * @ilCtrl_Calls ilObjBookingApprovalsGUI: ilFinishedApprovalsGUI
 * @ilCtrl_Calls ilObjBookingApprovalsGUI: ilMyApprovalsOverviewGUI
 * @ilCtrl_Calls ilObjBookingApprovalsGUI: ilSelfBookingWaitingWithApproveGUI, ilSelfBookingWithApproveGUI
 * @ilCtrl_Calls ilObjBookingApprovalsGUI: ilSuperiorBookingWaitingWithApproveGUI
 * @ilCtrl_Calls ilObjBookingApprovalsGUI: ilSuperiorBookingWithApproveGUI
 * @ilCtrl_Calls ilObjBookingApprovalsGUI: ilBlockedApprovalsGUI
 *
 * @author
 * @copyright Extended GPL, see LICENSE
 */
class ilObjBookingApprovalsGUI extends ilObjectPluginGUI
{
    const CMD_EDIT_PROPERTIES = "editProperties";

    const TAB_SETTINGS = "settings";
    const TAB_APPROVALS_OVERVIEW = "approvals_overview";
    const TAB_FINISHED_APPROVALS = "finished_approvals";
    const TAB_MY_APPROVALS_OVERVIEW = "my_approvals_overview";
    const TAB_BLOCKED_APPROVALS = "blocked_approvals";

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $g_tpl;

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ilTabs
     */
    protected $g_tabs;

    /**
     * @var ilToolbarGUI
     */
    protected $g_toolbar;

    /**
     * @var ilObjUser
     */
    protected $g_usr;

    /**
     * @var ilAccessHandler
     */
    protected $g_access;

    /**
     * @var ilLanguage
     */
    protected $g_lng;

    /**
     * Called after parent constructor. It's possible to define some plugin special values.
     *
     * @return 	void
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

        $this->g_lng->loadLanguageModule("");
    }

    final public function getType() : string
    {
        return "xbka";
    }

    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $classes = [
            "ilselfbookingwaitingwithapprovegui",
            "ilselfbookingwithapprovegui",
            "ilsuperiorbookingwaitingwithapprovegui",
            "ilsuperiorbookingwithapprovegui"
        ];

        if (in_array($next_class, $classes)) {
            $this->omitLocator(true);
        }
        parent::executeCommand();
    }

    public function performCommand(string $cmd)
    {
        $next_class = $this->g_ctrl->getNextClass();

        $this->setTitleByline();
        switch ($next_class) {
            case 'ilbookingapprovalssettingsgui':
                $this->forwardSettings();
                break;
            case 'ilapprovalsoverviewgui':
                $this->forwardApprovalOverviewGUI();
                break;
            case "ilfinishedapprovalsgui":
                    $this->forwardFinishedApprovals();
                break;
            case "ilmyapprovalsoverviewgui":
                $this->forwardMyApprovalsOverview();
                break;
            case "ilblockedapprovalsgui":
                $this->forwardBlockedApprovals();
                break;
            case "ilselfbookingwaitingwithapprovegui":
                $this->clearTabsAndTitle();
                $parent = new ilTrainingSearchGUI();
                $gui = new ilSelfBookingWaitingWithApproveGUI($parent, ilTrainingSearchGUI::CMD_SHOW);
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "ilselfbookingwithapprovegui":
                $this->clearTabsAndTitle();
                $parent = new ilTrainingSearchGUI();
                $gui = new ilSelfBookingWithApproveGUI($parent, ilTrainingSearchGUI::CMD_SHOW);
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "ilsuperiorbookingwaitingwithapprovegui":
                $this->clearTabsAndTitle();
                $parent = new ilTrainingSearchGUI();
                $gui = new ilSuperiorBookingWaitingWithApproveGUI($parent, ilTrainingSearchGUI::CMD_SHOW);
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "ilsuperiorbookingwithapprovegui":
                $this->clearTabsAndTitle();
                $parent = new ilTrainingSearchGUI();
                $gui = new ilSuperiorBookingWithApproveGUI($parent, ilTrainingSearchGUI::CMD_SHOW);
                $this->g_ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_EDIT_PROPERTIES:
                        $this->redirectSettings();
                        break;
                    case ApprovalGUI::CMD_SHOW_OVERVIEW:
                    case ApprovalGUI::CMD_MULTI_ACTION:
                        $this->forwardApprovalOverviewGUI();
                        break;
                    case ApprovalGUI::CMD_SHOW_FINISHED_APPROVALS:
                        $this->forwardFinishedApprovals();
                        break;
                    case ApprovalGUI::CMD_SHOW_BLOCKED_APPROVALS:
                        $this->forwardBlockedApprovals();
                        break;
                    case ApprovalGUI::CMD_SHOW_MY_APPROVALS:
                        $this->forwardMyApprovalsOverview();
                        break;
                    default:
                        throw new Exception("ilObjBookingApprovalsGUI: Unknown command: " . $cmd);
                }
        }
    }

    public function getAfterCreationCmd() : string
    {
        return self::CMD_EDIT_PROPERTIES;
    }

    public function getStandardCmd() : string
    {
        if (!$this->object->checkAccess()) {
            return APPROVALGUI::CMD_SHOW_SUMMARY;
        }

        if ($this->object->checkSuperior()) {
            return ApprovalGUI::CMD_SHOW_OVERVIEW;
        }

        return ApprovalGUI::CMD_SHOW_MY_APPROVALS;
    }

    protected function forwardSettings()
    {
        $this->g_tabs->activateTab(self::TAB_SETTINGS);
        $gui = new \ilBookingApprovalsSettingsGUI(
            $this,
            $this->object->getObjectActions(),
            $this->object->txtClosure()
        );
        $this->g_ctrl->forwardCommand($gui);
    }

    public function forwardApprovalOverviewGUI()
    {
        $this->g_tabs->activateTab(self::TAB_APPROVALS_OVERVIEW);

        $gui = new \ilApprovalsOverviewGUI(
            $this,
            $this->plugin->getCourseUtils(),
            $this->plugin->getOrguUtils(),
            $this->plugin->getIliasWrapper(),
            $this->object->getObjectActions(),
            $this->plugin->getApprovalActions(),
            $this->object->txtClosure()
        );

        $this->g_ctrl->forwardCommand($gui);
    }

    public function forwardFinishedApprovals()
    {
        $this->g_tabs->activateTab(self::TAB_FINISHED_APPROVALS);

        $gui = new \ilFinishedApprovalsGUI(
            $this,
            $this->plugin->getCourseUtils(),
            $this->plugin->getOrguUtils(),
            $this->plugin->getIliasWrapper(),
            $this->object->getObjectActions(),
            $this->plugin->getApprovalActions(),
            $this->object->txtClosure()
        );

        $this->g_ctrl->forwardCommand($gui);
    }

    public function forwardMyApprovalsOverview()
    {
        $this->g_tabs->activateTab(self::TAB_MY_APPROVALS_OVERVIEW);

        $gui = new \ilMyApprovalsOverviewGUI(
            $this,
            $this->plugin->getCourseUtils(),
            $this->plugin->getOrguUtils(),
            $this->plugin->getIliasWrapper(),
            $this->object->getObjectActions(),
            $this->plugin->getApprovalActions(),
            $this->object->txtClosure()
        );

        $this->g_ctrl->forwardCommand($gui);
    }

    public function forwardBlockedApprovals()
    {
        $this->g_tabs->activateTab(self::TAB_BLOCKED_APPROVALS);

        $gui = new \ilBlockedApprovalsGUI(
            $this,
            $this->plugin->getCourseUtils(),
            $this->plugin->getOrguUtils(),
            $this->plugin->getIliasWrapper(),
            $this->object->getObjectActions(),
            $this->plugin->getApprovalActions(),
            $this->object->txtClosure()
        );

        $this->g_ctrl->forwardCommand($gui);
    }

    /**
     * Set the title byline to offline if object is offline.
     */
    protected function setTitleByline()
    {
        require_once(__DIR__ . "/class.ilObjBookingApprovalsAccess.php");

        if (ilObjBookingApprovalsAccess::_isOffline($this->object->getId())) {
            $this->g_tpl->setAlertProperties(array(
            [
                "alert" => true,
                "property" => $this->g_lng->txt("status"),
                "value" => $this->g_lng->txt("offline")
            ]));
        }
    }

    /**
     * Redirect to settings gui.
     *
     * @return 	void
     */
    protected function redirectSettings()
    {
        $link = $this->g_ctrl->getLinkTargetByClass(
            array("ilObjBookingApprovalsGUI", "ilBookingApprovalsSettingsGUI"),
            ilBookingApprovalsSettingsGUI::CMD_EDIT_PROPERTIES,
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
        // Links
        $settings = $this->g_ctrl->getLinkTargetByClass(
            array("ilObjBookingApprovalsGUI", "ilBookingApprovalsSettingsGUI"),
            ilBookingApprovalsSettingsGUI::CMD_EDIT_PROPERTIES
        );

        $approval_overview = $this->g_ctrl->getLinkTargetByClass(
            array("ilObjBookingApprovalsGUI", "ilApprovalsOverviewGUI"),
            ApprovalGUI::CMD_SHOW_OVERVIEW
        );

        $finished_approvals = $this->g_ctrl->getLinkTargetByClass(
            array("ilObjBookingApprovalsGUI", "ilFinishedApprovalsGUI"),
            ApprovalGUI::CMD_SHOW_FINISHED_APPROVALS
        );

        $my_approvals_overview = $this->g_ctrl->getLinkTargetByClass(
            array("ilObjBookingApprovalsGUI", "ilMyApprovalsOverviewGUI"),
            ApprovalGUI::CMD_SHOW_MY_APPROVALS
        );

        $blocked_approvals = $this->g_ctrl->getLinkTargetByClass(
            array("ilObjBookingApprovalsGUI", "ilBlockedApprovalsGUI"),
            ApprovalGUI::CMD_SHOW_BLOCKED_APPROVALS
        );

        // Tabs
        $this->addInfoTab();

        if ($this->g_access->checkAccess("read", "", $this->object->getRefId()) &&
            $this->object->getSettings()->getSuperiorView()) {
            $this->g_tabs->addTab(self::TAB_APPROVALS_OVERVIEW, $this->txt("approval_overview"), $approval_overview);
        }

        if ($this->g_access->checkAccess("read", "", $this->object->getRefId()) &&
            !$this->object->getSettings()->getSuperiorView()) {
            $this->g_tabs->addTab(self::TAB_MY_APPROVALS_OVERVIEW, $this->txt("my_approvals_overview"), $my_approvals_overview);
        }

        if ($this->g_access->checkAccess("read", "", $this->object->getRefId())) {
            $this->g_tabs->addTab(self::TAB_FINISHED_APPROVALS, $this->txt("finished_approvals"), $finished_approvals);
        }

        if (
            $this->g_access->checkAccess(
                "read",
                "",
                $this->object->getRefId()
            ) &&
                $this->object->getSettings()->getSuperiorView()
        ) {
            $this->g_tabs->addTab(self::TAB_BLOCKED_APPROVALS, $this->txt("blocked_approvals"), $blocked_approvals);
        }

        if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
            $this->g_tabs->addTab(self::TAB_SETTINGS, $this->txt("settings"), $settings);
        }

        $this->addPermissionTab();
    }

    protected function clearTabsAndTitle()
    {
        $this->g_tpl->setTitle("");
        $this->g_tpl->setTitleIcon("");
        $this->g_tabs->clearTargets();
    }

    public static function _goto($a_target)
    {
        $ref_id = (int) $a_target[0];

        global $DIC;
        $ctrl = $DIC["ilCtrl"];
        $ctrl->setTargetScript("ilias.php");
        $ctrl->initBaseClass("ilobjplugindispatchgui");
        $ctrl->getCallStructure(strtolower("ilObjPluginDispatchGUI"));

        /** @var ilObjBookingApprovals $obj */
        $obj = ilObjectFactory::getInstanceByRefId($ref_id);
        $ctrl->setParameterByClass("ilObjBookingApprovalsGUI", "ref_id", $ref_id);
        $target = ["ilObjPluginDispatchGUI",'ilObjBookingApprovalsGUI', 'ilMyApprovalsOverviewGUI'];
        $cmd = ApprovalGUI::CMD_SHOW_MY_APPROVALS;

        if ($obj->getSettings()->getSuperiorView()) {
            $target = ["ilObjPluginDispatchGUI",'ilObjBookingApprovalsGUI', 'ilApprovalsOverviewGUI'];
            $cmd = ApprovalGUI::CMD_SHOW_OVERVIEW;
        }

        $link = $ctrl->getLinkTargetByClass(
            $target,
            $cmd,
            "",
            false,
            false
        );

        $ctrl->clearParametersByClass("ilObjBookingApprovalsGUI");
        $ctrl->redirectToURL($link);
    }
}
