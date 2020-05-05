<?php declare(strict_types = 1);

use CaT\Plugins\CancellationFeeReport as CFR;
use ILIAS\TMS\Filter as Filters;
use CaT\Libs\ExcelWrapper\Spout\SpoutWriter as SpoutWriter;

class ilCancellationFeeReportGUI
{
    const CMD_VIEW = 'report';
    const CMD_EXPORT_XLSX = 'export_xlsx';

    /**
     * @var	TrainingDemandAdvanced\Report
     */
    protected $report;

    protected $plugin;

    protected $ctrl;
    protected $access;
    protected $user;
    protected $tpl;

    protected $obj;

    protected $relevant_parameters = [];

    public function __construct(
        CFR\Report $report,
        ilCancellationFeeReportPlugin $plugin,
        ilCtrl $ctrl,
        ilAccess $access,
        ilGlobalTemplateInterface $tpl,
        ilObjUser $user
    ) {
        $this->report = $report;
        $this->plugin = $plugin;
        $this->ctrl = $ctrl;
        $this->access = $access;
        $this->tpl = $tpl;
        $this->user = $user;
    }

    public function withObject(ilObjCancellationFeeReport $obj)
    {
        $this->obj = $obj;
        $this->report->withObject($obj);
        return $this;
    }

    public function executeCommand()
    {
        if (!$this->obj) {
            throw new \LogicException('no object set');
        }
        if ($this->access->checkAccess("read", "", $this->obj->getRefId())
            || $this->access->checkAccess("write", "", $this->obj->getRefId())
        ) {
            $this->cmd = $this->ctrl->getCmd(self::CMD_VIEW);
            switch ($this->cmd) {
                case self::CMD_VIEW:
                    $this->renderReport();
                    break;
                case self::CMD_EXPORT_XLSX:
                    break;
            }
        }
    }

    public function renderReport()
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

    protected function configureFilter(CFR\Report $report) : catFilterFlatViewGUI
    {
        $filter = $report->filter();
        $this->filter_settings = $this->loadFilterSettings();

        $display = new Filters\DisplayFilter(new Filters\FilterGUIFactory(), new Filters\TypeFactory());
        $this->ecoded_filter_settigs = $this->encodeFilterParams($this->filter_settings);
        $this->addRelevantParameter('filter_params', $this->ecoded_filter_settigs);

        $report->applyFilterToSpace($display->buildFilterValues($filter, $this->filter_settings));

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

    protected function configureTable(CFR\Report $report)
    {
        if (!$this->obj) {
            throw new \LogicException('no object set');
        }
        $table = new SelectableReportTableGUI($this, self::CMD_VIEW);
        $table->setId('xcfr_' . $this->obj->getRefId());
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
}
