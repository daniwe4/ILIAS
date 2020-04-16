<?php

declare(strict_types=1);

require_once __DIR__ . "/Settings/class.ilBookingAcknowledgeSettingsGUI.php";
require_once __DIR__ . "/Acknowledgments/class.ilAcknowledgmentUpcomingGUI.php";
require_once __DIR__ . "/Acknowledgments/class.ilAcknowledgmentFinishedGUI.php";

use CaT\Plugins\BookingAcknowledge\BookingAcknowledge;
use CaT\Plugins\BookingAcknowledge\Acknowledgments\AcknowledgmentGUI;
use CaT\Plugins\BookingAcknowledge\Utils\RequestDigester;
use CaT\Plugins\BookingAcknowledge\Utils\AccessHelper;

/**
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS.
 *
 * @ilCtrl_isCalledBy ilObjBookingAcknowledgeGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjBookingAcknowledgeGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjBookingAcknowledgeGUI: ilBookingAcknowledgeSettingsGUI
 * @ilCtrl_Calls ilObjBookingAcknowledgeGUI: ilAcknowledgmentUpcomingGUI, ilAcknowledgmentFinishedGUI
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class ilObjBookingAcknowledgeGUI extends ilObjectPluginGUI
{
    const CMD_EDIT_PROPERTIES = "editProperties";
    const CMD_SHOW_CONTENT = "showContent";

    const TAB_SETTINGS = "settings";
    const TAB_UPCOMING = "upcoming";
    const TAB_FINISHED = "finished";

    /**
     * @var ilTemplate
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
     * @var AccessHelper
     */
    protected $access_helper;

    /**
     * @var \ilAccess
     */
    protected $g_access;

    /**
     * Called after parent constructor. It's possible to define some plugin special values
     */
    protected function afterConstructor()
    {
        global $DIC;

        $this->g_tabs = $DIC["ilTabs"];
        $this->g_ctrl = $DIC["ilCtrl"];
        $this->g_tpl = $DIC["tpl"];
        $this->g_access = $DIC["ilAccess"];

        $this->digester = new RequestDigester();
    }

    protected function sendSuccess(string $msg)
    {
        \ilUtil::sendSuccess($this->txt($msg), true);
    }


    final public function getType() : string
    {
        return BookingAcknowledge::PLUGIN_ID;
    }

    public function performCommand(string $cmd)
    {
        $next_class = $this->g_ctrl->getNextClass();

        switch ($next_class) {
            case "ilbookingacknowledgesettingsgui":
                $this->forwardSettings();
                break;
            case 'ilacknowledgmentupcominggui':
                $this->forwardUpcoming();
                break;
            case 'ilacknowledgmentfinishedgui':
                $this->forwardFinished();
                break;

            default:

                if ($cmd === RequestDigester::CMD_MULTI_ACTION) {
                    $multi_cmd = $_POST["multi_action"];
                    if (in_array(
                        $multi_cmd,
                        [
                            RequestDigester::CMD_ACKNOWLEDGE_CONFIRM,
                            RequestDigester::CMD_DECLINE_CONFIRM
                        ]
                    )) {
                        $cmd = $multi_cmd;
                    }
                }

                switch ($cmd) {
                    case self::CMD_EDIT_PROPERTIES:
                        $this->redirectSettings();
                        break;
                    case self::CMD_SHOW_CONTENT:
                        if ($this->getAccessHelper()->mayEditSettings()) {
                            $this->redirectSettings();
                        } else {
                            $this->redirectInfoTab();
                        }
                        break;
                    case AcknowledgmentGUI::CMD_SHOW_FINISHED:
                        $this->forwardFinished();
                        break;
                    case AcknowledgmentGUI::CMD_SHOW_UPCOMING:
                    case RequestDigester::CMD_DECLINE_CONFIRM:
                    case RequestDigester::CMD_ACKNOWLEDGE_CONFIRM:
                        $this->forwardUpcoming();
                        break;
                    default:
                        throw new Exception("ilObjBookingAcknowledgeGUI: Unknown command: " . $cmd);
                }
        }
    }

    public function getAfterCreationCmd() : string
    {
        return self::CMD_EDIT_PROPERTIES;
    }

    public function getStandardCmd() : string
    {
        return AcknowledgmentGUI::CMD_SHOW_UPCOMING;
    }

    protected function redirectSettings()
    {
        $link = $this->g_ctrl->getLinkTargetByClass(
            array("ilObjBookingAcknowledgeGUI", "ilBookingAcknowledgeSettingsGUI"),
            ilBookingAcknowledgeSettingsGUI::CMD_EDIT_PROPERTIES,
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    protected function forwardSettings()
    {
        $this->activateTab(self::TAB_SETTINGS);
        $gui = $this->object->getDI()["gui.settings"];
        $this->g_ctrl->forwardCommand($gui);
    }

    protected function forwardUpcoming()
    {
        $this->activateTab(self::TAB_UPCOMING);
        $gui = $this->object->getDI()["gui.upcoming"];
        $this->preservePossiblySetFilters();
        $this->g_ctrl->forwardCommand($gui);
    }

    protected function forwardFinished()
    {
        $this->activateTab(self::TAB_FINISHED);
        $gui = $this->object->getDI()["gui.finished"];
        $this->g_ctrl->forwardCommand($gui);
    }

    protected function preservePossiblySetFilters()
    {
        if ($_POST[RequestDigester::F_FILTER]) {
            $_GET[RequestDigester::F_FILTER] = $_POST[RequestDigester::F_FILTER];
        } else {
            if (array_key_exists(RequestDigester::F_FILTER, $_GET)) {
                $_GET[RequestDigester::F_FILTER] = $_GET[RequestDigester::F_FILTER];
            }
        }
    }

    protected function setTabs()
    {
        $settings = $this->g_ctrl->getLinkTargetByClass(
            array("ilObjBookingAcknowledgeGUI", "ilBookingAcknowledgeSettingsGUI"),
            ilBookingAcknowledgeSettingsGUI::CMD_EDIT_PROPERTIES
        );

        $upcoming = $this->g_ctrl->getLinkTargetByClass(
            array("ilObjBookingAcknowledgeGUI", "ilAcknowledgmentUpcomingGUI"),
            AcknowledgmentGUI::CMD_SHOW_UPCOMING
        );

        $finished = $this->g_ctrl->getLinkTargetByClass(
            array("ilObjBookingAcknowledgeGUI", "ilAcknowledgmentFinishedGUI"),
            AcknowledgmentGUI::CMD_SHOW_FINISHED
        );

        // Tabs
        $this->addInfoTab();

        if ($this->getAccessHelper()->mayEditSettings()) {
            $this->g_tabs->addTab(self::TAB_SETTINGS, $this->txt("settings"), $settings);
        }

        if ($this->getAccessHelper()->mayViewReport()) {
            $this->g_tabs->addTab(self::TAB_UPCOMING, $this->txt("upcoming"), $upcoming);
            $this->g_tabs->addTab(self::TAB_FINISHED, $this->txt("finished"), $finished);
        }
        $this->addPermissionTab();
    }

    protected function activateTab($tab)
    {
        $this->g_tabs->activateTab($tab);
    }

    protected function redirectInfoTab()
    {
        $link = $this->g_ctrl->getLinkTargetByClass(
            array("ilObjBookingAcknowledgeGUI", "ilInfoScreenGUI"),
            "showSummary",
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    protected function getAccessHelper()
    {
        if (!$this->access_helper) {
            $this->access_helper = new AccessHelper($this->g_access, (int) $this->object->getRefId());
        }

        return $this->access_helper;
    }
}
