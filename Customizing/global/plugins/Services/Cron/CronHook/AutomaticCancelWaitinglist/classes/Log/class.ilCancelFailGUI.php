<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\AutomaticCancelWaitinglist\Log;

require_once("Services/TMS/Table/TMSTableParentGUI.php");

class ilCancelFailGUI extends TMSTableParentGUI
{
    const CMD_VIEW_ENTRIES = "viewEntries";
    const CMD_RESOLVE_CONFLICT = "resolveConflict";
    const TABLE_ID = "cancel_success";
    const FAIL_ID = "fail_id";

    /**
     * @var string
     */
    protected $plugin_path;

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var Log\DB
     */
    protected $db;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    public function __construct(
        string $plugin_path,
        Closure $txt,
        ilCtrl $ctrl,
        Log\DB $db,
        ilGlobalTemplateInterface $tpl
    ) {
        $this->plugin_path = $plugin_path;
        $this->txt = $txt;
        $this->ctrl = $ctrl;
        $this->db = $db;
        $this->tpl = $tpl;
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_VIEW_ENTRIES:
                $this->viewEntries();
                break;
            case self::CMD_RESOLVE_CONFLICT:
                $this->resolveConflict();
                break;
            default:
                throw new Exception("Unknown command " . $cmd);
        }
    }

    protected function viewEntries()
    {
        $table = $this->getTMSTableGUI();
        $table->setTitle($this->txt("cancel_fail"));
        $table->setShowRowsSelector(false);
        $table->setRowTemplate("tpl.table_row.html", $this->plugin_path);
        $table->addColumn($this->txt("crs_ref_id"));
        $table->addColumn($this->txt("crs_title"));
        $table->addColumn($this->txt("date"));
        $table->addColumn($this->txt("message"));
        $table->addColumn($this->txt("actions"));
        $table->setData($this->db->getFailedLogEntries());

        $this->tpl->setContent($table->getHTML());
    }

    protected function resolveConflict()
    {
        $get = $_GET;

        if (!isset($get[self::FAIL_ID]) || is_null($get[self::FAIL_ID]) || $get[self::FAIL_ID] == "") {
            ilUtil::sendFailure($this->txt("no_id_found"), true);
        } else {
            $this->db->resolveConflictFor((int) $get[self::FAIL_ID]);
            ilUtil::sendSuccess($this->txt("conflict_resolved"), true);
        }

        $this->ctrl->redirect($this, self::CMD_VIEW_ENTRIES);
    }

    /**
     * @inheritdoc
     */
    protected function fillRow()
    {
        return function ($table, $entry) {
            $this->setValue($table, $entry->getCrsRefId());
            $this->setValue($table, \ilObject::_lookupTitle(\ilObject::_lookupObjectId($entry->getCrsRefId())));
            $this->setValue($table, $entry->getDate()->format("d.m.Y"));
            $this->setValue($table, $entry->getMessage());
            $this->setValue($table, $this->getActionMenu($entry->getId()));
        };
    }

    protected function setValue($table, $value)
    {
        $tpl = $table->getTemplate();
        $tpl->setCurrentBlock("column");
        $tpl->setVariable("VALUE", $value);
        $tpl->parseCurrentBlock();
    }

    protected function getActionMenu(int $id) : string
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

        foreach ($this->getActionMenuItems($id, $is_blank) as $key => $value) {
            $current_selection_list->addItem($value["title"], "", $value["link"], $value["image"], "", $value["frame"]);
        }

        return $current_selection_list->getHTML();
    }

    /**
     * Get items for action menu
     */
    protected function getActionMenuItems(int $id) : array
    {
        $this->ctrl->setParameter($this, self::FAIL_ID, $id);

        $resolve = $this->ctrl->getLinkTarget($this, self::CMD_RESOLVE_CONFLICT);
        $this->ctrl->clearParameters($this);

        $items = array();
        $items[] = array(
            "title" => $this->txt("resolve"),
            "link" => $resolve,
            "image" => "",
            "frame" => ""
        );

        return $items;
    }

    /**
     * @inheritdoc
     */
    protected function tableCommand()
    {
        return self::CMD_VIEW_ENTRIES;
    }

    /**
     * @inheritdoc
     */
    protected function tableId()
    {
        return self::TABLE_ID;
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}
