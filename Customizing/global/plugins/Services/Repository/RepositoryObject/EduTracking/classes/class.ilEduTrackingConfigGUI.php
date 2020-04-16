<?php
include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");

/**
 * Config gui of plugin
 *
 * @ilCtrl_Calls ilEduTrackingConfigGUI: ilConfigGTIGUI, ilConfigIDDGUI, ilConfigWBDGUI
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */

class ilEduTrackingConfigGUI extends ilPluginConfigGUI
{
    const TAB_WBD = "tab_wbd";
    const TAB_IDD = "tab_idd";
    const TAB_GTI = "tab_gti";

    const CMD_CONFIG = "configure";

    /**
     * @var \$ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var \$ilTabs
     */
    protected $g_tabs;

    public function __construct()
    {
        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tabs = $DIC->tabs();
    }

    /**
     * @inheritdoc
     *
     * @throws \Exception
     */
    public function performCommand($cmd)
    {
        require_once($this->plugin_object->getDirectory() . "/classes/Purposes/WBD/Configuration/class.ilConfigWBDGUI.php");
        require_once($this->plugin_object->getDirectory() . "/classes/Purposes/IDD/Configuration/class.ilConfigIDDGUI.php");
        require_once($this->plugin_object->getDirectory() . "/classes/Purposes/GTI/Configuration/class.ilConfigGTIGUI.php");

        $this->setTabs();
        $next_class = $this->g_ctrl->getNextClass();

        switch ($next_class) {
            case "ilconfigwbdgui":
                $this->activateTab(self::TAB_WBD);
                $actions = $this->plugin_object->getConfigActionsFor("WBD");
                $gui = new ilConfigWBDGUI($this, $actions, $this->plugin_object);
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "ilconfigiddgui":
                $this->activateTab(self::TAB_IDD);
                $actions = $this->plugin_object->getConfigActionsFor("IDD");
                $gui = new ilConfigIDDGUI($this, $actions, $this->plugin_object);
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "ilconfiggtigui":
                $this->activateTab(self::TAB_GTI);
                $actions = $this->plugin_object->getConfigActionsFor("GTI");
                $gui = new ilConfigGTIGUI($this, $actions, $this->plugin_object);
                $this->g_ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_CONFIG:
                        $this->redirectWBD();
                        break;
                }
        }
    }

    /**
     * Redirect to wbd config to keep next class option
     *
     * @return void
     */
    protected function redirectWBD()
    {
        $link = $this->g_ctrl->getLinkTargetByClass(array("ilEduTrackingConfigGUI", "ilConfigWBDGUI"), ilConfigWBDGUI::CMD_SHOW, '', false, false);
        ilUtil::redirect($link);
    }

    /**
     * Sets tabs for provider, trainer and tags
     *
     * @return null
     */
    protected function setTabs()
    {
        $link = $this->g_ctrl->getLinkTargetByClass(array("ilEduTrackingConfigGUI", "ilConfigWBDGUI"), ilConfigWBDGUI::CMD_SHOW);
        $this->g_tabs->addTab(self::TAB_WBD, $this->txt(self::TAB_WBD), $link);

        $link = $this->g_ctrl->getLinkTargetByClass(array("ilEduTrackingConfigGUI", "ilConfigIDDGUI"), ilConfigIDDGUI::CMD_SHOW);
        $this->g_tabs->addTab(self::TAB_IDD, $this->txt(self::TAB_IDD), $link);

        $link = $this->g_ctrl->getLinkTargetByClass(array("ilEduTrackingConfigGUI", "ilConfigGTIGUI"), ilConfigGTIGUI::CMD_SHOW);
        $this->g_tabs->addTab(self::TAB_GTI, $this->txt(self::TAB_GTI), $link);
    }

    /**
     * Activates current tab
     *
     * @param string 	$tab
     *
     * @return void
     */
    protected function activateTab($tab)
    {
        $this->g_tabs->activateTab($tab);
    }

    /**
     * @param 	string	$code
     * @return	string
     */
    protected function txt($code)
    {
        return $this->plugin_object->txt($code);
    }
}
