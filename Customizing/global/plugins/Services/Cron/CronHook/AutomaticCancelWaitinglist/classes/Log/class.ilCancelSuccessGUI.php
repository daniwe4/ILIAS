<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\AutomaticCancelWaitinglist\Log;

require_once("Services/TMS/Table/TMSTableParentGUI.php");

class ilCancelSuccessGUI extends TMSTableParentGUI
{
    const CMD_VIEW_ENTRIES = "viewEntries";
    const TABLE_ID = "cancel_success";

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
     * @var ilTemplate
     */
    protected $tpl;

    public function __construct(
        string $plugin_path,
        Closure $txt,
        ilCtrl $ctrl,
        Log\DB $db,
        ilTemplate $tpl
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
            default:
                throw new Exception("Unknown command " . $cmd);
        }
    }

    protected function viewEntries()
    {
        $table = $this->getTMSTableGUI();
        $table->setTitle($this->txt("cancel_success"));
        $table->setShowRowsSelector(false);
        $table->setRowTemplate("tpl.table_row.html", $this->plugin_path);
        $table->addColumn($this->txt("crs_ref_id"));
        $table->addColumn($this->txt("crs_title"));
        $table->addColumn($this->txt("date"));
        $table->setData($this->db->getSuccessLogEntries());

        $this->tpl->setContent($table->getHTML());
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
        };
    }

    protected function setValue($table, $value)
    {
        $tpl = $table->getTemplate();
        $tpl->setCurrentBlock("column");
        $tpl->setVariable("VALUE", $value);
        $tpl->parseCurrentBlock();
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
