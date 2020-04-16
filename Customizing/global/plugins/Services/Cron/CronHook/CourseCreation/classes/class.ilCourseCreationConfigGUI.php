<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");

use \CaT\Plugins\CourseCreation;

/**
 * GUI class to add or delete training provider, trainer or tags
 *
 * @ilCtrl_Calls ilCourseCreationConfigGUI: ilOpenRequestsGUI, ilNotSuccessfullRequestsGUI, ilFinishedRequestsGUI
 * @ilCtrl_Calls ilCourseCreationConfigGUI: ilCreationSettingsGUI, ilFailedCreationRecipientsGUI
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilCourseCreationConfigGUI extends ilPluginConfigGUI
{
    const CMD_CONFIGURE = "configure";
    const TAB_OPEN_REQUESTS = "open_requests";
    const TAB_NOT_SUCCESSFUL_REQUESTS = "not_successful_requests";
    const TAB_FINISHED_REQUESTS = "finished_request";
    const TAB_CREATION_SETTINGS = "creation_settings";
    const TAB_RECIPIENTS = "recipients";

    /**
     * @var \ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var \ilTabsGUI
     */
    protected $g_tabs;

    /**
     * @var \CaT\Plugins\MaterialList\ilPluginActions | null
     */
    protected $plugin_actions;

    public function __construct()
    {
        global $ilCtrl, $ilTabs;

        $this->g_ctrl = $ilCtrl;
        $this->g_tabs = $ilTabs;
    }

    public function performCommand($cmd)
    {
        require_once($this->plugin_object->getDirectory() . "/classes/Requests/class.ilOpenRequestsGUI.php");
        require_once($this->plugin_object->getDirectory() . "/classes/Requests/class.ilNotSuccessfullRequestsGUI.php");
        require_once($this->plugin_object->getDirectory() . "/classes/Requests/class.ilFinishedRequestsGUI.php");
        require_once($this->plugin_object->getDirectory() . "/classes/CreationSettings/class.ilCreationSettingsGUI.php");

        $this->setTabs();
        $next_class = $this->g_ctrl->getNextClass();

        switch ($next_class) {
            case "ilopenrequestsgui":
                $this->g_tabs->activateTab(self::TAB_OPEN_REQUESTS);
                $gui = new ilOpenRequestsGUI($this->plugin_object);
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "ilnotsuccessfullrequestsgui":
                $this->g_tabs->activateTab(self::TAB_NOT_SUCCESSFUL_REQUESTS);
                $gui = new ilNotSuccessfullRequestsGUI($this->plugin_object);
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "ilfinishedrequestsgui":
                $this->g_tabs->activateTab(self::TAB_FINISHED_REQUESTS);
                $gui = new ilFinishedRequestsGUI($this->plugin_object);
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "ilcreationsettingsgui":
                $this->g_tabs->activateTab(self::TAB_CREATION_SETTINGS);
                $gui = $this->plugin_object->getDI()["creationsettings.gui"];
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "ilfailedcreationrecipientsgui":
                $this->g_tabs->activateTab(self::TAB_RECIPIENTS);
                $gui = $this->plugin_object->getDI()["creationsettings.config.failedrecipients.gui"];
                $this->g_ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_CONFIGURE:
                        $this->redirectOpenRequests();
                        break;
                    default:
                        throw new Exception("ilTrainingProviderConfigGUI:: Unknown command: " . $cmd);
                }
        }
    }

    /**
     * Redirect to open requests
     *
     * @return void
     */
    protected function redirectOpenRequests()
    {
        $link = $this->g_ctrl->getLinkTargetByClass("ilOpenRequestsGUI", ilOpenRequestsGUI::CMD_VIEW_ENTRIES, '', false, false);
        ilUtil::redirect($link);
    }

    /**
     * Sets tabs for provider, trainer and tags
     */
    protected function setTabs()
    {
        $open_requests = $this->g_ctrl->getLinkTargetByClass(
            "ilOpenRequestsGUI",
            ilOpenRequestsGUI::CMD_VIEW_ENTRIES
        );
        $this->g_tabs->addTab(
            self::TAB_OPEN_REQUESTS,
            $this->plugin_object->txt("conf_open_requests"),
            $open_requests
        );

        $not_successfull_requests = $this->g_ctrl->getLinkTargetByClass(
            "ilNotSuccessfullRequestsGUI",
            ilNotSuccessfullRequestsGUI::CMD_VIEW_ENTRIES
        );
        $this->g_tabs->addTab(
            self::TAB_NOT_SUCCESSFUL_REQUESTS,
            $this->plugin_object->txt("conf_not_successful_requests"),
            $not_successfull_requests
        );

        $finished_requests = $this->g_ctrl->getLinkTargetByClass(
            "ilFinishedRequestsGUI",
            ilFinishedRequestsGUI::CMD_VIEW_ENTRIES
        );
        $this->g_tabs->addTab(
            self::TAB_FINISHED_REQUESTS,
            $this->plugin_object->txt("conf_finished_requests"),
            $finished_requests
        );

        $creation_settings = $this->g_ctrl->getLinkTargetByClass(
            "ilCreationSettingsGUI",
            ilCreationSettingsGUI::CMD_SHOW_SETTINGS
        );
        $this->g_tabs->addTab(
            self::TAB_CREATION_SETTINGS,
            $this->plugin_object->txt("conf_creation_settings"),
            $creation_settings
        );

        $this->g_tabs->addTab(
            self::TAB_RECIPIENTS,
            $this->plugin_object->txt("conf_recipients"),
            $this->plugin_object->getDI()["creationsettings.config.failedrecipients.gui.link"]
        );
    }
}
