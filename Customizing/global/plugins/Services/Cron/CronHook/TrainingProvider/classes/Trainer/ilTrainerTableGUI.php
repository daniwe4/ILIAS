<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace CaT\Plugins\TrainingProvider\Trainer;

require_once("Services/Table/classes/class.ilTable2GUI.php");

/**
 * Table GUI for Trainer configuration
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de
 */
class ilTrainerTableGUI extends \ilTable2GUI
{
    public function __construct($parent_object, $plugin_object, $a_parent_cmd = "", $a_template_context = "")
    {
        $this->setId("tp_Trainer");
        $this->plugin_object = $plugin_object;
        parent::__construct($parent_object, $a_parent_cmd, $a_template_context);

        global $ilCtrl, $tpl;
        $this->gCtrl = $ilCtrl;
        $this->gTpl = $tpl;

        $this->gTpl->addCss($this->plugin_object->getDirectory() . "/templates/default/trainer_table.css");

        $this->configureTable();
    }

    protected function fillRow($a_set)
    {
        if (!$a_set["active"]) {
            $this->tpl->setVariable("STRIKE_START", "<s>");
            $this->tpl->setVariable("STRIKE_END", "</s>");
        }

        $this->tpl->setVariable("NAME", $a_set["name"]);
        $this->tpl->setVariable("PROVIDER", $a_set["provider"]);
        $this->tpl->setVariable("EMAIL", $a_set["email"]);
        $this->tpl->setVariable("PHONE", $a_set["phone"]);
        $this->tpl->setVariable("MOBILE_NUMBER", $a_set["mobile_number"]);
        $this->tpl->setVariable("FEE", number_format($a_set["fee"], 2));
        $this->tpl->setVariable("EXTRA_INFOS", $a_set["extra_infos"]);
        $this->tpl->setVariable("ACTIONS", $this->getActionMenu($a_set["id"]));
    }

    protected function configureTable()
    {
        $this->setEnableTitle(true);
        $this->setTitle($this->txt("trainer"));
        $this->setTopCommands(false);
        $this->setEnableHeader(true);
        $this->setExternalSorting(false);
        $this->setExternalSegmentation(false);
        $this->setRowTemplate("tpl.trainer_table_row.html", $this->plugin_object->getDirectory());
        $this->setShowRowsSelector(false);

        $this->setFormAction($this->gCtrl->getFormAction($this->parent_obj, "showTrainer"));

        foreach ($this->getBaseColumns() as $lng_var => $params) {
            $this->addColumn($this->txt($lng_var), $params[0]);
        }
    }

    protected function txt($code)
    {
        return $this->plugin_object->txt($code);
    }

    protected function getBaseColumns()
    {
        return array("name" => array("name")
                     , "provider" => array("provider")
                     , "email" => array("email")
                     , "phone" => array(null)
                     , "mobile_number" => array(null)
                     , "fee" => array("fee")
                     , "extra_infos" => array("extra_infos")
                     , "actions" => array(null)
                );
    }

    protected function getActionMenu($id)
    {
        include_once("Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
        $current_selection_list = new \ilAdvancedSelectionListGUI();
        $current_selection_list->setAsynch(false);
        $current_selection_list->setAsynchUrl(true);
        $current_selection_list->setListTitle($this->txt("actions"));
        $current_selection_list->setId($id);
        $current_selection_list->setSelectionHeaderClass("small");
        $current_selection_list->setItemLinkClass("xsmall");
        $current_selection_list->setLinksMode("il_ContainerItemCommand2");
        $current_selection_list->setHeaderIcon(\ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
        $current_selection_list->setUseImages(false);
        $current_selection_list->setAdditionalToggleElement("id" . $id, "ilContainerListItemOuterHighlight");

        foreach ($this->getActionMenuItems($id) as $key => $value) {
            $current_selection_list->addItem($value["title"], "", $value["link"], $value["image"], "", $value["frame"]);
        }

        return $current_selection_list->getHTML();
    }

    protected function getActionMenuItems($id)
    {
        $this->gCtrl->setParameter($this->parent_obj, "id", $id);
        $link_edit = $this->memberlist_link = $this->gCtrl->getLinkTarget($this->parent_obj, "editTrainer");
        $link_delete = $this->memberlist_link = $this->gCtrl->getLinkTarget($this->parent_obj, "deleteConfirmTrainer");
        $this->gCtrl->clearParameters($this->parent_obj);

        $items = array();
        $items[] = array("title" => $this->txt("edit_trainer"), "link" => $link_edit, "image" => "", "frame" => "");
        $items[] = array("title" => $this->txt("delete_trainer"), "link" => $link_delete, "image" => "", "frame" => "");

        return $items;
    }

    /**
     * Set current filter values to keep if sorting
     *
     * @param mixed[] 	$filter_values
     *
     * @return null
     */
    public function setFilterValues(array $filter_values)
    {
        $this->filter_values = $filter_values;
    }

    public function render()
    {
        $this->gCtrl->setParameter($this->parent_obj, "filter_values", base64_encode(serialize($this->filter_values)));
        $ret = parent::render();
        $this->gCtrl->setParameter($this->parent_obj, "filter_values", null);

        return $ret;
    }

    public function numericOrdering($a_field)
    {
        if ($a_field === "fee") {
            return true;
        }
        return false;
    }
}
