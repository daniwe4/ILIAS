<?php

require_once 'Services/TMS/TableRelations/classes/class.SelectableReportTableGUI.php';

use ILIAS\TMS\TableRelations as TableRelations;
use ILIAS\TMS\Filter as Filters;
use CaT\Plugins\TrainingStatistics as TrainingStatistics;
use CaT\Libs\ExcelWrapper\Spout\SpoutWriter as SpoutWriter;

class ilTrainingStatisticsReportGUI
{
    const CMD_VIEW = 'report';
    const CMD_EXPORT_XLSX = 'export_xlsx';

    /**
     * @var int
     */
    protected $obj_ref_id;

    /**
     * @var	TrainingStatistics\Report
     */
    protected $report;

    protected $plugin;

    protected $g_ctrl;
    protected $g_access;
    protected $g_tpl;

    public function __construct(
        $a_parent_gui,
        $plugin,
        $obj_ref_id,
        TrainingStatistics\Report $report
    ) {
        assert('is_int($obj_ref_id)');

        $this->obj_ref_id = $obj_ref_id;
        $this->report = $report;

        $this->plugin = $plugin;

        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_access = $DIC->access();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_user = $DIC->user();

        $this->gf = new TableRelations\GraphFactory();
        $this->pf = new Filters\PredicateFactory();
        $this->tf = new TableRelations\TableFactory($this->pf, $this->gf);
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd(self::CMD_VIEW);
        $this->cmd = $cmd;
        switch ($cmd) {
            case self::CMD_VIEW:
                if ($this->g_access->checkAccess("read", "", $this->obj_ref_id)) {
                    $this->renderReport();
                }
                break;
            case self::CMD_EXPORT_XLSX:
                if ($this->g_access->checkAccess("read", "", $this->obj_ref_id)) {
                }
                break;
        }
    }

    public function renderReport()
    {
        $filter = $this->configureFilter($this->report);
        $this->enableRelevantParametersCtrl();
        $overview_table = $this->configureOverviewTable($this->report);
        $table = $this->configureTable($this->report);
        $overview_table->setData($this->report->fetchOverviewData());
        $table->setData($this->report->fetchData());

        $this->g_tpl->setContent(
            $filter->render($this->filter_settings)
            . $table->getHTML()
            . $overview_table->getHTML()
        );
        $this->disableRelevantParametersCtrl();
    }

    protected function configureFilter(TrainingStatistics\Report $report)
    {
        $filter = $report->filter();
        $this->filter_settings = $this->loadFilterSettings();

        $display = new Filters\DisplayFilter(new Filters\FilterGUIFactory(), new Filters\TypeFactory());
        $this->ecoded_filter_settigs = $this->encodeFilterParams($this->filter_settings);
        $this->addRelevantParameter('filter_params', $this->ecoded_filter_settigs);

        $report->applyFilterToSpace($display->buildFilterValues($filter, $this->filter_settings));

        require_once("Services/TMS/Filter/classes/class.catFilterFlatViewGUI.php");
        $filter_view = new catFilterFlatViewGUI($this, $filter, $display, $this->cmd);
        return $filter_view;
    }

    protected function loadFilterSettings()
    {
        if (isset($_POST['filter'])) {
            return $_POST['filter'];
        } elseif (isset($_GET['filter_params'])) {
            return $this->decodeFilterParams($_GET['filter_params']);
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

    protected function configureTable(TrainingStatistics\Report $report)
    {
        $table = new SelectableReportTableGUI($this, self::CMD_VIEW);
        $table->setId('xtdr_' . $this->obj_ref_id);
        $report->configureTable($table);
        $table->addExporter(
            new SpoutWriter(),
            1,
            '.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xlsx'
        );
        return $table;
    }


    protected function configureOverviewTable(TrainingStatistics\Report $report)
    {
        $table = new SelectableReportTableGUI($this, self::CMD_VIEW);
        $table->setId('xtdr_overview_' . $this->obj_ref_id);
        $report->configureOverviewTable($table);
        $table->addExporter(
            new SpoutWriter(),
            1,
            '.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xlsx'
        );
        return $table;
    }


    /**
    * housekeeping the get parameters passed to ctrl
    */
    final public function enableRelevantParametersCtrl()
    {
        foreach ($this->relevant_parameters as $get_parameter => $get_value) {
            $this->g_ctrl->setParameter($this, $get_parameter, $get_value);
        }
    }

    final public function disableRelevantParametersCtrl()
    {
        foreach ($this->relevant_parameters as $get_parameter => $get_value) {
            $this->g_ctrl->setParameter($this, $get_parameter, null);
        }
    }

    public function addRelevantParameter($key, $value)
    {
        $this->relevant_parameters[$key] = $value;
    }
}
