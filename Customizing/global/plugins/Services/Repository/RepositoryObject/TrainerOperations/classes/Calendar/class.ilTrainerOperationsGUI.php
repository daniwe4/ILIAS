<?php

declare(strict_types=1);

use CaT\Plugins\TrainerOperations\Calendar\CalRenderer;
use CaT\Plugins\TrainerOperations\Calendar\SessionModal;
use CaT\Plugins\TrainerOperations\Calendar\CalendarBuilder;
use CaT\Plugins\TrainerOperations\Calendar\CalConfig;
use CaT\Plugins\TrainerOperations\Calendar\AssignmentActions;
use CaT\Plugins\TrainerOperations\Aggregations\UserAuthority;
use CaT\Plugins\TrainerOperations\Aggregations\IliasRepository;
use CaT\Plugins\TrainerOperations\Aggregations\User;
use ILIAS\TMS\CourseCreation\CourseTemplateDB;
use ILIAS\TMS\CourseCreation;
use CaT\Plugins\TrainerOperations\AccessHelper;

/**
 * GUI for the TEP.
 *
 * @ilCtrl_isCalledBy ilTrainerOperationsGUI: ilObjPluginDispatchGUI
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilTrainerOperationsGUI
{
    use CourseCreation\LinkHelper;
    use \PluginObjectFactory;

    const CMD_SHOW = 'cmd_show_tep';
    const F_START = 'start';
    const F_INTERVAL = 'dint';
    const INTERVAL_WEEK = 'P1W';
    const INTERVAL_MONTH = 'P1M';
    const INTERVAL_QUART = 'P3M';
    const INTERVAL_HALFYEAR = 'P6M';

    const F_COL_SELECTOR = 'cols';
    const CMD_SELECT_COLS = 'selcol';

    const OPEN_REQUEST_WAITING_INTERVAL = 30000;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var \ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var \ilTemplate
     */
    protected $g_tpl;

    /**
     * @var CalRenderer
     */
    protected $renderer;

    /**
     * @var Factory
     */
    protected $ui_factory;

    /**
     * @var DefaultRenderer
     */
    protected $ui_renderer;

    /**
     * @var CalendarBuilder
     */
    protected $cal_builder;

    /**
     * @var UserAuthority
     */
    protected $user_authority;

    /**
     * @var IliasRepository
     */
    protected $il_repo;

    /**
     * @var AssignmentActions
     */
    protected $assignment;

    /**
     * @var \ilTemplate
     */
    protected $col_selector_tpl;
    /**
     * @var User
     */
    protected $user_utils;

    /**
     * @var int
     */
    protected $tep_obj_id;

    /**
     * @var int
     */
    protected $tep_ref_id;

    /**
     * @var	CourseTemplateDB
     */
    protected $crs_template_db;

    /**
     * @var	\ilObjUser
     */
    protected $g_usr;

    /**
     * @var	\ilLanguage
     */
    protected $g_lng;

    /**
     * @var	AccessHelper
     */
    protected $access_helper;

    /**
     * @var \RbacImpl
     */
    protected $rbac;

    public function __construct(
        \Closure $txt,
        \ilCtrl $g_ctrl,
        \ilTemplate $g_tpl,
        \ilToolbarGUI $toolbar,
        \ILIAS\UI\Implementation\Factory $ui_factory,
        \ILIAS\UI\Implementation\DefaultRenderer $ui_renderer,
        CalRenderer $renderer,
        SessionModal $session_modal,
        CalendarBuilder $cal_builder,
        UserAuthority $user_authority,
        IliasRepository $il_repo,
        AssignmentActions $assignment,
        User $user_utils,
        \ilTemplate $col_selector_tpl,
        int $tep_obj_id,
        int $tep_ref_id,
        CourseTemplateDB $crs_template_db,
        \ilObjUser $g_usr,
        \ilLanguage $g_lng,
        AccessHelper $access_helper,
        \RbacImpl $rbac
    ) {
        $this->txt = $txt;
        $this->g_ctrl = $g_ctrl;
        $this->g_tpl = $g_tpl;
        $this->toolbar = $toolbar;
        $this->ui_factory = $ui_factory;
        $this->ui_renderer = $ui_renderer;
        $this->renderer = $renderer;
        $this->session_modal = $session_modal;
        $this->cal_builder = $cal_builder;
        $this->user_authority = $user_authority;
        $this->il_repo = $il_repo;
        $this->assignment = $assignment;
        $this->user_utils = $user_utils;
        $this->col_selector_tpl = $col_selector_tpl;
        $this->tep_obj_id = $tep_obj_id;
        $this->tep_ref_id = $tep_ref_id;
        $this->crs_template_db = $crs_template_db;
        $this->g_usr = $g_usr;
        $this->g_lng = $g_lng;
        $this->access_helper = $access_helper;
        $this->rbac = $rbac;

        $this->current_interval = self::INTERVAL_MONTH;
        $this->current_offset = new DateTime();
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_SHOW:
            case self::CMD_SELECT_COLS:
                $conf = $this->configure($_GET, $_POST);
                $this->buildToolbar($conf);
                $this->buildColumnSelector($conf);
                $this->showCalendars($conf);
                break;

            case CalRenderer::ASYNC_CMD_SESSION_MODAL:
                $session_ref = (int) $_GET[CalRenderer::F_SESSION_REF_ID];
                $course_ref = (int) $_GET[CalRenderer::F_COURSE_REF_ID];
                $modal = $this->session_modal->getFor($session_ref, $course_ref);
                ob_clean();
                echo $this->ui_renderer->renderAsync($modal);
                exit();
                break;

            case SessionModal::CMD_ASSIGN_TUTORS_TO_SESSION:

                foreach ($_POST as $key => $value) {
                    if (substr($key, 0, strlen(SessionModal::F_TUTOR_SOURCE)) === SessionModal::F_TUTOR_SOURCE) {
                        $tutor_source = (int) $_POST[$key];
                    }
                }
                $session_ref = (int) $_POST[SessionModal::F_SESSION_REF_ID];

                if ($tutor_source === \ilObjSession::TUTOR_CFG_MANUALLY) {
                    $tutor_name = (string) $_POST[SessionModal::F_TUTOR_NAME];
                    $tutor_mail = (string) $_POST[SessionModal::F_TUTOR_MAIL];
                    $tutor_phone = (string) $_POST[SessionModal::F_TUTOR_PHONE];
                    $this->assignment->assignManualTutorsToSession($session_ref, $tutor_name, $tutor_mail, $tutor_phone);
                }

                if ($tutor_source === \ilObjSession::TUTOR_CFG_FROMCOURSE) {
                    $tutor_ids = $_POST[SessionModal::F_ASSIGNS_TUTORS];
                    if (!$tutor_ids) {
                        $tutor_ids = [];
                    }
                    $tutor_ids = array_map('intval', $tutor_ids);
                    $this->assignment->assignTutorsToSession($session_ref, $tutor_ids);
                }

                $this->g_ctrl->redirect($this, self::CMD_SHOW);
                break;

            case SessionModal::CMD_ASSIGN_TUTORS_TO_COURSE:
                $session_ref_id = (int) $_GET[SessionModal::F_SESSION_REF_ID];
                $crs_ref_id = (int) $_GET[SessionModal::F_COURSE_REF_ID];
                $tutor_id = (int) $_GET[SessionModal::F_TUTOR_ID];

                $this->assignment->assignTutorToCourse($crs_ref_id, $tutor_id);

                $add_frm = $this->session_modal->getAddTutorForm(
                    $session_ref_id,
                    $crs_ref_id
                );
                $assign_frm = $this->session_modal->getTutorAssignForm(
                    $session_ref_id,
                    $crs_ref_id
                );

                echo(
                    $add_frm->get()
                    . '##SPLIT##'
                    . $assign_frm->getHtml()
                );

                exit();
                break;

            default:
                throw new Exception(__METHOD__ . " :: Unknown command " . $cmd);
        }
    }

    protected function configure(array $get_params, array $post_params = []) : CalConfig
    {
        $session_id = 'xtep_calparams_' . $this->tep_ref_id;
        $session_params = $this->user_utils->getCurrentUserPrefsForTEP($this->tep_ref_id);

        $interval = $get_params[self::F_INTERVAL];
        if ($interval && in_array(
            $interval,
            [
                self::INTERVAL_WEEK,
                self::INTERVAL_MONTH,
                self::INTERVAL_QUART,
                self::INTERVAL_HALFYEAR
            ]
        )) {
            $this->current_interval = $interval;
        } else {
            if (array_key_exists('interval', $session_params)) {
                $this->current_interval = $session_params['interval'];
            }
        }
        $session_params['interval'] = $this->current_interval;


        $start = $get_params[self::F_START];
        if ($start) {
            $start = \DateTime::createFromFormat('Ymd', $start);
        } else {
            if (array_key_exists('start', $session_params)) {
                $start = $session_params['start'];
            } else {
                $start = new \DateTime();
            }
        }
        $session_params['start'] = $start;


        $this->current_offset = $this->getNewSeedFor(
            $start,
            $this->current_interval,
            0
        );

        if ($this->access_helper->maySeeForeignCalendars()) {
            $tutor_ids = $this->user_authority->getTrainers();
            $tutor_ids = $this->user_utils->sortUsrIdsByUserLastname($tutor_ids);
        } else {
            $tutor_ids = [$this->user_utils->getCurrentUserId()];
        }

        $columns = [];

        if ($this->access_helper->maySeeGeneralCalendars()) {
            $columns[CalendarBuilder::ID_GLOBAL] = true;
        }

        if ($this->access_helper->maySeeUnassingedDates()) {
            $columns[CalendarBuilder::ID_UNASSIGNED] = true;
        }

        if ($this->access_helper->maySeeForeignCalendars()) {
            $columns[CalendarBuilder::ID_INACCESSIBLE] = true;
        }

        foreach ($tutor_ids as $id) {
            $columns[(string) $id] = true;
        }

        if (array_key_exists('selected', $session_params)) {
            foreach ($session_params['selected'] as $key => $value) {
                if (array_key_exists($key, $columns)) {
                    $columns[$key] = $session_params['selected'][$key];
                }
            }
        }

        if (array_key_exists(self::F_COL_SELECTOR, $post_params)) {
            foreach ($columns as $key => $value) {
                if (in_array($key, $post_params[self::F_COL_SELECTOR])) {
                    $columns[$key] = true;
                } else {
                    $columns[$key] = false;
                }
            }
        }
        $session_params['selected'] = $columns;

        $this->user_utils->setCurrentUserPrefsForTEP($this->tep_ref_id, $session_params);

        $conf = new CalConfig(
            $this->tep_obj_id,
            $this->il_repo->getParentId($this->tep_ref_id),
            $tutor_ids,
            $this->current_offset,
            new \DateInterval($this->current_interval),
            $columns
        );

        return $conf;
    }

    protected function getSelectorFormUrl() : string //TODO: ilCtrl?!
    {
        $modal_async_url = $_SERVER['REQUEST_URI'];
        $base = substr($modal_async_url, 0, strpos($modal_async_url, '?') + 1);
        $query = parse_url($modal_async_url, PHP_URL_QUERY);
        parse_str($query, $params);

        $params['cmd'] = self::CMD_SELECT_COLS;
        $selector_url = $base . http_build_query($params);
        return $selector_url;
    }

    protected function buildColumnSelector(CalConfig $config)
    {
        $trans = [
            CalendarBuilder::ID_UNASSIGNED => $this->txt('schedule_unassigned'),
            CalendarBuilder::ID_INACCESSIBLE => $this->txt('schedule_inaccessible'),
            CalendarBuilder::ID_GLOBAL => $this->txt('schedule_general')
        ];

        foreach ($config->getSelectedColumns() as $schedule_id => $checked) {
            if (array_key_exists($schedule_id, $trans)) {
                $label = $trans[$schedule_id];
            } else {
                $label = $this->user_utils->getDisplayName((int) $schedule_id);
            }

            $attr_checked = '';
            if ($checked) {
                $attr_checked = 'checked="checked"';
            }
            $this->col_selector_tpl->setCurrentBlock('col_selector_entry');
            $this->col_selector_tpl->setVariable('F_COL_SELECTOR', self::F_COL_SELECTOR);
            $this->col_selector_tpl->setVariable('COL_SELECTOR_VALUE', $schedule_id);
            $this->col_selector_tpl->setVariable('COL_SELECTOR_ENTRY_LABEL', $label);
            $this->col_selector_tpl->setVariable('COL_SELECTOR_CHECKED', $attr_checked);
            $this->col_selector_tpl->parseCurrentBlock();
        }

        $this->col_selector_tpl->setVariable('COL_SELECTOR_FORM_ACTION', $this->getSelectorFormUrl());
        $this->col_selector_tpl->setVariable('COL_SELECTOR_LABEL', $this->txt('select_columns'));
        $this->col_selector_tpl->setVariable('COL_SELECTOR_BTN_LABEL', $this->txt('submit_col_selector'));
    }


    protected function buildToolbar(CalConfig $config)
    {
        $f = $this->ui_factory;
        $thisclass = get_class($this);

        $back = $this->getNewSeedFor($this->current_offset, $this->current_interval, -1)->format('Ymd');
        $today = $this->getNewSeedFor(new \DateTime(), $this->current_interval, 0)->format('Ymd');
        $forward = $this->getNewSeedFor($this->current_offset, $this->current_interval, +1)->format('Ymd');

        $this->g_ctrl->setParameterByClass($thisclass, self::F_INTERVAL, $this->current_interval);

        $this->g_ctrl->setParameterByClass($thisclass, self::F_START, $back);
        $dat_back = $f->button()->standard("back", $this->g_ctrl->getLinkTarget($this, self::CMD_SHOW));
        $this->g_ctrl->setParameterByClass($thisclass, self::F_START, $forward);
        $dat_forward = $f->button()->standard("forward", $this->g_ctrl->getLinkTarget($this, self::CMD_SHOW));
        $this->g_ctrl->setParameterByClass($thisclass, self::F_START, $today);
        $dat_now = $f->button()->standard($this->txt("nav_now"), $this->g_ctrl->getLinkTarget($this, self::CMD_SHOW));

        $view_control_section = $f->viewControl()->section($dat_back, $dat_now, $dat_forward);

        $this->g_ctrl->setParameterByClass($thisclass, self::F_START, $this->current_offset->format('Ymd'));

        $this->g_ctrl->setParameterByClass($thisclass, self::F_INTERVAL, self::INTERVAL_WEEK);
        $mode_week = $this->g_ctrl->getLinkTarget($this, self::CMD_SHOW);
        $this->g_ctrl->setParameterByClass($thisclass, self::F_INTERVAL, self::INTERVAL_MONTH);
        $mode_month = $this->g_ctrl->getLinkTarget($this, self::CMD_SHOW);
        $this->g_ctrl->setParameterByClass($thisclass, self::F_INTERVAL, self::INTERVAL_QUART);
        $mode_quart = $this->g_ctrl->getLinkTarget($this, self::CMD_SHOW);
        $this->g_ctrl->setParameterByClass($thisclass, self::F_INTERVAL, self::INTERVAL_HALFYEAR);
        $mode_halfyear = $this->g_ctrl->getLinkTarget($this, self::CMD_SHOW);

        $mode_actions = [
            $this->getLabelForInterval(self::INTERVAL_WEEK) => $mode_week,
            $this->getLabelForInterval(self::INTERVAL_MONTH) => $mode_month,
            $this->getLabelForInterval(self::INTERVAL_QUART) => $mode_quart,
            $this->getLabelForInterval(self::INTERVAL_HALFYEAR) => $mode_halfyear
        ];

        $aria_label = '';
        $view_control_mode = $f->viewControl()->mode($mode_actions, $aria_label)
            ->withActive($this->getLabelForInterval($this->current_interval));

        $this->toolbar->addComponent($view_control_section);
        $this->toolbar->addSeparator();
        $this->toolbar->addComponent($view_control_mode);
        $this->toolbar->addSeparator();

        $course_templates = $this->crs_template_db->getCreatableCourseTemplates((int) $this->g_usr->getId());
        if (
            count($course_templates) > 0 &&
            !$this->maybeShowRequestInfo($this->getCourseCreationPlugin(), self::OPEN_REQUEST_WAITING_INTERVAL)
        ) {
            $this->addCourseTemplateSelectionModalToToolbar(
                $this->ui_factory,
                $this->ui_renderer,
                $this->toolbar,
                $course_templates,
                ["ilObjPluginDispatchGUI", "ilObjTrainerOperationsGUI","ilTrainerOperationsGUI"],
                //["ilObjTrainerOperationsGUI","ilTrainerOperationsGUI"],
                self::CMD_SHOW,
                $this->tep_ref_id
            );
        }
    }

    /**
     * @return	\ilCtrl
     */
    protected function getCtrl()
    {
        return $this->g_ctrl;
    }

    /**
    * @return \ilLanguage
    */
    protected function getLng()
    {
        return $this->g_lng;
    }

    /**
     * @return \ilObjUser
     */
    protected function getUser()
    {
        return $this->g_usr;
    }

    protected function sendInfo($message)
    {
        \ilUtil::sendInfo($message, true);
    }


    protected function showCalendars(CalConfig $config)
    {
        $calendar = $this->cal_builder
            ->configure($config)
            ->getCalendar();

        $calhtml = $this->renderer->render($calendar);
        $modals = $this->renderer->getModals();

        $this->addFilesFromSkinOrPlugin();
        $this->g_tpl->addJavaScript("./Services/Form/js/Form.js");

        $this->g_tpl->setContent(
            $this->ui_renderer->render($modals)
            . $this->col_selector_tpl->get()
            . $calhtml
        );
    }

    protected function addFilesFromSkinOrPlugin()
    {
        $skin = $this->user_utils->getSkin($this->user_utils->getCurrentUserId());
        $path = 'Customizing/global/plugins/Services/Repository/RepositoryObject/TrainerOperations';
        $css = '/templates/css/tep.css';
        $js = '/templates/js/tep.js';

        if ($skin === 'default') {
            $this->g_tpl->addCss($path . $css);
            $this->g_tpl->addJavascript($path . $js);
        } else {
            $skinpath = 'Customizing/global/skin/' . $skin . '/' . $path;
            if (file_exists($skinpath . $css)) {
                $this->g_tpl->addCss($skinpath . $css);
            } else {
                $this->g_tpl->addCss($path . $css);
            }
            if (file_exists($skinpath . $js)) {
                $this->g_tpl->addJavascript($skinpath . $js);
            } else {
                $this->g_tpl->addJavascript($path . $js);
            }
        }
    }

    protected function getNewSeedFor(\DateTime $seed, string $for_interval, int $direction) : \DateTime
    {
        $seed = clone $seed;
        switch ($for_interval) {
            case self::INTERVAL_WEEK:
                $seed->modify('monday this week');
                break;
            case self::INTERVAL_MONTH:
                $seed->modify('first day of this month');
                break;
            case self::INTERVAL_QUART:
                $month = $seed->format('n') ;
                if ($month < 4) {
                    $seed->modify('first day of january');
                } elseif ($month > 3 && $month < 7) {
                    $seed->modify('first day of april');
                } elseif ($month > 6 && $month < 10) {
                    $seed->modify('first day of july');
                } elseif ($month > 9) {
                    $seed->modify('first day of october');
                }
                break;
            case self::INTERVAL_HALFYEAR:
                $month = $seed->format('n') ;
                if ($month < 7) {
                    $seed->modify('first day of january');
                } else {
                    $seed->modify('first day of july');
                }

        }
        if ($direction === -1) {
            $seed->sub(new DateInterval($for_interval));
        }
        if ($direction === 1) {
            $seed->add(new DateInterval($for_interval));
        }
        return $seed;
    }

    protected function getLabelForInterval(string $for_interval) : string
    {
        switch ($for_interval) {
            case self::INTERVAL_WEEK:
                return $this->txt('week');
            case self::INTERVAL_MONTH:
                return $this->txt('month');
            case self::INTERVAL_QUART:
                return $this->txt('quart');
            case self::INTERVAL_HALFYEAR:
                return $this->txt('halfyear');

        }
    }

    public function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }

    protected function getRbac()
    {
        return $this->rbac;
    }
}
