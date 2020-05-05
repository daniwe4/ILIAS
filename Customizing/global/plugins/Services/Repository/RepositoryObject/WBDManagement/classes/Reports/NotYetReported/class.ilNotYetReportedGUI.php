<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/TMS/WBD/Cases/ilCasesDB.php";

use CaT\WBD\Cases\ReportParticipation;
use \CaT\Plugins\WBDManagement\Config\UserDefinedFields\WBDManagementUDFStorage;

class ilNotYetReportedGUI extends TMSTableParentGUI
{
    const CMD_SHOW_TABLE = "showTable";

    const TABLE_ID = "notYetReported";

    const CLMN_CRS_ID = "crs_id";
    const CLMN_USER_ID = "user_id";
    const CLMN_WBD_ID = "tbl_wbd_id";
    const CLMN_CRS_TITLE = "crs_title";
    const CLMN_IDD_TIME = "idd_time";
    const CLMN_BEGIN_DATE = "begin_date";
    const CLMN_END_DATE = "end_date";
    const CLMN_CRS_TYPE = "crs_type";
    const CLMN_CRS_CONTENT = "crs_content";
    const CLMN_INTERNAL_ROW_ID = "internal_row_id";
    const CLMN_CONTACT_TITLE = "contact_title";
    const CLMN_CONTACT_FIRSTNAME = "contact_firstname";
    const CLMN_CONTACT_LASTNAME = "contact_lastname";
    const CLMN_CONTACT_PHONE = "contact_phone";
    const CLMN_CONTACT_EMAIL = "contact_email";

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

    /**
     * @var string
     */
    protected $udf_key_id;

    /**
     * @var string
     */
    protected $udf_key_status;

    /**
     * @var DateTime | null
     */
    protected $start_date;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        ilCasesDB $cases_db,
        WBDManagementUDFStorage $storage,
        Closure $txt,
        string $plugin_path,
        string $udf_key_id,
        string $udf_key_status,
        DateTime $start_date = null
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->cases_db = $cases_db;
        $this->storage = $storage;
        $this->txt = $txt;
        $this->plugin_path = $plugin_path;
        $this->udf_key_id = $udf_key_id;
        $this->udf_key_status = $udf_key_status;
        $this->start_date = $start_date;
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
        $start_date = null;

        try {
            $rp = $this->cases_db->getParticipationsToReport(
                $this->getGutBeratenUdfId(),
                $this->getGutBeratenStatus(),
                $this->start_date
            );
        } catch (\ilDatabaseException $e) {
            $rp = [];
        }

        $table = $this->getTMSTableGUI();
        $table->setData($rp);
        $this->tpl->setContent($table->getHTML());
    }

    protected function getGutBeratenUdfId()
    {
        $udf = $this->storage->read($this->udf_key_id);
        return $udf->getUdfId();
    }

    protected function getGutBeratenStatus()
    {
        $udf = $this->storage->read($this->udf_key_status);
        return $udf->getUdfId();
    }

    protected function getTMSTableGUI() : ilTMSTableGUI
    {
        $table = parent::getTMSTableGUI();

        $table->setEnableTitle(true);
        $table->setTitle($this->txt("not_yet_reported"));

        $table->setTopCommands(false);
        $table->setEnableHeader(true);
        $table->setRowTemplate("tpl.table_not_yet_reported.html", $this->plugin_path);
        $table->setFormAction($this->ctrl->getFormAction($this));
        $table->setExternalSorting(true);
        $table->setEnableAllCommand(true);
        $table->setShowRowsSelector(true);

        $table->addColumn($this->txt(self::CLMN_CRS_ID));

        $table->addColumn($this->txt(self::CLMN_USER_ID));
        $table->addColumn($this->txt(self::CLMN_WBD_ID));
        $table->addColumn($this->txt(self::CLMN_CRS_TITLE));
        $table->addColumn($this->txt(self::CLMN_IDD_TIME));
        $table->addColumn($this->txt(self::CLMN_BEGIN_DATE));
        $table->addColumn($this->txt(self::CLMN_END_DATE));

        foreach ($table->getSelectedColumns() as $key => $column) {
            $table->addColumn($this->txt($column));
        }

        return $table;
    }

    public function getSelectableColumns()
    {
        $clmn = [];

        $clmn[self::CLMN_CRS_TYPE] = ["txt" => $this->txt(self::CLMN_CRS_TYPE)];
        $clmn[self::CLMN_CRS_CONTENT] = ["txt" => $this->txt(self::CLMN_CRS_CONTENT)];
        $clmn[self::CLMN_INTERNAL_ROW_ID] = ["txt" => $this->txt(self::CLMN_INTERNAL_ROW_ID)];
        $clmn[self::CLMN_CONTACT_TITLE] = ["txt" => $this->txt(self::CLMN_CONTACT_TITLE)];
        $clmn[self::CLMN_CONTACT_FIRSTNAME] = ["txt" => $this->txt(self::CLMN_CONTACT_FIRSTNAME)];
        $clmn[self::CLMN_CONTACT_LASTNAME] = ["txt" => $this->txt(self::CLMN_CONTACT_LASTNAME)];
        $clmn[self::CLMN_CONTACT_PHONE] = ["txt" => $this->txt(self::CLMN_CONTACT_PHONE)];
        $clmn[self::CLMN_CONTACT_EMAIL] = ["txt" => $this->txt(self::CLMN_CONTACT_EMAIL)];

        return $clmn;
    }

    protected function fillRow()
    {
        return function (ilTMSTableGUI $table, ReportParticipation $rp) {
            $tpl = $table->getTemplate();

            $ref_id = array_shift(ilObject::_getAllReferences($rp->getCrsId()));
            $link = ilLink::_getStaticLink($ref_id, "crs");
            $tpl->setVariable("CRS_ID", $ref_id);
            $tpl->setVariable("USER_ID", $rp->getUsrId());
            $tpl->setVariable("GUTBERATEN_ID", $rp->getGutBeratenId());
            $tpl->setVariable("CRS_TITLE", $rp->getTitle());
            $tpl->setVariable("CRS_LINK", $link);
            $tpl->setVariable("IDD_TIME", $rp->getTimeInMinutes() . " " . $this->txt("minutes"));
            $tpl->setVariable("BEGIN_DATE", $rp->getStartDate()->format("d.m.Y"));
            $tpl->setVariable("END_DATE", $rp->getEndDate()->format("d.m.Y"));

            foreach ($table->getSelectedColumns() as $key => $column) {
                $tpl->setCurrentBlock($key);
                switch ($key) {
                    case self::CLMN_CRS_TYPE:
                        $data = $rp->getType();
                        break;
                    case self::CLMN_CRS_CONTENT:
                        $data = $rp->getTopic();
                        break;
                    case self::CLMN_INTERNAL_ROW_ID:
                        $data = $rp->getInternalId();
                        break;
                    case self::CLMN_CONTACT_TITLE:
                        $data = $rp->getContactTitle();
                        break;
                    case self::CLMN_CONTACT_FIRSTNAME:
                        $data = $rp->getContactFirstname();
                        break;
                    case self::CLMN_CONTACT_LASTNAME:
                        $data = $rp->getContactLastname();
                        break;
                    case self::CLMN_CONTACT_PHONE:
                        $data = $rp->getContactTelno();
                        break;
                    case self::CLMN_CONTACT_EMAIL:
                        $data = $rp->getContactEmail();
                        break;
                }

                $tpl->setVariable(strtoupper($key), $data);
                $tpl->parseCurrentBlock();
            }
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
