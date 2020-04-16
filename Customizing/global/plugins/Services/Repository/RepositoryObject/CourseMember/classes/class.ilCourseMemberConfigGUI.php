<?php

include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");
/**
 *
 * @ilCtrl_Calls ilCourseMemberConfigGUI: ilLPOptionsGUI, ilSiglistConfigGUI, ilNotFinalizedGUI
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
use CaT\Plugins\CourseMember;

class ilCourseMemberConfigGUI extends ilPluginConfigGUI
{
    use CourseMember\DI;

    const CMD_CONFIGURE = "configure";

    const TAB_LP_OPTIONS = "tab_lp_options";
    const TAB_SIGLIST = "tab_siglist";
    const TAB_REMINDER = "tab_reminder";

    /**
     * @var \$ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var \ilTabsGUI
     */
    protected $g_tabs;

    /**
     * @var Pimple\Container
     */
    protected $dic;

    public function __construct()
    {
        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tabs = $DIC->tabs();
        $this->g_toolbar = $DIC->toolbar();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_lng = $DIC->language();
    }

    /**
     * @inheritdoc
     *
     * @throws \Exception
     *
     * @return void
     */
    public function performCommand($cmd)
    {
        global $DIC;
        $this->dic = $this->getPluginDIC($this->plugin_object, $DIC);

        require_once($this->plugin_object->getDirectory() . "/classes/LPOptions/class.ilLPOptionsGUI.php");
        require_once($this->plugin_object->getDirectory() . "/classes/SignatureList/class.ilSiglistConfigGUI.php");

        $next_class = $this->g_ctrl->getNextClass();
        $this->setTabs();
        switch ($next_class) {
            case "illpoptionsgui":
                $this->activateTab(self::TAB_LP_OPTIONS);
                $actions = $this->plugin_object->getLPOptionActions();
                $backend = new CaT\Plugins\CourseMember\LPOptions\LPOptionBackend($actions);
                $table_processor = new CaT\Plugins\CourseMember\TableProcessing\TableProcessor($backend);
                $gui = new ilLPOptionsGUI($this, $actions, $table_processor, $this->g_ctrl, $this->g_tpl, $this->g_toolbar, $this->g_lng);
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "ilsiglistconfiggui":
                $this->activateTab(self::TAB_SIGLIST);
                $this->g_ctrl->forwardCommand($this->dic["SignatureList.ilSiglistConfigGUI"]);
                break;
            case "ilnotfinalizedgui":
                $this->activateTab(self::TAB_REMINDER);
                $gui = $this->dic["config.notfinalizedgui"];
                $this->g_ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_CONFIGURE:
                        $cmd = ilLPOptionsGUI::CMD_SHOW_OPTIONS;
                        //Change command for net class
                        // no break
                    case ilLPOptionsGUI::CMD_SHOW_OPTIONS:
                        $this->forwardLPOptions($cmd);
                        break;
                }
        }
    }

    /**
     * Sets tabs
     *
     * @return void
     */
    protected function setTabs()
    {
        $link_category = $this->g_ctrl->getLinkTargetByClass(array("ilCourseMemberConfigGUI", "ilLPOptionsGUI"), ilLPOptionsGUI::CMD_SHOW_OPTIONS);
        $link_siglist = $this->g_ctrl->getLinkTargetByClass(array("ilCourseMemberConfigGUI", "ilSiglistConfigGUI"), ilSiglistConfigGUI::CMD_SHOW);
        $link_reminder = $this->dic["config.notfinalized.link"];

        $tabs[$this->plugin_object->txt("lp_options")] = array(self::TAB_LP_OPTIONS, $link_category);
        $tabs[$this->plugin_object->txt("siglist_config")] = array(self::TAB_SIGLIST, $link_siglist);
        $tabs[$this->plugin_object->txt("reminder")] = array(self::TAB_REMINDER, $link_reminder);

        foreach ($tabs as $caption => $tab) {
            $this->g_tabs->addTab($tab[0], $caption, $tab[1]);
        }
    }

    /**
     * Activate tab to display as current
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
     * Redirect to options gui. To keep the next class oportunity
     *
     * @param string 	$cmd
     *
     * @return void
     */
    protected function forwardLPOptions($cmd)
    {
        $link = $this->g_ctrl->getLinkTargetByClass(array("ilCourseMemberConfigGUI", "ilLPOptionsGUI"), $cmd, '', false, false);
        ilUtil::redirect($link);
    }
}
