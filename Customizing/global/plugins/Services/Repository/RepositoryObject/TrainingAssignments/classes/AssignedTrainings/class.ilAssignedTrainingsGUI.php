<?php

use CaT\Plugins\TrainingAssignments;
use ILIAS\TMS\CourseCreation\CourseTemplateDB;
use ILIAS\TMS\CourseCreation;

require_once("Services/TMS/PluginObjectFactory.php");

/**
 * Contaner gui to show assigned trainings
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilAssignedTrainingsGUI
{
    use CourseCreation\LinkHelper;
    use \PluginObjectFactory;

    const CMD_SHOW_ASSIGNMENTS = "showAssignments";
    const CMD_QUICKFILTER = "quickFilter";
    const CMD_SORT = "sort";

    const F_TYPE = "f_type";
    const F_MONTH = "f_month";
    const F_SORT_VALUE = "f_sort_value";

    const S_TITLE_ASC = "s_title_asc";
    const S_PERIOD_ASC = "s_period_asc";

    const S_TITLE_DESC = "s_title_desc";
    const S_PERIOD_DESC = "s_period_desc";

    // How many month are shown in the quick filter before and after the current month.
    const MONTH_OFFSET_PAST = 5;
    const MONTH_OFFSET_FUTURE = 12;
    const OPEN_REQUEST_WAITING_INTERVAL = 30000;

    private static $months = array(
        1 => "Januar",
        2 => "Februar",
        3 => "März",
        4 => "April",
        5 => "Mai",
        6 => "Juni",
        7 => "Juli",
        8 => "August",
        9 => "September",
        10 => "Oktober",
        11 => "November",
        12 => "Dezember"
    );

    /**
     * @var \ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var \ilLanguage
     */
    protected $g_lng;

    /**
     * @var \ilObjTrainingAssignmentsGUI
     */
    protected $parent;

    /**
     * @var TrainingAssignments\ilObjActions 	$actions
     */
    protected $actions;

    /**
     * @var	CourseTemplateDB
     */
    protected $crs_template_db;

    /**
     * @var	ilToolbarGUI
     */
    protected $g_toolbar;

    /**
     * @var	ILIAS\UI\Factory
     */
    protected $g_factory;

    /**
     * @var	ILIAS\UI\Renderer
     */
    protected $g_renderer;

    /**
     * @var ilAccess
     */
    protected $g_access;

    /**
     * @var ILIAS\TMS\CourseCreation/Request[]|null
     */
    protected $cached_requests;

    public function __construct(ilObjTrainingAssignmentsGUI $parent, TrainingAssignments\ilObjActions $actions, CourseTemplateDB $crs_template_db)
    {
        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_lng = $DIC->language();
        $this->g_user = $DIC->user();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_tabs = $DIC->tabs();
        $this->g_factory = $DIC->ui()->factory();
        $this->g_renderer = $DIC->ui()->renderer();
        $this->g_toolbar = $DIC->toolbar();
        $this->g_access = $DIC->access();
        $this->g_rbacreview = $DIC["rbacreview"];
        $this->parent = $parent;
        $this->actions = $actions;
        $this->cached_requests = null;

        $this->crs_template_db = $crs_template_db;

        $this->g_lng->loadLanguageModule("tms");
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
     * @inheritdoc
     */
    protected function getUser()
    {
        return $this->g_user;
    }

    /**
     * @inheritdoc
     */
    protected function sendInfo($message)
    {
        \ilUtil::sendInfo($message);
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCMD();

        switch ($cmd) {
            case self::CMD_SHOW_ASSIGNMENTS:
                if ($this->g_access->checkAccess("read", "", $this->parent->object->getRefId())) {
                    $this->showAssignments();
                } else {
                    \ilUtil::redirect("");
                }
                break;
            case self::CMD_QUICKFILTER:
                $this->quickFilter();
                break;
            case self::CMD_SORT:
                $this->sort();
                break;
        }
    }

    protected function showAssignments()
    {
        $filter = array();
        $data = $this->actions->getAssignedTrainingsFor((int) $this->g_user->getId(), $filter);
        $data = $this->sortTrainings(array(self::F_SORT_VALUE => self::S_PERIOD_ASC), $data);
        $this->showTable($data);
    }

    /**
     * Post processing for quick filter values
     *
     * @return void
     */
    public function quickFilter()
    {
        $get = $_GET;
        $filter = $this->getFilterValuesFrom($get);
        $data = $this->actions->getAssignedTrainingsFor((int) $this->g_user->getId(), $filter);
        $data = $this->sortTrainings($get, $data);
        $this->showTable($data);
    }

    /**
     * Sorts all table entries according to selection
     *
     * @return void
     */
    protected function sort()
    {
        $get = $_GET;
        $filter = $this->getFilterValuesFrom($get);
        $data = $this->actions->getAssignedTrainingsFor((int) $this->g_user->getId(), $filter);
        $data = $this->sortTrainings($get, $data);
        $this->showTable($data);
    }

    /**
     * Render table
     *
     * @param TrainingAssignments\AssignedTrainings\AssignedTrainings[] 	$data
     *
     * @return void
     */
    protected function showTable(array $data)
    {
        $course_templates = $this->crs_template_db->getCreatableCourseTemplates((int) $this->g_user->getId());
        if (count($course_templates) > 0 &&
            !$this->maybeShowRequestInfo($this->getCourseCreationPlugin(), self::OPEN_REQUEST_WAITING_INTERVAL)
        ) {
            $this->addCourseTemplateSelectionModalToToolbar(
                $this->g_factory,
                $this->g_renderer,
                $this->g_toolbar,
                $course_templates,
                ["ilObjPluginDispatchGUI","ilObjTrainingAssignmentsGUI","ilAssignedTrainingsGUI"],
                "showAssignments",
                $this->parent->getObjectRefId()
            );
        }

        $table = new TrainingAssignments\AssignedTrainings\ilAssignedTrainingsTableGUI($this);
        $table->setData($data);

        $filter_controls = $this->getQuickfilterObjects();
        $sort_controls = $this->getSortingObjects();
        $view_controls = array_merge($filter_controls, $sort_controls);
        $content = $table->getHtml($view_controls);
        if (count($data) == 0) {
            $content .= $this->getNoAvailableTrainings();
        }

        $this->g_tpl->setContent($content);
    }

    /**
     * Get objects to filter the source value
     *
     * @return UI\Quickfilter[]
     */
    protected function getQuickfilterObjects()
    {
        $filter_controls = array();
        $link = $this->g_ctrl->getLinkTarget($this, self::CMD_QUICKFILTER);
        $options = array("" => $this->txt("show_all"));

        $months = $this->getMonthOptions();
        $filter_controls[] = $this->g_factory->viewControl()->quickfilter($options + $months)
                    ->withTargetURL($link, self::F_MONTH)
                    ->withDefaultValue("")
                    ->withLabel($this->txt("filter_month"));

        require_once("Services/Component/classes/class.ilPluginAdmin.php");
        if (ilPluginAdmin::isPluginActive('xccl')) {
            $plugin = ilPluginAdmin::getPluginObjectById('xccl');
            $actions = $plugin->getActions();

            $type_options = $actions->getTypeOptions();
            uasort($type_options, function ($a, $b) {
                return strcmp($a, $b);
            });
            $filter_controls[] = $this->g_factory->viewControl()->quickfilter($options + $type_options)
                        ->withTargetURL($link, self::F_TYPE)
                        ->withDefaultValue("")
                        ->withLabel($plugin->txt("conf_options_type"));
        }

        return $filter_controls;
    }

    /**
     * Get objects to sort the source value
     *
     * @return UI\Sorting[]
     */
    protected function getSortingObjects()
    {
        $sort_controls = array();
        $link = $this->g_ctrl->getLinkTarget($this, self::CMD_SORT);
        $sort_controls[] = $this->g_factory->viewControl()->sortation($this->getSortOptions())
                        ->withTargetURL($link, self::F_SORT_VALUE)
                        ->withLabel($this->txt(self::S_PERIOD_ASC));
        return $sort_controls;
    }

    /**
     * Get month options
     *
     * @return string[]
     */
    protected function getMonthOptions()
    {
        $ret = array();
        $m = (int) date("n");
        $y = (int) date("Y");

        $current_month = $this->getMonthOptionCurrent($y, $m);
        $months_before = $this->getMonthOptionsBefore($y, $m);
        $months_after = $this->getMonthOptionsAfter($y, $m);

        $ret = $current_month + $months_before + $months_after;

        ksort($ret);
        return $ret;
    }

    /**
     * Get Options for months before
     *
     * @param string 	$y
     * @param string 	$m
     *
     * @param string[]
     */
    protected function getMonthOptionCurrent($y, $m)
    {
        $ret = array();

        $last_day = $this->getLastDayOfMonth($y, $m);
        $ret[$y . "-" . str_pad($m, 2, 0, STR_PAD_LEFT) . "-" . $last_day] = self::$months[$m] . " " . $y;

        return $ret;
    }

    /**
     * Get Options for months before
     *
     * @param string 	$y
     * @param string 	$m
     *
     * @param string[]
     */
    protected function getMonthOptionsBefore($y, $m)
    {
        $i = 1;
        $ret = array();
        while ($i <= self::MONTH_OFFSET_PAST) {
            if ($m - 1 <= 0) {
                $y--;
                $m = 12;
            } else {
                $m--;
            }

            $last_day = $this->getLastDayOfMonth($y, $m);
            $ret[$y . "-" . str_pad($m, 2, 0, STR_PAD_LEFT) . "-" . $last_day] = self::$months[$m] . " " . $y;

            $i++;
        }

        return $ret;
    }

    /**
     * Get Options for months after
     *
     * @param string 	$y
     * @param string 	$m
     *
     * @param string[]
     */
    protected function getMonthOptionsAfter($y, $m)
    {
        $i = 1;
        $ret = array();
        while ($i <= self::MONTH_OFFSET_FUTURE) {
            if ($m + 1 > 12) {
                $y++;
                $m = 1;
            } else {
                $m++;
            }

            $last_day = $this->getLastDayOfMonth($y, $m);
            $ret[$y . "-" . str_pad($m, 2, 0, STR_PAD_LEFT) . "-" . $last_day] = self::$months[$m] . " " . $y;

            $i++;
        }

        return $ret;
    }

    /**
     * Gets the last day of month
     *
     * @param string 	$y
     * @param string 	$m
     *
     * @return string
     */
    protected function getLastDayOfMonth($y, $m)
    {
        return date("d", strtotime('-1 second', strtotime('+1 month', strtotime($y . "-" . $m . "-01 00:00:00"))));
    }

    /**
     * Parse port array for filter values
     *
     * @return string[]
     */
    public function getFilterValuesFrom(array $values)
    {
        $filter = array();

        if (array_key_exists(self::F_TYPE, $values)) {
            $type = $values[self::F_TYPE];
            if ($type != -1) {
                $filter[self::F_TYPE] = $type;
            }
        }

        if (array_key_exists(self::F_MONTH, $values)) {
            $type = $values[self::F_MONTH];
            if ($type != -1) {
                $filter[self::F_MONTH] = $type;
            }
        }

        return $filter;
    }

    /**
     * Sorts filtered bookable training according to user input
     *
     * @param string[] 	$values
     * @param AdministratedTraining[] 	$data
     *
     * @return AdministratedTraining[]
     */
    public function sortTrainings(array $values, $data)
    {
        if (is_array($data)
            && count($data) > 0
            && array_key_exists(self::F_SORT_VALUE, $values)
            && $values[self::F_SORT_VALUE] != ""
        ) {
            $function = null;
            switch ($values[self::F_SORT_VALUE]) {
                case self::S_TITLE_ASC:
                    uasort($data, $this->getTitleSortingClosure("asc"));
                    break;
                case self::S_TITLE_DESC:
                    uasort($data, $this->getTitleSortingClosure("desc"));
                    break;
                case self::S_PERIOD_ASC:
                    uasort($data, $this->getPeriodSortingClosure("asc"));
                    break;
                case self::S_PERIOD_DESC:
                    uasort($data, $this->getPeriodSortingClosure("desc"));
                    break;
            }
        }

        return $data;
    }

    /**
     * Get sorting closure for title
     *
     * @param string 	$direction
     *
     * @return Closure
     */
    protected function getTitleSortingClosure($direction)
    {
        if ($direction == "asc") {
            return function ($a, $b) {
                return strcasecmp($a->getCourseTitle(), $b->getCourseTitle());
            };
        }

        if ($direction == "desc") {
            return function ($a, $b) {
                return strcasecmp($b->getCourseTitle(), $a->getCourseTitle());
            };
        }
    }

    /**
     * Get sorting closure for period
     *
     * @param string 	$direction
     *
     * @return Closure
     */
    protected function getPeriodSortingClosure($direction)
    {
        if ($direction == "asc") {
            return function ($a, $b) {
                if (is_null($a->getCourseStartDate()) && is_null($b->getCourseStartDate())) {
                    return 0;
                } elseif (is_null($a->getCourseStartDate()) && !is_null($b->getCourseStartDate())) {
                    return 1;
                } elseif (!is_null($a->getCourseStartDate()) && is_null($b->getCourseStartDate())) {
                    return -1;
                }

                $start_date_a = $a->getCourseStartDate()->get(IL_CAL_DATE);
                $start_date_b = $b->getCourseStartDate()->get(IL_CAL_DATE);
                return strcmp($start_date_a, $start_date_b);
            };
        }

        if ($direction == "desc") {
            return function ($a, $b) {
                if (is_null($a->getCourseStartDate()) && is_null($b->getCourseStartDate())) {
                    return 0;
                } elseif (is_null($a->getCourseStartDate()) && !is_null($b->getCourseStartDate())) {
                    return 1;
                } elseif (!is_null($a->getCourseStartDate()) && is_null($b->getCourseStartDate())) {
                    return -1;
                }

                $start_date_a = $a->getCourseStartDate()->get(IL_CAL_DATE);
                $start_date_b = $b->getCourseStartDate()->get(IL_CAL_DATE);
                return strcmp($start_date_b, $start_date_a);
            };
        }
    }

    /**
     * Get the option for sorting of table
     *
     * @return string[]
     */
    public function getSortOptions()
    {
        return array(
            self::S_TITLE_ASC => $this->txt(self::S_TITLE_ASC),
            self::S_TITLE_DESC => $this->txt(self::S_TITLE_DESC),
            self::S_PERIOD_ASC => $this->txt(self::S_PERIOD_ASC),
            self::S_PERIOD_DESC => $this->txt(self::S_PERIOD_DESC)
        );
    }

    /**
     * Get a link to storno from the given training.
     *
     * @param TrainingAssignments\AssignedTraining 	$assigned_training
     * @return string
     */
    public function getCourseLink(TrainingAssignments\AssignedTrainings\AssignedTraining $assigned_training)
    {
        require_once("Services/Link/classes/class.ilLink.php");
        return ilLink::_getStaticLink($assigned_training->getRefId(), "crs");
    }

    /**
     * @param 	string	$code
     * @return	string
     */
    public function txt($code)
    {
        assert('is_string($code)');
        return $this->actions->getObject()->pluginTxt($code);
    }

    /**
     * Get empty search-results message
     *
     * @return void
     */
    protected function getNoAvailableTrainings()
    {
        return $this->txt('no_trainings_available');
    }

    protected function getRbac()
    {
        return new RbacImpl($this->g_rbacreview);
    }
}
