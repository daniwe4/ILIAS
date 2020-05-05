<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types = 1);

use CaT\Plugins\WBDManagement\Reports\ErrorReport\Report;
use CaT\Plugins\WBDManagement\Reports\ErrorReport\Entry;
use ILIAS\TMS\TableRelations;
use ILIAS\TMS\Filter;
use CaT\Libs\ExcelWrapper\Spout\SpoutWriter;
use CaT\Plugins\WBDManagement\Reports\ErrorReport\DB;

class ilWBDReportGUI
{
    const CMD_VIEW = "renderReport";
    const CMD_SET_RESOLVED = "setResolved";
    const CMD_SET_NOT_RESOLVABLE = "setNotResolvable";
    const CMD_CONFIRM_STATUS_RESOLVED = "confirmStatusResolved";
    const CMD_CONFIRM_STATUS_NOT_RESOLVABLE = "confirmStatusNotResolvable";
    const CMD_CONFIRM_STATUS_RESOLVED_MULTI = "confirmStatusResolvedMulti";
    const CMD_CONFIRM_STATUS_NOT_RESOLVABLE_MULTI = "confirmStatusNotResolvableMulti";

    const P_IDS_TO_HANDLE = "id_to_handle";
    const P_MULTI_IDS = "multi_ids";

    /**
     * @var int
     */
    protected $obj_ref_id;

    /**
     * @var Report
     */
    protected $report;

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var TableRelations\TableFactory
     */
    protected $tf;

    /**
     * @var DB
     */
    protected $db;

    /**
     * @var array
     */
    protected $relevant_parameters;

    /**
     * @var array
     */
    protected $filter_settings;

    /**
     * @var array
     */
    protected $encoded_filter_settigs;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        Report $report,
        DB $db,
        Closure $txt
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->report = $report;
        $this->db = $db;
        $this->txt = $txt;

