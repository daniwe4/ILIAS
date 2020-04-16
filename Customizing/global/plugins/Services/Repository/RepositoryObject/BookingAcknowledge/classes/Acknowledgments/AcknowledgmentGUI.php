<?php

declare(strict_types=1);

namespace CaT\Plugins\BookingAcknowledge\Acknowledgments;

use CaT\Plugins\BookingAcknowledge\BookingAcknowledge;
use CaT\Plugins\BookingAcknowledge\Report;
use CaT\Plugins\BookingAcknowledge\Utils\RequestDigester;
use ILIAS\TMS\Filter as Filters;

/**
 * GUI for an overview of booking requests.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class AcknowledgmentGUI
{
    const CMD_SHOW_UPCOMING = 'cmd_upcoming';
    const CMD_SHOW_FINISHED = 'cmd_finished';

    public function __construct(
        \ilCtrl $ctrl,
        \ilTemplate $tpl,
        Report $report,
        \ilObjBookingAcknowledge $object,
        \Closure $txt
    ) {
        $this->g_ctrl = $ctrl;
        $this->g_tpl = $tpl;
        $this->report = $report;
        $this->object = $object;
        $this->txt = $txt;
        $this->plugin_directory = $object->getDirectory();
        $this->digester = new RequestDigester();
    }

    public function executeCommand()
    {
        $this->cmd = $this->g_ctrl->getCmd();
        if (!$this->cmd) {
            $this->cmd = self::CMD_SHOW_UPCOMING;
        }

        if ($this->cmd === RequestDigester::CMD_MULTI_ACTION) {
            $multi_cmd = $_POST["multi_action"];
            if (in_array(
                $multi_cmd,
                [
                    RequestDigester::CMD_ACKNOWLEDGE_CONFIRM,
                    RequestDigester::CMD_DECLINE_CONFIRM
                ]
            )) {
                $this->cmd = $multi_cmd;
            }
        }

        switch ($this->cmd) {
            case self::CMD_SHOW_UPCOMING:
                $this->report = $this->report->withType(Report::TYPE_TO_ACKNOWLEDGE);
                $this->renderReport();
                break;
            case self::CMD_SHOW_FINISHED:
                $this->report = $this->report->withType(Report::TYPE_ACKNOWLEDGED);
                $this->renderReport();
                break;
            case RequestDigester::CMD_ACKNOWLEDGE_CONFIRM:
                $usrcrs = $this->digester->digest();
                $this->confirm(RequestDigester::CMD_ACKNOWLEDGE, $usrcrs);
                break;
            case RequestDigester::CMD_DECLINE_CONFIRM:
                $usrcrs = $this->digester->digest();
                $this->confirm(RequestDigester::CMD_DECLINE, $usrcrs);
                break;
            case RequestDigester::CMD_ACKNOWLEDGE:
                $usrcrs = $this->digester->digest();
                $this->object->acknowledge($usrcrs);
                $this->sendSuccess('msg_acknowledged');
                $this->g_ctrl->redirect($this, AcknowledgmentGUI::CMD_SHOW_UPCOMING);
                break;
            case RequestDigester::CMD_DECLINE:
                $usrcrs = $this->digester->digest();
                $this->object->decline($usrcrs);
                $this->sendSuccess('msg_declined');
                $this->g_ctrl->redirect($this, AcknowledgmentGUI::CMD_SHOW_UPCOMING);
                break;
            default:
                throw new \Exception("Unknown command: " . $this->cmd);
        }
    }

    protected function sendSuccess(string $msg)
    {
        \ilUtil::sendSuccess($this->txt($msg), true);
    }

    public function renderReport()
    {
        $filter = $this->configureFilter($this->report);
        $this->enableRelevantParametersCtrl();
        $table = $this->configureTable($this->report);
        $table->setRowTemplate('tpl.report_row.html', $this->plugin_directory);

        if ($this->loadFilterSettings()) {
            $params = $this->encodeFilterParams($this->loadFilterSettings());
            $_GET['filter_params'] = $params;
        }

        $table->setData($this->report->fetchData());

        $this->g_tpl->setContent(
            $filter->render($this->filter_settings)
            . $table->getHTML()
        );
        $this->disableRelevantParametersCtrl();
    }

    protected function configureFilter(Report $report)
    {
        $filter = $report->filter();
        $this->filter_settings = $this->loadFilterSettings();

        $display = new Filters\DisplayFilter(new Filters\FilterGUIFactory(), new Filters\TypeFactory());
        $this->ecoded_filter_settigs = $this->encodeFilterParams($this->filter_settings);
        $this->addRelevantParameter('filter_params', $this->ecoded_filter_settigs);

        $report->applyFilterToSpace($display->buildFilterValues($filter, $this->filter_settings));

        $filter_view = new \catFilterFlatViewGUI($this, $filter, $display, $this->cmd);
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

    protected function configureTable(Report $report)
    {
        $table = new \SelectableReportTableGUI($this, $this->cmd);
        $table->setId('xack_' . $this->obj_ref_id);
        $report->configureTable($table);
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

    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}
