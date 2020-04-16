<?php
declare(strict_types=1);

use \ILIAS\UI\Factory;
use \ILIAS\UI\Renderer;
use \ILIAS\TMS\Mailing\LoggingDB;
use \CaT\Plugins\CourseMailing\ilTxtClosure;
use \CaT\Plugins\CourseMailing\Surroundings\Surroundings;

/**
 * GUI to view the mail logs
 *
 * @author Nils Haagen	<nils.haagen@concepts-and-training.de>
 */
class ilMailLogsGUI
{
    use ilTxtClosure;

    const CMD_SHOW = "show_logs";

    const PARAM_TABLEMODE = 'xcml_tm';
    const TABLEMODE_USER = 'u';
    const TABLEMODE_MAIL = 'm';

    const PARAM_PAGINATION = 'xcml_pag';

    const PARAM_SORTATION = 'xcml_sort';
    const SORT_DATE = 'datetime';
    const SORT_LOGIN = 'usr_login';
    const SORT_MAILID = 'template_ident';

    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ilAccess
     */
    protected $access;

    /**
     * @var \ilTemplate
     */
    protected $tpl;

    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var Surroundings
     */
    protected $surroundings;

    /**
     * @var LoggingDB
     */
    protected $loggin_db;

    /**
     * @var array<string,mixed>
     */
    protected $params = array();

    public function __construct(
        ilCtrl $ctrl,
        ilTemplate $tpl,
        ilAccess $access,
        Factory $factory,
        Renderer $renderer,
        Surroundings $surroundings,
        LoggingDB $loggin_db,
        \Closure $txt
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->access = $access;
        $this->factory = $factory;
        $this->renderer = $renderer;
        $this->surroundings = $surroundings;
        $this->loggin_db = $loggin_db;
        $this->txt = $txt;
    }

    /**
     * Delegate commands
     *
     * @throws \Exception
     * @return void
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        if ($cmd === self::CMD_SHOW) {
            $this->initViewControlParams();
            $this->digestViewControlQuery();
            $this->overview();
        } else {
            throw new Exception("Unknown command " . $cmd);
        }
    }

    protected function getUserPageSize()
    {
        global $DIC;
        return (int) $DIC->user()->prefs['hits_per_page'];
    }


    protected function initViewControlParams()
    {
        $this->params = array(
            self::PARAM_TABLEMODE => self::TABLEMODE_USER,
            self::PARAM_PAGINATION => 0,
            self::PARAM_SORTATION => self::SORT_DATE . '.desc'
        );
    }

    /**
     * Get query params for the table's view-controls
     * @return void
     */
    protected function digestViewControlQuery()
    {
        $get = $_GET;
        foreach ($this->params as $param => $value) {
            if (array_key_exists($param, $get)) {
                $this->params[$param] = $get[$param];
            }
        }
    }

    /**
     * Get query params for the table's view-controls
     * @return string
     */
    protected function getCurrentUrlWithParam($param, $value)
    {
        $url = $_SERVER['REQUEST_URI'];
        $query = html_entity_decode(parse_url($url, PHP_URL_QUERY));
        parse_str($query, $params);
        $params[$param] = $value;
        if (is_null($value)) {
            unset($params[$param]);
        }
        $nu_query = array();
        foreach ($params as $key => $value) {
            $nu_query[] = $key . '=' . $value;
        }
        $nu_query = implode('&', $nu_query);
        $url = str_replace($query, $nu_query, $url);
        return $url;
    }

    /**
     * command: show the GUI
     *
     * @return void
     */
    protected function overview()
    {
        $mode = $this->modeControl();
        $mapping_closure = $this->mappingClosure(); //by mode

        $pagination = $this->paginationControl();
        $sortation = $this->sortationControl();

        //apply pagination
        $limit = array(
            $pagination->getPageLength(),
            $pagination->getOffset()
        );
        if ($limit[0] < 1) {
            $limit = null; //show first page.
        }

        //apply sorting
        $sort = explode('.', $this->params[self::PARAM_SORTATION]);

        $title = $this->txt('log_table_title');
        $view_controls = array($mode, $sortation, $pagination);

        $data = $this->getMailLogsForCourse($sort, $limit);
        $table = $this->factory->table()
        ->presentation($title, $view_controls, $mapping_closure)
        ->withData($data);

        $this->tpl->setContent(
            $this->renderer->render($table)
        );
    }

    /**
     * @return UI\Component\ViewControl\Mode
     */
    protected function modeControl()
    {
        $view_controls = array();
        $modes = array(
            self::TABLEMODE_USER => $this->txt('log_table_mode_user'),
            self::TABLEMODE_MAIL => $this->txt('log_table_mode_mail')
        );
        $actions = array(
            $modes[self::TABLEMODE_USER] => htmlentities($this->getCurrentUrlWithParam(self::PARAM_TABLEMODE, self::TABLEMODE_USER)),
            $modes[self::TABLEMODE_MAIL] => htmlentities($this->getCurrentUrlWithParam(self::PARAM_TABLEMODE, self::TABLEMODE_MAIL))
        );

        $aria_label = $this->txt('aria_table_mode');
        return $this->factory->viewControl()->mode($actions, $aria_label)
            ->withActive($modes[$this->params[self::PARAM_TABLEMODE]]);
    }

