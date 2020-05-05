<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\ScaledFeedback\Config\Sets;
use CaT\Plugins\ScaledFeedback\Config\DB;

/**
 * @ilCtrl_Calls ilSetsGUI: ilSetGUI, ilSetSettingsGUI, ilSetDimensionsGUI, ilSetTextGUI
 */
class ilSetsGUI extends TMSTableParentGUI
{
    const CMD_SHOW_SETS = "showSets";
    const CMD_DELETE_SETS = "deleteSets";
    const CMD_EDIT_SET = "editSet";
    const CMD_CONFIRM_DELETE = "confirmDelete";
    const CMD_CANCEL = "cancel";

    const TABLE_ID = "sets";

    const POST_VAR = "row_selector";

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
     * @var ilSetGUI
     */
    protected $gui;

    /**
     * @var Closure
     */
    protected $txt;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        ilTabsGUI $tabs,
        ilToolbarGUI $toolbar,
        DB $db,
        string $plugin_path,
        ilSetGUI $gui,
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
        $cmd = $this->ctrl->getCMD(self::CMD_SHOW_SETS);
        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
            case "ilsetgui":
                $this->addSet();
                break;
            default:
                switch ($cmd) {
                    case self::CMD_CANCEL:
                        $this->showSets();
                        break;
                    case self::CMD_SHOW_SETS:
                    case self::CMD_DELETE_SETS:
                    case self::CMD_EDIT_SET:
                    case self::CMD_CONFIRM_DELETE:
                        $this->$cmd();
                        break;
                    default:
                        throw new Exception(__METHOD__ . " unknown command: " . $cmd);
                }
        }
    }

    /**
     * Show sets table.
     *
     * @return void
     */
    protected function showSets()
    {
        $this->setToolbar();
        $sets = $this->db->selectAllSets();
        $process_data = $this->createProcessingArray($sets);
        $this->renderSetsTable($process_data);
    }

    protected function addSet()
    {
        $this->ctrl->forwardCommand($this->gui);
    }

    protected function editSet()
    {
        $this->ctrl->forwardCommand($this->gui);
    }

    protected function deleteSets()
    {
        $this->db->deleteSets($_POST['delete']);
        ilUtil::sendSuccess($this->txt('entries_delete'), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_SETS);
    }

    protected function confirmDelete()
    {
        $post = $_POST;

        if (!isset($post['row_selector'])) {
            ilUtil::sendInfo($this->txt('no_entries_delete'), true);
            $this->ctrl->redirect($this, self::CMD_SHOW_SETS);
        }

        foreach ($post['row_selector'] as $id) {
            $sets[] = $this->db->selectSetById((int) $id);
        }

        $used_sets = array_filter($sets, function (Sets\Set $s) {
            return $s->getIsUsed() === true;
        });

        $sets = array_filter($sets, function (Sets\Set $s) {
            return $s->getIsUsed() === false;
        });

        if (!empty($used_sets)) {
            ilUtil::sendInfo(
                $this->txt("notdeleteable") . "<br>" .
                implode("<br>", array_map(function (Sets\Set $s) {
                    return $s->getTitle();
                }, $used_sets)),
                true
                );
            if (empty($sets)) {
                $this->ctrl->redirect($this, self::CMD_SHOW_SETS);
            }
        }
        require_once "Services/Utilities/classes/class.ilConfirmationGUI.php";
        $confirmation = new ilConfirmationGUI();

        foreach ($sets as $set) {
            $confirmation->addItem("delete[]", $set->getSetId(), $set->getTitle());
        }

        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $confirmation->setHeaderText($this->txt("delete_confirmation"));
        $confirmation->setConfirm($this->txt("confirm"), self::CMD_DELETE_SETS);
        $confirmation->setCancel($this->txt("cancel"), self::CMD_CANCEL);
        $this->tpl->setContent($confirmation->getHTML());
    }

    /**
     * @param 	Sets\Set[]
     */
    protected function renderSetsTable(array $sets)
    {
        $table = $this->getTMSTableGUI();
        $table->setData($sets);
        $table->addMultiCommand(self::CMD_CONFIRM_DELETE, $this->txt("delete"));

        $this->tpl->setContent($table->getHtml());
    }

    /**
     * @param Sets\Set[]
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
        $this->toolbar->setFormAction($this->ctrl->getFormActionByClass(array(ilSetGUI::class, ilSetSettingsGUI::class), "addSet"));
        $this->toolbar->setCloseFormTag(true);
        $this->toolbar->addFormButton($this->txt("add_entry"), "addSet");
    }

    protected function getTMSTableGUI() : ilTMSTableGUI
    {
        $table = parent::getTMSTableGUI();

        $table->setEnableTitle(true);
        $table->setTitle($this->txt("sets"));

        $table->setTopCommands(false);
        $table->setEnableHeader(true);
        $table->setRowTemplate("tpl.table_dimension_row.html", $this->plugin_path);
        $table->setFormAction($this->ctrl->getFormAction($this));
        $table->setExternalSorting(true);
        $table->setEnableAllCommand(true);
        $table->setShowRowsSelector(false);
        $table->setSelectAllCheckbox(self::POST_VAR . "[]");

        $table->addColumn("", "", "1", true);
        $table->addColumn($this->txt("title"));
        $table->addColumn($this->txt("status"));
        $table->addColumn($this->txt("used"));
        $table->addColumn($this->txt("actions"));

        return $table;
    }

    protected function fillRow()
    {
        return function (ilTMSTableGUI $table, array $set) {
            $tpl = $table->getTemplate();

            $object = $set["object"];
            $is_used = "no";
            $is_locked = "unlocked";

            if ($object->getIsLocked()) {
                $is_locked = "locked";
            }
            if ($object->getIsUsed()) {
                $is_used = "yes";
            }

            $tpl->setVariable("POST_VAR", self::POST_VAR);
            $tpl->setVariable("ID", $object->getSetId());
            $tpl->setVariable("TITLE", $object->getTitle());
            $tpl->setVariable("STATUS", $this->txt($is_locked));
            $tpl->setVariable("USED", $this->txt($is_used));
            $tpl->setVariable("ACTIONS", $this->getActionMenu($object->getSetId()));
        };
    }

    protected function getActionMenu(int $id) : string
    {
        require_once "Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php";
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

    protected function getActionMenuItems(int $id) : array
    {
        $this->ctrl->setParameterByClass("ilSetsGUI", "id", $id);
        $link_edit = $this->ctrl->getLinkTargetByClass(array("ilSetGUI", "ilSetSettingsGUI"), "editSet");
        $this->ctrl->clearParametersByClass("ilSetsGUI");

        $items = array();
        $items[] = array("title" => $this->txt("edit"), "link" => $link_edit, "image" => "", "frame" => "");

        return $items;
    }

    protected function tableCommand()
    {
        return self::CMD_SHOW_SETS;
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
