<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/TMS/WBD/Cases/ilCasesDB.php";

use CaT\WBD\Cases\CancelParticipation;
use \CaT\Plugins\WBDManagement\Config\UserDefinedFields\WBDManagementUDFStorage;

class ilNotYetCancelledGUI extends TMSTableParentGUI
{
    const CMD_SHOW_TABLE = "showTable";

    const TABLE_ID = "notYetReported";

    const CLMN_CRS_ID = "crs_id";
    const CLMN_USER_ID = "user_id";
    const CLMN_WBD_ID = "tbl_wbd_id";
    const CLMN_CRS_TITLE = "crs_title";
    const CLMN_IDD_TIME = "idd_time";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var ilCasesDB
     */
    protected $cases_db;

    /**
     * @var WBDManagementUDFStorage
     */
    protected $storage;

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var string
     */
    protected $plugin_path;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        ilCasesDB $cases_db,
        WBDManagementUDFStorage $storage,
        Closure $txt,
        string $plugin_path
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->cases_db = $cases_db;
        $this->storage = $storage;
        $this->txt = $txt;
        $this->plugin_path = $plugin_path;
    }

    /**
     * @throws Exception
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_SHOW_TABLE:
                $this->showTable();
                break;
            default:
                throw new Exception("Unknown command " . $cmd);
        }
    }

    protected function showTable()
    {
        $rp = $this->cases_db->getParticipationsToCancel();
        $table = $this->getTMSTableGUI();
        $table->setData($rp);
        $this->tpl->setContent($table->getHTML());
    }

    protected function getTMSTableGUI() : ilTMSTableGUI
    {
        $table = parent::getTMSTableGUI();

        $table->setEnableTitle(true);
        $table->setTitle($this->txt("not_yet_cancelled"));

        $table->setTopCommands(false);
        $table->setEnableHeader(true);
        $table->setRowTemplate("tpl.table_not_yet_cancelled.html", $this->plugin_path);
        $table->setFormAction($this->ctrl->getFormAction($this));
        $table->setExternalSorting(true);
        $table->setEnableAllCommand(true);
        $table->setShowRowsSelector(true);

        $table->addColumn($this->txt(self::CLMN_CRS_ID));

        $table->addColumn($this->txt(self::CLMN_USER_ID));
        $table->addColumn($this->txt(self::CLMN_WBD_ID));
        $table->addColumn($this->txt(self::CLMN_CRS_TITLE));
        $table->addColumn($this->txt(self::CLMN_IDD_TIME));

        return $table;
    }

    protected function fillRow()
    {
        return function (ilTMSTableGUI $table, CancelParticipation $rp) {
            $tpl = $table->getTemplate();

            $ref_id = array_shift(ilObject::_getAllReferences($rp->getCrsId()));
            $link = ilLink::_getStaticLink($ref_id, "crs");
            $tpl->setVariable("CRS_ID", $ref_id);
            $tpl->setVariable("USER_ID", $rp->getUsrId());
            $tpl->setVariable("GUTBERATEN_ID", $rp->getGutBeratenId());
            $tpl->setVariable("CRS_TITLE", $rp->getTitle());
            $tpl->setVariable("CRS_LINK", $link);
            $tpl->setVariable("IDD_TIME", $rp->getTimeInMinutes() . " " . $this->txt("minutes"));
        };
    }

    protected function tableCommand()
    {
        return self::CMD_SHOW_TABLE;
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
