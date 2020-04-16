<?php

namespace CaT\Plugins\MaterialList\HeaderConfiguration;

use \CaT\Plugins\MaterialList\ilPluginActions;

require_once("Services/Table/classes/class.ilTable2GUI.php");

/**
 * Table for configuration entries
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilHeaderConfigurationTableGUI extends \ilTable2GUI
{
    public function __construct(\ilHeaderConfigurationGUI $parent_object, \ilMaterialListPlugin $plugin_object)
    {
        parent::__construct($parent_object);

        $this->xls_header_options = $plugin_object->getXLSHeaderOptions();
        $this->txt = $plugin_object->txtClosure();

        $this->setEnableTitle(true);
        $this->setTitle($plugin_object->txt("configuration_entries"));
        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(false);
        $this->setRowTemplate("tpl.configuration_entries_row.html", $plugin_object->getDirectory());
        $this->setShowRowsSelector(false);
        $this->setLimit(0);

        $this->addColumn("", "", "1", true);
        $this->addColumn($plugin_object->txt("type"), false);
        $this->addColumn($plugin_object->txt("source_for_value"), false);
    }

    public function fillRow($a_set)
    {
        $options = $this->xls_header_options->getSourceForValueOptionsByType($a_set->getType());

        $this->tpl->setVariable("POST_VAR", ilPluginActions::F_IDS_TO_DEL);
        $this->tpl->setVariable("ID", $a_set->getId());

        if (array_key_exists("getVenueUnset", $options) && $a_set->getSourceForValue() === "getVenueUnset") {
            $this->tpl->setCurrentBlock("linethrought");
        } else {
            $this->tpl->setCurrentBlock("standard");
        }
        $this->tpl->setVariable("TYPE", $this->txt("type_" . $a_set->getType()));
        $this->tpl->parseCurrentBlock();

        require_once("Services/Form/classes/class.ilSelectInputGUI.php");
        $value = new \ilSelectInputGUI("", ilPluginActions::F_SOURCES_FOR_VALUE . "[]");
        $value->setOptions($options);
        $value->setValue($a_set->getSourceForValue());
        $this->tpl->setVariable("VALUE", $value->render());

        $this->tpl->setVariable("HIDDEN_ID_POST", ilPluginActions::F_IDS . "[]");
        $this->tpl->setVariable("HIDDEN_ID", $a_set->getId());

        $this->tpl->setVariable("HIDDEN_TYPE_POST", ilPluginActions::F_TYPES . "[]");
        $this->tpl->setVariable("HIDDEN_TYPE", $a_set->getType());
    }

    /**
     * Translate code to lang value
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt($code)
    {
        assert('is_string($code)');

        $txt = $this->txt;

        return $txt($code);
    }
}
