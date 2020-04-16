<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace CaT\Plugins\TrainingProvider\Provider;

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once("Services/Utilities/classes/class.ilUtil.php");

/**
 * Table GUI for provider configuration
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de
 */
class ilProviderTableGUI extends \ilTable2GUI
{

    /**
     * @var ILIAS\UI\Implementation\Factory
     */
    protected $g_f;

    /**
     * @var \ILIAS\UI\Renderer
     */
    protected $g_renderer;

    /**
     * @var \ILIAS\Data\Factory
     */
    protected $df;

    public function __construct($parent_object, $plugin_object, array $filtered_tags = array(), $a_parent_cmd = "", $a_template_context = "")
    {
        $this->setId("tp_provider");
        $this->plugin_object = $plugin_object;
        parent::__construct($parent_object, $a_parent_cmd, $a_template_contex);

        global $DIC;
        $this->gCtrl = $DIC->ctrl();
        $this->gTpl = $DIC->ui()->mainTemplate();
        $this->g_f = $DIC->ui()->factory();
        $this->df = new \ILIAS\Data\Factory;
        $this->g_renderer = $DIC->ui()->renderer();
        $this->gTpl->addCss($this->plugin_object->getDirectory() . "/templates/default/provider_table.css");

        $this->configureTable();
        $data = $this->plugin_object->getActions()->getProviderOverviewData($filtered_tags);
        $this->setData($data);
    }

    protected function fillRow($a_set)
    {
        $this->tpl->setVariable("NAME", $a_set["name"]);

        $rating = $a_set["rating"];
        for ($i = 0.2; $i <= 1; $i += 0.2) {
            $this->tpl->setCurrentBlock("rating");
            if ((string) $i <= $rating) {
                $this->tpl->setVariable("RATING", \ilUtil::getImagePath("icon_rate_on.svg"));
                $this->tpl->setVariable("ALT", $i);
            } else {
                $this->tpl->setVariable("RATING", \ilUtil::getImagePath("icon_rate_off.svg"));
                $this->tpl->setVariable("ALT", $i);
            }
            $this->tpl->parseCurrentBlock();
        }

        if ((bool) $a_set["general_agreement"]) {
            $this->tpl->setVariable("GENERAL_AGREEMENT", $this->plugin_object->txt("yes"));
        } else {
            $this->tpl->setVariable("GENERAL_AGREEMENT", $this->plugin_object->txt("no"));
        }

        $this->tpl->setVariable("ACTIONS", $this->getActionMenu($a_set["id"]));
        foreach ($this->getSelectedColumns() as $column) {
            $this->tpl->setCurrentBlock($column);
            switch ($column) {
                case "tags":
                    $value = $this->getTagsHtml($a_set["tags"]);
                    break;
                default:
                    $value = $a_set[$column];
            }
            if ($value === null) {
                $value = "";
            }
            $this->tpl->setVariable(strtoupper($column), $value);
            $this->tpl->parseCurrentBlock($column);
        }
    }

    protected function configureTable()
    {
        $this->setEnableTitle(true);
        $this->setTitle($this->txt("provider"));
        $this->setTopCommands(false);
        $this->setEnableHeader(true);
        $this->setExternalSorting(false);
        $this->setExternalSegmentation(false);
        $this->setRowTemplate("tpl.provider_table_row.html", $this->plugin_object->getDirectory());
        $this->setShowRowsSelector(false);

        $this->setFormAction($this->gCtrl->getFormAction($this->parent_obj, "configure"));

        $columns = $this->getBaseColumns();

        foreach ($this->getSelectedColumns() as $column) {
            $columns[$column] = array($column);
        }

        $columns["actions"] = array(null);

        foreach ($columns as $lng_var => $params) {
            $this->addColumn($this->txt($lng_var), $params[0]);
        }
    }

    protected function txt($code)
    {
        return $this->plugin_object->txt($code);
    }

    /**
     * Get selectable columns
     *
     * @return array[] 	$cols
     */
    public function getSelectableColumns()
    {
        return array("tags" => array("txt" => $this->txt("tags"))
                    , "info" => array("txt" => $this->txt("info"))
                    , "address" => array("txt" => $this->txt("address1"))
                    , "homepage" => array("txt" => $this->txt("homepage"))
                    , "internal_contact" => array("txt" => $this->txt("internal_contact"))
                    , "contact" => array("txt" => $this->txt("contact"))
                    , "terms" => array("txt" => $this->txt("terms"))
                    , "trainer" => array("txt" => $this->txt("trainer"))
                    , "min_fee" => array("txt" => $this->txt("min_fee"))
                    , "max_fee" => array("txt" => $this->txt("max_fee"))
                );
    }

    protected function getBaseColumns()
    {
        return array("name" => array("name")
                     , "rating" => array("rating")
                     , "general_agreement" => array("general_agreement")
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
        $link_edit = $this->memberlist_link = $this->gCtrl->getLinkTarget($this->parent_obj, "editProvider");
        $link_delete = $this->memberlist_link = $this->gCtrl->getLinkTarget($this->parent_obj, "deleteConfirmProvider");
        $this->gCtrl->clearParameters($this->parent_obj);

        $items = array();
        $items[] = array("title" => $this->txt("edit_provider"), "link" => $link_edit, "image" => "", "frame" => "");
        $items[] = array("title" => $this->txt("delete_provider"), "link" => $link_delete, "image" => "", "frame" => "");

        return $items;
    }

    protected function getTagsHtml(array $tags)
    {
        $ret = array();
        foreach ($tags as $tag) {
            $color = $this->df->color('#' . $tag[1]);
            $result_tag = $this->g_f->button()->tag($tag[0], "#")->withBackgroundColor($color);
            $ret[] = $this->g_renderer->render($result_tag);
        }
        return implode(" ", $ret);
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
}
