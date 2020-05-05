<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use \CaT\Plugins\ScaledFeedback\Config\Dimensions;
use CaT\Plugins\ScaledFeedback\Config\DB;

/**
 * @ilCtrl_Calls ilDimensionsGUI: ilDimensionGUI
 */
class ilDimensionsGUI extends TMSTableParentGUI
{
    const CMD_SHOW_DIMENSIONS = "showDimensions";
    const CMD_ADD_DIMENSION = "addDimension";
    const CMD_DELETE_DIMENSIONS = "deleteDimensions";
    const CMD_EDIT_DIMENSION = "editDimension";
    const CMD_CONFIRM_DELETE = "confirmDelete";
    const CMD_APPLY_FILTER = "applyFilter";
    const CMD_RESET_FILTER = "resetFilter";
    const CMD_CANCEL = "cancel";

    const TABLE_ID = "dimension";

    const POST_VAR = "row_selector";

    const OPTION_ALL = "all";
    const OPTION_UNLOCKED = "unlocked";
    const OPTION_LOCKED = "locked";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @var string
     */
    protected $plugin_path;

    /**
     * @var ilDimensionGUI
     */
    protected $gui;

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var ilTMSTableGUI
     */
    protected $table;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        ilTabsGUI $tabs,
        ilToolbarGUI $toolbar,
        DB $db,
        string $plugin_path,
        ilDimensionGUI $gui,
        Closure $txt
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->tabs = $tabs;
        $this->toolbar = $toolbar;
        $this->db = $db;
        $this->plugin_path = $plugin_path;
        $this->gui = $gui;
        $this->txt = $txt;
    }

    /**
     * @throws Exception
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCMD(self::CMD_SHOW_DIMENSIONS);
        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
            case "ildimensiongui":
                $this->addDimension();
                break;
            default:
                switch ($cmd) {
                    case self::CMD_CANCEL:
                        $this->showDimensions();
                        break;
                    case self::CMD_SHOW_DIMENSIONS:
                    case self::CMD_DELETE_DIMENSIONS:
                    case self::CMD_ADD_DIMENSION:
                    case self::CMD_EDIT_DIMENSION:
                    case self::CMD_CONFIRM_DELETE:
                    case self::CMD_APPLY_FILTER:
                    case self::CMD_RESET_FILTER:
                        $this->$cmd();
                        break;
                    default:
                        throw new Exception("Unknown command: " . $cmd);
                }
        }
    }

    protected function showDimensions()
    {
        $filter = $this->getStatusFilterValue();

        if ($filter) {
            $dimensions = $this->db->selectAllDimensions($filter);
        } else {
            $dimensions = $this->db->selectAllDimensions();
        }

        $this->setToolbar();
        $process_data = $this->createProcessingArray($dimensions);
        $this->renderDimensionsTable($process_data);
    }

    /**
     * @throws ilCtrlException
     */
    protected function addDimension()
    {
        $this->ctrl->forwardCommand($this->gui);
    }

    /**
     * @throws ilCtrlException
     */
    protected function editDimension()
    {
        $this->ctrl->forwardCommand($this->gui);
    }

    protected function deleteDimensions()
    {
        $this->db->deleteDimensions($_POST['delete']);
        ilUtil::sendSuccess($this->txt('entries_delete'), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_DIMENSIONS);
    }

    protected function confirmDelete()
    {
        $post = $_POST;

        if (!isset($post['row_selector'])) {
            ilUtil::sendInfo($this->txt('no_entries_delete'), true);
            $this->ctrl->redirect($this, self::CMD_SHOW_DIMENSIONS);
        }

        $dims = [];
        foreach ($post['row_selector'] as $id) {
            $dims[] = $this->db->selectDimensionById((int) $id);
        }

        $used_dims = array_filter($dims, function (Dimensions\Dimension $d) {
            return $d->getIsUsed() === true;
        });

        $dims = array_filter($dims, function (Dimensions\Dimension $d) {
            return $d->getIsUsed() === false;
        });

        if (!empty($used_dims)) {
            ilUtil::sendInfo(
                $this->txt("notdeleteable") . "<br>" .
                implode("<br>", array_map(function (Dimensions\Dimension $d) {
                    return $d->getTitle();
                }, $used_dims)),
                true
            );
            if (empty($dims)) {
                $this->ctrl->redirect($this, self::CMD_SHOW_DIMENSIONS);
            }
        }
        require_once "Services/Utilities/classes/class.ilConfirmationGUI.php";
        $confirmation = new ilConfirmationGUI();

        foreach ($dims as $dim) {
            $confirmation->addItem("delete[]", $dim->getDimId(), $dim->getTitle());
        }

        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $confirmation->setHeaderText($this->txt("delete_confirmation"));
        $confirmation->setConfirm($this->txt("confirm"), self::CMD_DELETE_DIMENSIONS);
        $confirmation->setCancel($this->txt("cancel"), self::CMD_CANCEL);
        $this->tpl->setContent($confirmation->getHTML());
    }

    protected function applyFilter()
    {
        $table = $this->getTMSTableGUI();
        $table->resetOffset();
        $table->writeFilterToSession();
        $this->ctrl->redirect($this, self::CMD_SHOW_DIMENSIONS);
    }

    protected function resetFilter()
    {
        $table = $this->getTMSTableGUI();
        $table->resetOffset();
        $table->resetFilter();
        $this->ctrl->redirect($this, self::CMD_SHOW_DIMENSIONS);
    }

    /**
     * @param 	Dimensions\Dimension[]
     */
    protected function renderDimensionsTable(array $dimensions)
    {
        $table = $this->getTMSTableGUI();

        $table->setData($dimensions);
        $table->addMultiCommand(self::CMD_CONFIRM_DELETE, $this->txt("delete"));

        $this->tpl->setContent($table->getHtml());
    }

    /**
     * @param Dimensions\Dimension[] | [] $objects
     */
    protected function createProcessingArray(array $objects) : array
    {
        $ret = array();

        foreach ($objects as $object) {
            $ret[] = array("object" => $object, "delete" => false, "message" => array());
        }

        return $ret;
    }

    protected function setToolbar()
    {
        $this->toolbar->setFormAction($this->ctrl->getFormActionByClass(ilDimensionGUI::class, self::CMD_ADD_DIMENSION));
        $this->toolbar->setCloseFormTag(true);
        $this->toolbar->addFormButton($this->txt("add_entry"), self::CMD_ADD_DIMENSION);
    }

    protected function getTMSTableGUI() : ilTMSTableGUI
    {
        $this->table = parent::getTMSTableGUI();

        $this->table->setEnableTitle(true);
        $this->table->setTitle($this->txt("dimensions"));
        $this->table->setTopCommands(false);
        $this->table->setEnableHeader(true);
        $this->table->setRowTemplate("tpl.table_dimension_row.html", $this->plugin_path);
        $this->table->setFormAction($this->ctrl->getFormAction($this));
        $this->table->setExternalSorting(true);
        $this->table->setEnableAllCommand(true);
        $this->table->setShowRowsSelector(false);
        $this->table->setSelectAllCheckbox(self::POST_VAR . "[]");
        $this->initFilter();
        $this->table->setFilterCommand("applyFilter");

        $this->table->addColumn("", "", "1", true);
        $this->table->addColumn($this->txt("title"));
        $this->table->addColumn($this->txt("status"));
        $this->table->addColumn($this->txt("used"));
        $this->table->addColumn($this->txt("actions"));

        return $this->table;
    }

    protected function fillRow()
    {
        return function (ilTMSTableGUI $table, array $set) {
            $tpl = $table->getTemplate();

            $object = $set["object"];
            $is_used = "no";
            $is_locked = "unlocked";

            if ($object->getIsUsed()) {
                $is_used = "yes";
            }

            if ($object->getIsLocked()) {
                $is_locked = "locked";
            }

            $tpl->setVariable("POST_VAR", self::POST_VAR);
            $tpl->setVariable("ID", $object->getDimId());
            $tpl->setVariable("TITLE", $object->getTitle());
            $tpl->setVariable("STATUS", $this->txt($is_locked));
            $tpl->setVariable("USED", $this->txt($is_used));
            $tpl->setVariable("ACTIONS", $this->getActionMenu($object->getDimId()));
        };
    }

    public function initFilter()
    {
        include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
        $options = array(
            self::OPTION_ALL => $this->txt(self::OPTION_ALL),
            self::OPTION_UNLOCKED => $this->txt(self::OPTION_UNLOCKED),
            self::OPTION_LOCKED => $this->txt(self::OPTION_LOCKED),
        );
        $si = new ilSelectInputGUI($this->txt("status"), "filter_status");
        $si->setOptions($options);
        $this->table->addFilterItem($si);
        $si->readFromSession();
    }

    protected function getStatusFilterValue() : string
    {
        $table = $this->getTMSTableGUI();
        if ($table->getFilterItems()[0]->getValue()) {
            return $table->getFilterItems()[0]->getValue();
        }
        return "";
    }

    protected function getActionMenu(int $id) : string
    {
        require_once "Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php";

        $current_selection_list = new ilAdvancedSelectionListGUI();
        $current_selection_list->setAsynch(false);
        $current_selection_list->setAsynchUrl(true);
        $current_selection_list->setListTitle($this->txt("actions"));
        $current_selection_list->setId($id);
        $current_selection_list->setSelectionHeaderClass("small");
        $current_selection_list->setItemLinkClass("xsmall");
        $current_selection_list->setLinksMode("il_ContainerItemCommand2");
        $current_selection_list->setHeaderIcon(ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
        $current_selection_list->setUseImages(false);
        $current_selection_list->setAdditionalToggleElement("id" . $id, "ilContainerListItemOuterHighlight");

        foreach ($this->getActionMenuItems($id) as $key => $value) {
            $current_selection_list->addItem(
                $value["title"],
                "",
                $value["link"],
                $value["image"],
                "",
                $value["frame"]
            );
        }

        return $current_selection_list->getHTML();
    }

    protected function getActionMenuItems(int $id) : array
    {
        $this->ctrl->setParameter($this, "id", $id);
        $link_edit = $this->ctrl->getLinkTarget($this, "editDimension");
        $this->ctrl->clearParameters($this);

        $items = array();
        $items[] = array("title" => $this->txt("edit"), "link" => $link_edit, "image" => "", "frame" => "");

        return $items;
    }

    protected function tableCommand()
    {
        return self::CMD_SHOW_DIMENSIONS;
    }

    protected function tableId()
    {
        return self::TABLE_ID;
    }

    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}
