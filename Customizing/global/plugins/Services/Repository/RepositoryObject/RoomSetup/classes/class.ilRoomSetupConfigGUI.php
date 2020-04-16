<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

use \CaT\Plugins\RoomSetup\TableProcessing\TableProcessor;
use \CaT\Plugins\RoomSetup\ServiceOptions\ServiceOptionBackend;

include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");

/**
 * GUI class to add or delete training provider, trainer or tags
 *
 * @ilCtrl_Calls ilRoomSetupConfigGUI: ilServiceOptionsGUI
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilRoomSetupConfigGUI extends ilPluginConfigGUI
{
    const CMD_CONFIGURE = "configure";

    /**
     * @var \ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var \ilTabsGUI
     */
    protected $g_tabs;

    /**
     * @var \CaT\Plugins\RoomSetup\ilPluginActions | null
     */
    protected $plugin_actions;

    public function __construct()
    {
        global $DIC;

        $this->g_ctrl = $DIC->ctrl();
        $this->g_tabs = $DIC->tabs();
    }

    public function performCommand($cmd)
    {
        require_once($this->plugin_object->getDirectory() . "/classes/ServiceOptions/class.ilServiceOptionsGUI.php");
        $this->plugin_actions = $this->plugin_object->getActions();

        $this->setTabs();

        $next_class = $this->g_ctrl->getNextClass();

        switch ($next_class) {
            case "ilserviceoptionsgui":
                $backend = new ServiceOptionBackend($this->plugin_actions);
                $table_processor = new TableProcessor($backend);
                $this->g_tabs->activateTab(ilServiceOptionsGUI::CMD_SHOW_SERVICE_OPTIONS);
                $gui = new ilServiceOptionsGUI($this, $this->plugin_actions, $this->plugin_object->txtClosure(), $table_processor);
                $this->g_ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_CONFIGURE:
                        $this->redirectServiceOptions();
                        break;
                    default:
                        throw new Exception("ilRoomSetupConfigGUI:: Unknown command: " . $cmd);
                }
        }
    }

    protected function redirectServiceOptions()
    {
        $link = $this->g_ctrl->getLinkTargetByClass(
            array("ilRoomSetupConfigGUI", "ilServiceOptionsGUI"),
            ilServiceOptionsGUI::CMD_SHOW_SERVICE_OPTIONS,
            "",
            false,
            false
        );
        ilUtil::redirect($link);
    }

    /**
     * Sets tabs for service options
     */
    protected function setTabs()
    {
        $service_options_configuration_link = $this->g_ctrl->getLinkTargetByClass(array("ilRoomSetupConfigGUI", "ilServiceOptionsGUI"), ilServiceOptionsGUI::CMD_SHOW_SERVICE_OPTIONS);
        $this->g_tabs->addTab(ilServiceOptionsGUI::CMD_SHOW_SERVICE_OPTIONS, $this->plugin_object->txt("service_options_configuration"), $service_options_configuration_link);
    }
}
