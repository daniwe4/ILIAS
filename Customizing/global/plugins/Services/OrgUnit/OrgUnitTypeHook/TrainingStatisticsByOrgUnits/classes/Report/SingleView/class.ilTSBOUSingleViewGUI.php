<?php

declare(strict_types=1);

use CaT\Plugins\TrainingStatisticsByOrgUnits\Report\SingleView\Report;
use CaT\Libs\ExcelWrapper\Spout\SpoutWriter;
use ILIAS\TMS\Filter;

class ilTSBOUSingleViewGUI
{
    const CMD_SHOW_SINGLE_VIEW = "show_single_view";

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
    protected $single_view;

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
        Report $single_view,
        int $obj_ref_id,
        int $parent_orgu_ref_id = null
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->spout_writer = $spout_writer;
        $this->single_view = $single_view;
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
            case self::CMD_SHOW_SINGLE_VIEW:
                $this->showSingleView();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    protected function showSingleView()
    {
        $filter_settings = $this->loadFilterSettings();
        $encoded_filter_settigs = $this->encodeFilterParams($filter_settings);

        $filter = $this->configureFilter($this->single_view, $filter_settings);
        $table = $this->configureTable($this->single_view);

        $data = [];
        if (!is_null($this->parent_orgu_ref_id)) {
            $data = $this->single_view->fetchDataForOrgu($this->parent_orgu_ref_id);
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
        $table = new SelectableReportTableGUI($this, self::CMD_SHOW_SINGLE_VIEW);
        $table->setId('xtuo_' . $this->obj_ref_id);
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
        $filter_view = new catFilterFlatViewGUI($this, $filter, $display, self::CMD_SHOW_SINGLE_VIEW);
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
}
