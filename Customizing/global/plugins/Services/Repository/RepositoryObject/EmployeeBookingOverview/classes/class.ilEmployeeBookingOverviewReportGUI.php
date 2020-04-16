<?php

declare(strict_types=1);

require_once 'Services/TMS/TableRelations/classes/class.SelectableReportTableGUI.php';

use ILIAS\TMS\Filter\DisplayFilter;
use CaT\Plugins\EmployeeBookingOverview\Report;

class ilEmployeeBookingOverviewReportGUI
{
    const CMD_VIEW = 'report';
    const CMD_USERS_QUERY = 'users_query';
    const CMD_EXPORT_XLSX = 'export_xlsx';

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var FilterViewFactory
     */
    protected $fvf;

    /**
     * @var ExportFactory
     */
    protected $ef;

    /**
     * @var ReportTableFactory
     */
    protected $rtf;

    /**
     * @var AccessChecker
     */
    protected $access_checker;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var DisplayFilter
     */
    protected $display;

    /**
     * @var Report
     */
    protected $report;

    /**
     * @var int
     */
    protected $obj_ref_id;

    /**
     * @var array
     */
    protected $encoded_filter_settigs;


    protected $filter_settings;

    /**
     * @var array
     */
    protected $relevant_parameters;

    public function __construct(
        ilCtrl $ctrl,
        ilTemplate $tpl,
        FilterViewFactory $fvf,
        ExportFactory $ef,
        ReportTableFactory $rtf,
        AccessChecker $access_checker,
        DisplayFilter $display,
        Report $report,
        ilEmployeeBookingOverviewUserAutoComplete $user_autocomplete,
        int $obj_ref_id
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->access_checker = $access_checker;
        $this->fvf = $fvf;
        $this->ef = $ef;
        $this->rtf = $rtf;
        $this->display = $display;

        $this->report = $report->setUserQueryLink(
            $ctrl->getLinkTarget(
                $this,
                self::CMD_USERS_QUERY,
                "",
                true
            )
        );
        $this->user_autocomplete = $user_autocomplete;
        $this->obj_ref_id = $obj_ref_id;
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd(self::CMD_VIEW);

        switch ($cmd) {
            case self::CMD_VIEW:
                if ($this->access_checker->canRead()) {
                    $this->renderReport($cmd);
                }
                break;
            case self::CMD_USERS_QUERY:
                if ($this->access_checker->canRead()) {
                    $this->usersQuery();
                }
                break;
            case self::CMD_EXPORT_XLSX:
                break;
        }
    }

    protected function usersQuery()
    {
        $auto = $this->user_autocomplete->withVisibleUsers($this->report->visibleUsers());
        $auto->setSearchFields(array('login','firstname','lastname','email', 'second_email'));
        $auto->enableFieldSearchableCheck(false);
        $auto->setMoreLinkAvailable(true);

        if (($_REQUEST['fetchall'])) {
            $auto->setLimit(ilEmployeeBookingOverviewUserAutoComplete::MAX_ENTRIES);
        }
        echo $auto->getList($_REQUEST['term']);
        exit();
    }

    protected function renderReport(string $cmd)
    {
        $filter = $this->configureFilter($cmd);
        $this->enableRelevantParametersCtrl();
        $table = $this->configureTable();
        $table->setData($this->report->fetchData());

        $this->tpl->setContent(
            $filter->render($this->filter_settings) .
            $table->getHTML()
        );
        $this->disableRelevantParametersCtrl();
    }

    protected function configureFilter(string $cmd)
    {
        $filter = $this->report->filter();
        $this->filter_settings = $this->loadFilterSettings();

        $this->encoded_filter_settigs = $this->encodeFilterParams($this->filter_settings);
        $this->addRelevantParameter('filter_params', $this->encoded_filter_settigs);

        $this->report->applyFilterToSpace($this->display->buildFilterValues($filter, $this->filter_settings));

        $filter_view = $this->fvf->getFlatviewGUI($this, $filter, $this->display, $cmd);
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

    protected function configureTable()
    {
        $table = $this->rtf->getSelectableReportTable(
            $this,
            self::CMD_VIEW,
            'xebo_' . $this->obj_ref_id
        );
        $this->report->configureTable($table);
        $table->addExporter(
            $this->ef->getSpoutWriter(),
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
    final protected function enableRelevantParametersCtrl()
    {
        foreach ($this->relevant_parameters as $get_parameter => $get_value) {
            $this->ctrl->setParameter($this, $get_parameter, $get_value);
        }
    }

    final protected function disableRelevantParametersCtrl()
    {
        foreach ($this->relevant_parameters as $get_parameter => $get_value) {
            $this->ctrl->setParameter($this, $get_parameter, null);
        }
    }

    protected function addRelevantParameter($key, $value)
    {
        $this->relevant_parameters[$key] = $value;
    }
}