        $this->relevant_parameter = [];
    }

    /**
     * @throws Exception
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_VIEW:
                $this->renderReport();
                break;
            case self::CMD_SET_RESOLVED:
                $this->setResolved();
                break;
            case self::CMD_SET_NOT_RESOLVABLE:
                $this->setNotResolvable();
                break;
            case self::CMD_CONFIRM_STATUS_RESOLVED:
                $this->confirmStatusResolved();
                break;
            case self::CMD_CONFIRM_STATUS_NOT_RESOLVABLE:
                $this->confirmStatusNotResolvable();
                break;
            case self::CMD_CONFIRM_STATUS_RESOLVED_MULTI:
                $this->confirmStatusResolvedMulti();
                break;
            case self::CMD_CONFIRM_STATUS_NOT_RESOLVABLE_MULTI:
                $this->confirmStatusNotResolvableMulti();
                break;
            default:
                new Exception("Unknown command: " . $cmd);
        }
    }

    /**
     * @throws Exception
     */
    protected function renderReport()
    {
        $filter = $this->configureFilter($this->report);
        $this->enableRelevantParametersCtrl();
        $table = $this->configureTable($this->report);
        $table->setData($this->report->fetchData());

        $this->tpl->setContent(
            $filter->render($this->filter_settings)
            . $table->getHTML()
        );
        $this->disableRelevantParametersCtrl();
    }

    protected function confirmStatusResolved()
    {
        $get = $_GET;
        if (!array_key_exists(self::P_IDS_TO_HANDLE, $get)
            || $get[self::P_IDS_TO_HANDLE] == ""
            || !is_numeric($get[self::P_IDS_TO_HANDLE])
        ) {
            ilUtil::sendInfo($this->txt("nothing_selected"), true);
            $this->ctrl->redirect($this, self::CMD_VIEW);
        }

        $this->showConfirm(array($get[self::P_IDS_TO_HANDLE]), Entry::STATUS_RESOLVED, self::CMD_SET_RESOLVED);
    }

    protected function confirmStatusResolvedMulti()
    {
        $post = $_POST;
        if (count($post[self::P_MULTI_IDS]) == 0) {
            ilUtil::sendInfo($this->txt("nothing_selected"), true);
            $this->ctrl->redirect($this, self::CMD_VIEW);
        }

        $this->showConfirm($post[self::P_MULTI_IDS], Entry::STATUS_RESOLVED, self::CMD_SET_RESOLVED);
    }

    protected function confirmStatusNotResolvable()
    {
        $get = $_GET;
        if (!array_key_exists(self::P_IDS_TO_HANDLE, $get)
            || $get[self::P_IDS_TO_HANDLE] == ""
            || !is_numeric($get[self::P_IDS_TO_HANDLE])
        ) {
            ilUtil::sendInfo($this->txt("nothing_selected"), true);
            $this->ctrl->redirect($this, self::CMD_VIEW);
        }

        $this->showConfirm(array($get[self::P_IDS_TO_HANDLE]), Entry::STATUS_NOT_RESOLVABLE, self::CMD_SET_NOT_RESOLVABLE);
    }

    protected function confirmStatusNotResolvableMulti()
    {
        $post = $_POST;
        if (count($post[self::P_MULTI_IDS]) == 0) {
            ilUtil::sendInfo($this->txt("nothing_selected"), true);
            $this->ctrl->redirect($this, self::CMD_VIEW);
        }

        $this->showConfirm($post[self::P_MULTI_IDS], Entry::STATUS_NOT_RESOLVABLE, self::CMD_SET_NOT_RESOLVABLE);
    }

    protected function setResolved()
    {
        $post = $_POST;
        foreach ($post[self::P_IDS_TO_HANDLE] as $id) {
            $this->db->setStatusToResolved((int) $id);
        }

        ilUtil::sendSuccess($this->txt("successful_resolved"), true);
        $this->ctrl->redirect($this, self::CMD_VIEW);
    }

    protected function setNotResolvable()
    {
        $post = $_POST;
        foreach ($post[self::P_IDS_TO_HANDLE] as $id) {
            $this->db->setStatusToNotResolvable((int) $id);
        }

        ilUtil::sendSuccess($this->txt("successful_not_resolvable"), true);
        $this->ctrl->redirect($this, self::CMD_VIEW);
    }

    protected function showConfirm(array $ids, $event, $cmd)
    {
        $error_infos = $this->db->getErrorInfosFor($ids);

        require_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $confirmation->setHeaderText(sprintf($this->txt("confirm_header"), $this->txt($event)));

        foreach ($error_infos as $error_info) {
            $text = sprintf(
                $this->txt("confirm_info_text"),
                $this->placeholderOrValue($error_info->getFirstname()),
                $this->placeholderOrValue($error_info->getLastname()),
                $error_info->getCrsTitle(),
                $error_info->getRequestDate()->format("d.m.Y H:i:s"),
                $error_info->getMessage()
            );
            $confirmation->addItem(self::P_IDS_TO_HANDLE . "[]", $error_info->getId(), $text);
        }
        $confirmation->setConfirm($this->txt("set_status"), $cmd);
        $confirmation->setCancel($this->txt("cancel"), self::CMD_VIEW);
        $this->tpl->setContent($confirmation->getHTML());
    }

    protected function placeholderOrValue($value) : string
    {
        if (is_null($value)) {
            return "-";
        }

        return $value;
    }

    /**
     * @param Report $report
     * @return catFilterFlatViewGUI
     * @throws Exception
     */
    protected function configureFilter(Report $report)
    {
        $filter = $report->filter();
        $this->filter_settings = $this->loadFilterSettings();

        $display = new Filter\DisplayFilter(new Filter\FilterGUIFactory(), new Filter\TypeFactory());
        $this->encoded_filter_settigs = $this->encodeFilterParams($this->filter_settings);
        $this->addRelevantParameter("filter_params", $this->encoded_filter_settigs);

        $report->applyFilterToSpace($display->buildFilterValues($filter, $this->filter_settings));

        require_once("Services/TMS/Filter/classes/class.catFilterFlatViewGUI.php");
        $filter_view = new catFilterFlatViewGUI($this, $filter, $display, self::CMD_VIEW);
        return $filter_view;
    }

    protected function loadFilterSettings()
    {
        if (isset($_POST["filter"])) {
            return $_POST["filter"];
        } elseif (isset($_GET["filter_params"])) {
            return $this->decodeFilterParams($_GET["filter_params"]);
        }
        return [];
    }

    protected function encodeFilterParams(array $filter_params)
    {
        return base64_encode(json_encode($filter_params));
    }

    protected function decodeFilterParams($encoded_filter)
    {
        return json_decode(base64_decode($encoded_filter), true);
    }

    protected function configureTable(Report $report)
    {
        $table = new SelectableReportTableGUI($this, self::CMD_VIEW);
        $table->setId("xtda_" . $this->obj_ref_id);
        $table->setSelectAllCheckbox("multi");
        $table->addMultiCommand(self::CMD_CONFIRM_STATUS_RESOLVED_MULTI, $this->txt(Entry::STATUS_RESOLVED));
        $table->addMultiCommand(self::CMD_CONFIRM_STATUS_NOT_RESOLVABLE_MULTI, $this->txt(Entry::STATUS_NOT_RESOLVABLE));

        $report->configureTable($table);

        $table->addExporter(
            new SpoutWriter(),
            1,
            ".xlsx",
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "xlsx"
        );
        return $table;
    }

    /**
     * housekeeping the get parameters passed to ctrl
     */
    final public function enableRelevantParametersCtrl()
    {
        foreach ($this->relevant_parameters as $get_parameter => $get_value) {
            $this->ctrl->setParameter($this, $get_parameter, $get_value);
        }
    }

    final public function disableRelevantParametersCtrl()
    {
        foreach ($this->relevant_parameters as $get_parameter => $get_value) {
            $this->ctrl->setParameter($this, $get_parameter, null);
        }
    }

    public function addRelevantParameter($key, $value)
    {
        $this->relevant_parameters[$key] = $value;
    }

    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}