    /**
     * @return UI\Component\ViewControl\Pagination
     */
    protected function paginationControl()
    {
        $data_count = $this->getMailLogsCountForCourse();

        $url = $_SERVER['REQUEST_URI'];
        $pagination = $this->factory->viewControl()->pagination()
            ->withTargetURL($url, self::PARAM_PAGINATION)
            ->withTotalEntries($data_count)
            ->withPageSize($this->getUserPageSize())
            ->withCurrentPage((int) $this->params[self::PARAM_PAGINATION]);
        return $pagination;
    }

    /**
     * @return UI\Component\ViewControl\Sortation
     */
    protected function sortationControl()
    {
        $url = $this->getCurrentUrlWithParam(self::PARAM_SORTATION, null);
        $sort_options = array(
            self::SORT_DATE,
            self::SORT_LOGIN,
            self::SORT_MAILID,
        );
        $options = array();
        foreach ($sort_options as $opt) {
            $options[$opt . '.asc'] = $this->txt($opt . '_asc');
            $options[$opt . '.desc'] = $this->txt($opt . '_desc');
        }

        $sortation = $this->factory->viewControl()->sortation($options)
            ->withTargetURL($url, self::PARAM_SORTATION)
            ->withLabel($options[$this->params[self::PARAM_SORTATION]]);

        return $sortation;
    }

    /**
     * @return \Closure
     */
    protected function mappingClosure()
    {
        switch ($this->params[self::PARAM_TABLEMODE]) {
            case self::TABLEMODE_USER:
                $closure = $this->rowClosureUserOriented();
                break;
            case self::TABLEMODE_MAIL:
                $closure = $this->rowClosureMailOriented();
                break;
        }
        return $closure;
    }

    /**
     * @return closure
     */
    protected function rowClosureUserOriented()
    {
        //$record is a LogEntry
        $rowmapping = function ($row, $record, $ui_factory, $environment) {
            $subtitle = sprintf(
                '%s - %s',
                $record->getUserLogin(),
                $record->getUserMail()
            );

            $important_fields = array(
                $record->getDateAsString(),
                $this->txt('log_table_ifield_templateident') => $record->getTemplateIdent()
            );

            $content = $ui_factory->listing()->descriptive(array(
                $this->txt('log_table_content_label_subject') => $record->getSubject(),
                $this->txt('log_table_content_label_msg') => nl2br($record->getMessage())
            ));

            $err = $record->getError();
            if (trim($err) === '') {
                $err = $this->txt('log_table_no_error');
            }

            $attachments = implode('<br>', $record->getAttachments());
            if (trim($attachments) === '') {
                $attachments = $this->txt('log_table_no_attachments');
            }

            $further_fields = array(
                $this->txt('log_table_ffield_date') => $record->getDateAsString(),
                $this->txt('log_table_ifield_templateident') => $record->getTemplateIdent(),
                $this->txt('log_table_ffield_event') => $record->getEvent(),
                $this->txt('log_table_ffield_attachments') => $attachments,
                $this->txt('log_table_ffield_error') => $err
            );

            return $row
                ->withHeadline($record->getUserName())
                ->withSubheadline($subtitle)
                ->withImportantFields($important_fields)
                ->withContent($content)
                ->withFurtherFieldsHeadline($this->txt('log_table_ffields_headline'))
                ->withFurtherFields($further_fields)
            ;
        };
        return $rowmapping;
    }

    /**
     * @return closure
     */
    protected function rowClosureMailOriented()
    {
        //$record is a LogEntry
        $rowmapping = function ($row, $record, $ui_factory, $environment) {
            $title = sprintf(
                '%s - %s',
                $record->getTemplateIdent(),
                $record->getSubject()
            );

            $subtitle = sprintf(
                '%s (%s) - %s',
                $record->getUserName(),
                $record->getUserLogin(),
                $record->getUserMail()
            );

            $important_fields = array(
                $record->getDateAsString()
            );

            $content = $ui_factory->listing()->descriptive(array(
                $this->txt('log_table_content_label_subject') => $record->getSubject(),
                $this->txt('log_table_content_label_msg') => nl2br($record->getMessage())
            ));

            $err = $record->getError();
            if (trim($err) === '') {
                $err = '-';
            }
            $further_fields = array(
                $this->txt('log_table_ffield_date') => $record->getDateAsString(),
                $this->txt('log_table_ifield_templateident') => $record->getTemplateIdent(),
                $this->txt('log_table_ffield_event') => $record->getEvent(),
                $this->txt('log_table_ffield_attachments') => implode('<br>', $record->getAttachments()),
                $this->txt('log_table_ffield_error') => $err
            );

            return $row
            ->withHeadline($title)
            ->withSubheadline($subtitle)
            ->withImportantFields($important_fields)
            ->withContent($content)
            ->withFurtherFieldsHeadline($this->txt('log_table_ffields_headline'))
            ->withFurtherFields($further_fields)
            ;
        };
        return $rowmapping;
    }

    protected function getMailLogsForCourse($sort = null, $limit = null)
    {
        $crs_ref = $this->surroundings->getParentCourseRefId();
        return $this->loggin_db->selectForCourse($crs_ref, $sort, $limit);
    }

    protected function getMailLogsCountForCourse()
    {
        $crs_ref = $this->surroundings->getParentCourseRefId();
        return $this->loggin_db->selectCountForCourse($crs_ref);
    }
}
