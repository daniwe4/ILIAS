<?php
include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");


use CaT\Plugins\BookingModalities\TableProcessing\TableProcessor;
use CaT\Plugins\BookingModalities\Settings\SelectableReasons\SelectableReasonsBackend;

/**
 * Config gui of plugin
 *
 * @ilCtrl_Calls ilBookingModalitiesConfigGUI: ilSelectableRolesGUI, ilSelectableReasonsGUI, ilDownloadableDocumentGUI
 * @ilCtrl_Calls ilBookingModalitiesConfigGUI: ilMinMemberGUI
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */

class ilBookingModalitiesConfigGUI extends ilPluginConfigGUI
{
    use CaT\Plugins\BookingModalities\DI;

    const CMD_CONFIGURE = "configure";
    const TAB_ROLES = "tab_roles";
    const TAB_REASONS = "tab_reasons";
    const TAB_DOCUMENT = "tab_document";
    const TAB_MIN_MEMBER = "tab_min_member";

    /**
     * @var \$ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var \$ilTabs
     */
    protected $g_tabs;

    /**
     * @var ilBookingModalitiesPlugin
     */
    protected $plugin_object;

    /**
     * @var Pimple\Container
     */
    protected $dic;

    /**
     * @inheritdoc
     *
     * @throws \Exception
     */
    public function performCommand($cmd)
    {
        global $DIC;
        $this->dic = $this->getPluginDI($this->plugin_object, $DIC);

        require_once($this->plugin_object->getDirectory() . "/classes/Settings/SelectableRoles/class.ilSelectableRolesGUI.php");
        require_once($this->plugin_object->getDirectory() . "/classes/Settings/SelectableReasons/class.ilSelectableReasonsGUI.php");
        require_once($this->plugin_object->getDirectory() . "/classes/Settings/DownloadableDocument/class.ilDownloadableDocumentGUI.php");

        $this->g_ctrl = $this->dic["ilCtrl"];
        $this->g_tabs = $this->dic["ilTabs"];

        $this->setTabs();
        $next_class = $this->g_ctrl->getNextClass();
        switch ($next_class) {
            case "ilselectablerolesgui":
                $this->activateTab(self::TAB_ROLES);
                $gui = new ilSelectableRolesGUI($this, $this->plugin_object->getActions());
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "ilselectablereasonsgui":
                $this->activateTab(self::TAB_REASONS);
                $actions = $this->plugin_object->getActions();
                $table_processor = new TableProcessor(new SelectableReasonsBackend($actions));
                $gui = new ilSelectableReasonsGUI($this, $actions, $table_processor);
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "ildownloadabledocumentgui":
                $this->activateTab(self::TAB_DOCUMENT);
                $actions = $this->plugin_object->getActions();
                $gui = new ilDownloadableDocumentGUI($this, $actions);
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "ilminmembergui":
                $this->activateTab(self::TAB_MIN_MEMBER);
                $gui = $this->dic["config.minmembergui"];
                $this->g_ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_CONFIGURE:
                        $this->forwardSelectableRoles();
                        break;
                    case ilSelectableReasonsGUI::CMD_SHOW_REASONS:
                        $this->redirectSelectableReasons();
                        break;
                }
        }
    }

    /**
     * Redirect to selectable roles gui
     *
     * @return null
     */
    protected function forwardSelectableRoles()
    {
        $link = $this->g_ctrl->getLinkTargetByClass(array("ilBookingModalitiesConfigGUI", "ilSelectableRolesGUI"), ilSelectableRolesGUI::CMD_SHOW_ROLES, '', false, false);
        ilUtil::redirect($link);
    }

    /**
     * Redirect to selectable roles gui
     *
     * @return null
     */
    protected function redirectSelectableReasons()
    {
        $link = $this->g_ctrl->getLinkTargetByClass(array("ilBookingModalitiesConfigGUI", "ilSelectableReasonsGUI"), ilSelectableReasonsGUI::CMD_SHOW_REASONS, '', false, false);
        ilUtil::redirect($link);
    }

    /**
     * Sets tabs for provider, trainer and tags
     *
     * @return null
     */
    protected function setTabs()
    {
        $link_roles = $this->g_ctrl->getLinkTargetByClass(array("ilBookingModalitiesConfigGUI", "ilSelectableRolesGUI"), ilSelectableRolesGUI::CMD_SHOW_ROLES);
        $this->g_tabs->addTab(self::TAB_ROLES, $this->plugin_object->txt("conf_selectable_roles"), $link_roles);

        $link_reasons = $this->g_ctrl->getLinkTargetByClass(array("ilBookingModalitiesConfigGUI", "ilSelectableReasonsGUI"), ilSelectableReasonsGUI::CMD_SHOW_REASONS);
        $this->g_tabs->addTab(self::TAB_REASONS, $this->plugin_object->txt("conf_selectable_reasons"), $link_reasons);

        $link_file = $this->g_ctrl->getLinkTargetByClass(array("ilBookingModalitiesConfigGUI", "ilDownloadableDocumentGUI"), ilDownloadableDocumentGUI::CMD_SHOW_DOC);
        $this->g_tabs->addTab(self::TAB_DOCUMENT, $this->plugin_object->txt("conf_downloadable_document"), $link_file);

        $this->g_tabs->addTab(self::TAB_MIN_MEMBER, $this->plugin_object->txt("conf_min_member"), $this->dic["config.minmember.link"]);
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
}
