<?php
require_once("./Services/Component/classes/class.ilPluginConfigGUI.php");

use CaT\Plugins\CronJobSurveillance\Config\IliasFormBuilder;
use CaT\Plugins\CronJobSurveillance\Config\ConfigurationForm;

/**
 * GUI class for configure CronJobSurveillance
 *
 * @ilCtrl_Calls ilCronJobSurveillanceConfigGUI: ilCJSConfigGUI
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE
 */
class ilCronJobSurveillanceConfigGUI extends ilPluginConfigGUI
{
    /**
     * @var \ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var \ilTabsGUI
     */
    protected $g_tabs;

    public function __construct()
    {
        global $DIC;

        $this->g_ctrl = $DIC->ctrl();
        $this->g_tabs = $DIC->tabs();
    }

    public function performCommand($cmd)
    {
        require_once(__DIR__ . "/Config/class.ilCJSConfigGUI.php");

        $this->setTabs();

        $next_class = $this->g_ctrl->getNextClass();

        switch ($next_class) {
            case "ilcjsconfiggui":
                $this->forwardCJSConfigGUI();
                break;
            default:
                $this->g_ctrl->redirectByClass(ilCJSConfigGUI::class, ilCJSConfigGUI::CMD_SHOW_CONFIG);
        }
    }

    /**
     * Forwardng command to cron job surveillance config gui.
     *
     * @return void
     */
    protected function forwardCJSConfigGUI()
    {
        $this->g_tabs->activateTab(ilCJSConfigGUI::CMD_SHOW_CONFIG);

        $ifb = new IliasFormBuilder();
        $cf = new ConfigurationForm($this->plugin_object->getCronManager(), $this->plugin_object->txtClosure());

        $gui = new ilCJSConfigGUI(
            $ifb,
            $cf,
            $this->plugin_object->getConfigDB(),
            $this->plugin_object->txtClosure()
        );

        $this->g_ctrl->forwardCommand($gui);
    }

    /**
     * Sets tabs for ilCronJobSurveillanceConfigGUI
     *
     * @return void
     */
    protected function setTabs()
    {
        $config_link = $this->g_ctrl->getLinkTargetByClass(
            array("ilCronJobSurveillanceConfigGUI", "ilCJSConfigGUI"),
            ilCJSConfigGUI::CMD_SHOW_CONFIG
            );

        $this->g_tabs->addTab(
            ilCJSConfigGUI::CMD_SHOW_CONFIG,
            $this->plugin_object->txt("jobs_under_surveillance"),
            $config_link
        );
    }
}
