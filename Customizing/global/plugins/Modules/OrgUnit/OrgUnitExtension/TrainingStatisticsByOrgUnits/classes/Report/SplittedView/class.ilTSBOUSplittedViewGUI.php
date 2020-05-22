<?php

declare(strict_types=1);

use CaT\Plugins\TrainingStatisticsByOrgUnits\Report\SplittedView\Report;
use CaT\Libs\ExcelWrapper\Spout\SpoutWriter;
use ILIAS\TMS\Filter;

class ilTSBOUSplittedViewGUI
{
    const CMD_SHOW_SPLITTED_VIEW = "showSplittedView";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var Report
     */
    protected $splitted_view;

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var SpoutWriter
     */
    protected $spout_writer;

    /**
     * @var int
     */
    protected $obj_ref_id;

    /**
     * @var int | null
     */
    protected $parent_orgu_ref_id;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        SpoutWriter $spout_writer,
        Report $splitted_view,
        Closure $txt,
        int $obj_ref_id,
        int $parent_orgu_ref_id = null
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->spout_writer = $spout_writer;
        $this->splitted_view = $splitted_view;
        $this->txt = $txt;
        $this->obj_ref_id = $obj_ref_id;
        $this->parent_orgu_ref_id = $parent_orgu_ref_id;
    }

    /**
     * @throws Exception if cmd is unknown
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_SHOW_SPLITTED_VIEW:
                $this->showSplittedView();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    protected function showSplittedView()
    {
        $filter_settings = $this->loadFilterSettings();
        $encoded_filter_settigs = $this->encodeFilterParams($filter_settings);

        $filter = $this->configureFilter($this->splitted_view, $filter_settings);
        $table = $this->configureTable($this->splitted_view);

        $data = [];
        if (!is_null($this->parent_orgu_ref_id)) {
            $data = $this->splitted_view->fetchDataForOrgu(
                (string) $_GET['elpt__xpt'] !== '1'
            );
        }
        $table->setData($data);

        $this->ctrl->setParameter($this, 'filter_params', $encoded_filter_settigs);
        $this->tpl->setContent(
            $filter->render($filter_settings)
            . $table->getHTML()
        );
        $this->ctrl->setParameter($this, 'filter_params', null);
    }

    protected function configureTable(Report $report) : SelectableReportTableGUI
    {
        $table = new SelectableReportTableGUI($this, self::CMD_SHOW_SPLITTED_VIEW);
        $table->setId('xtdr_' . $this->obj_ref_id);
        $report->configureTable($table);
        $table->addExporter(
            $this->spout_writer,
            1,
            '.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xlsx'
        );
        return $table;
    }

    /**
     * @param Report $report
     * @param array $filter_settings
     * @return catFilterFlatViewGUI
     * @throws Exception if filter class is not found
     */
    protected function configureFilter(Report $report, array $filter_settings) : catFilterFlatViewGUI
    {
        $filter = $report->filter();
        $display = new Filter\DisplayFilter(new Filter\FilterGUIFactory(), new Filter\TypeFactory());
        $report->applyFilterToSpace($display->buildFilterValues($filter, $filter_settings));
        $filter_view = new catFilterFlatViewGUI($this, $filter, $display, self::CMD_SHOW_SPLITTED_VIEW);
        return $filter_view;
    }

    protected function loadFilterSettings() : array
    {
        if (isset($_POST['filter'])) {
            return $_POST['filter'];
        } elseif (isset($_GET['filter_params'])) {
            return $this->decodeFilterParams($_GET['filter_params']);
        }
        return [];
    }

    protected function encodeFilterParams(array $filter_params) : string
    {
        return base64_encode(json_encode($filter_params));
    }

    protected function decodeFilterParams(string $encoded_filter) : array
    {
        return json_decode(base64_decode($encoded_filter), true);
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}
