<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");

use \CaT\Plugins\MaterialList;

/**
 * GUI class to add or delete training provider, trainer or tags
 *
 * @ilCtrl_Calls ilMaterialListConfigGUI: ilHeaderConfigurationGUI
 * @ilCtrl_Calls ilMaterialListConfigGUI: ilMaterialsGUI
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilMaterialListConfigGUI extends ilPluginConfigGUI
{
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
        require_once($this->plugin_object->getDirectory() . "/classes/HeaderConfiguration/class.ilHeaderConfigurationGUI.php");
        require_once($this->plugin_object->getDirectory() . "/classes/Materials/class.ilMaterialsGUI.php");
        $this->plugin_actions = $this->plugin_object->getActions();
        $this->xls_header_options = $this->plugin_object->getXLSHeaderOptions();

        $this->setTabs();

        $next_class = $this->g_ctrl->getNextClass();

        switch ($next_class) {
            case "ilheaderconfigurationgui":
                $this->forwardHeaderConfiguration();
                break;
            case "ilmaterialsgui":
                $this->fowardMaterials();
                break;
            default:
                switch ($cmd) {
                    case ilHeaderConfigurationGUI::CMD_CONFIGURE:
                    case ilHeaderConfigurationGUI::CMD_VIEW_ENTRIES:
                        $this->forwardHeaderConfiguration();
                        break;
                    case ilMaterialsGUI::CMD_MATERIALS:
                        $this->fowardMaterials();
                        break;
                    default:
                        throw new Exception("ilTrainingProviderConfigGUI:: Unknown command: " . $cmd);
                }
        }
    }

    /**
     * Forwardng command to header configuration gui
     *
     * @return null
     */
    protected function forwardHeaderConfiguration()
    {
        $this->g_tabs->activateTab(ilHeaderConfigurationGUI::CMD_VIEW_ENTRIES);
        $type_form_factory = new MaterialList\HeaderConfiguration\TypeFormFactory(
            $this->plugin_object->txtClosure(),
            $this->xls_header_options->getSourceForValueOptionsByType(MaterialList\HeaderConfiguration\ConfigurationEntry::TYPE_STANDARD),
            $this->xls_header_options->getSourceForValueOptionsByType(MaterialList\HeaderConfiguration\ConfigurationEntry::TYPE_AMD),
            $this->xls_header_options->getSourceForValueOptionsByType(MaterialList\HeaderConfiguration\ConfigurationEntry::TYPE_FUNCTION)
        );
        $gui = new ilHeaderConfigurationGUI($this, $this->plugin_object, $type_form_factory);
        $this->g_ctrl->forwardCommand($gui);
    }

    /**
     * Forwardng command to materials gui
     *
     * @return null
     */
    protected function fowardMaterials()
    {
        $this->g_tabs->activateTab(ilMaterialsGUI::CMD_MATERIALS);
        $backend = new MaterialList\Materials\MaterialsBackend($this->plugin_actions);
        $table_processor = new MaterialList\TableProcessing\TableProcessor($backend);
        $gui = new ilMaterialsGUI($this, $this->plugin_actions, $this->plugin_object->txtClosure(), $table_processor);
        $this->g_ctrl->forwardCommand($gui);
    }

    /**
     * Sets tabs for provider, trainer and tags
     *
     * @return null
     */
    protected function setTabs()
    {
        $header_configuration_link = $this->g_ctrl->getLinkTargetByClass(array("ilMaterialListConfigGUI", "ilHeaderConfigurationGUI"), ilHeaderConfigurationGUI::CMD_VIEW_ENTRIES);
        $materials_link = $this->g_ctrl->getLinkTargetByClass(array("ilMaterialListConfigGUI", "ilMaterialsGUI"), ilMaterialsGUI::CMD_MATERIALS);

        $this->g_tabs->addTab(ilHeaderConfigurationGUI::CMD_VIEW_ENTRIES, $this->plugin_object->txt("conf_header_configuration"), $header_configuration_link);
        $this->g_tabs->addTab(ilMaterialsGUI::CMD_MATERIALS, $this->plugin_object->txt("conf_materials"), $materials_link);
    }
}
