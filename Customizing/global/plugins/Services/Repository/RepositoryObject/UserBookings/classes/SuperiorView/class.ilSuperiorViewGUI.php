<?php

declare(strict_types=1);

use CaT\Plugins\UserBookings\ilObjActions;
use CaT\Plugins\UserBookings\SuperiorView;
use CaT\Plugins\UserBookings\Helper;
use CaT\Plugins\UserBookings\SuperiorView\DB;

/**
* @ilCtrl_Calls ilSuperiorViewGUI: ilTMSSuperiorCancelGUI, ilTMSSuperiorCancelWaitingGUI
*/
class ilSuperiorViewGUI
{
    use ILIAS\TMS\MyUsersHelper {
        getUsersWhereCurrentCanViewBookings as protected TgetUsersWhereCurrentCanViewBookings;
    }

    const HITS_PER_PAGE = "hits_per_page";
    const PAGINATION_PARAM = "pagination";
    const DROPDOWN_AT_PAGES = 1;

    protected $visible_users = [];

    /**
     * Overwrite Trait function to ensure query buffer.
     *
     * @param	int	$usr_id
     * @return	int[]
     */
    protected function getUsersWhereCurrentCanViewBookings($usr_id)
    {
        if (!array_key_exists($usr_id, $this->visible_users)) {
            $this->visible_users[$usr_id] = $this->TgetUsersWhereCurrentCanViewBookings($usr_id);
        }
        return $this->visible_users[$usr_id];
    }

    const CMD_SHOW_BOOKINGS = "showEmployeeBookings";
    const CMD_CHANGE_USER = "changeUser";
    const CMD_SORT = "sort";

    const S_TITLE_ASC = "s_title_asc";
    const S_PERIOD_ASC = "s_period_asc";
    const S_TITLE_DESC = "s_title_desc";
    const S_PERIOD_DESC = "s_period_desc";
    const S_BY_NAME_DESC = "s_by_name_desc";
    const S_BY_NAME_ASC = "s_by_name_asc";
    const S_USER = "s_user";

    const F_SORT_VALUE = "f_sort_value";

    const P_TABLEMODE = "p_tablemode";
    const T_USER = "t_user";
    const T_COURSE = "t_course";

    protected static $save_parameter = array(
        self::S_USER,
        self::P_TABLEMODE
    );

    /**
     * @var ilObjUserBookingsGUI
     */
    protected $parent;

    /**
     * @var ilObjActions
     */
    protected $actions;

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ilAccess
     */
    protected $g_access;

    /**
     * @var ilTemplate
     */
    protected $g_tpl;

    /**
     * @var ilObjUser
     */
    protected $g_user;

    public function __construct(ilObjUserBookingsGUI $parent, ilObjActions $actions, Helper $helper)
    {
        $this->parent = $parent;
        $this->actions = $actions;
        $this->helper = $helper;

        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_user = $DIC->user();
        $this->g_tabs = $DIC->tabs();
        $this->g_access = $DIC->access();
        $this->locator = $DIC['ilLocator'];
        $this->g_f = $DIC->ui()->factory();
        $this->g_renderer = $DIC->ui()->renderer();
        $this->g_lng = $DIC->language();

        $this->g_tabs->clearTargets();
        $this->g_lng->loadLanguageModule('tms');
        $this->setInitialSearchUsers();
        $this->setInitialTableMode();
    }

    public function executeCommand()
    {
        $next_class = $this->g_ctrl->getNextClass();
        $cmd = $this->g_ctrl->getCmd();

        if (is_null($cmd) || $cmd == "") {
            $this->g_ctrl->clearParameters($this);
            $cmd = self::CMD_SHOW_BOOKINGS;
        }

        $this->changeUser();
        $this->changeTableMode();

        switch ($next_class) {
            case "iltmssuperiorcancelgui":
                require_once("Services/TMS/Cancel/classes/class.ilTMSSuperiorCancelGUI.php");
                $gui = new ilTMSSuperiorCancelGUI($this, self::CMD_SHOW_BOOKINGS, false);
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "iltmssuperiorcancelwaitinggui":
                require_once("Services/TMS/Cancel/classes/class.ilTMSSuperiorCancelWaitingGUI.php");
                $gui = new ilTMSSuperiorCancelWaitingGUI($this, self::CMD_SHOW_BOOKINGS, false);
                $this->g_ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_SHOW_BOOKINGS:
                    case self::CMD_CHANGE_USER:
                    case self::CMD_SORT:
                        if ($this->g_access->checkAccess("read", "", $this->parent->object->getRefId())) {
                            $this->showBookings();
                        } else {
                            \ilUtil::redirect("");
                        }
                        break;
                    default:
                        throw new Exception("Uknown command: " . $cmd);
                }
        }
    }

    /**
     * Set all amployees of current user as search user
     *
     * @return void
     */
    protected function setInitialSearchUsers()
    {
        $this->search_user_ids = array_keys($this->getUsersWhereCurrentCanViewBookings((int) $this->g_user->getId()));
    }

    /**
     * Set the mode of the table
     *
     * @return void
     */
    protected function setInitialTableMode()
    {
        $this->table_mode = self::T_USER;
    }

    /**
     * Lists all bookings as table
     *
     * @return void
     */
    protected function showBookings()
    {
        $view_controls = $this->getViewControlObjects();
        $this->g_ctrl->saveParameter($this, self::$save_parameter);
        $pagination = $this->getPagination();

        if ($pagination->getNumberOfPages() > 1) {
            $view_controls[] = $pagination;
        }

        $table = new SuperiorView\ilSuperiorViewTableGUI(
            $this,
            $this->helper,
            $view_controls,
            $this->table_mode
        );

        $offset = $pagination->getOffset();
        $limit = $pagination->getPageSize();
        $data = $this->actions->getBookedTrainingsFor($this->search_user_ids, $this->getSortMode($_GET), $limit, $offset);
        $table->setData($data);
        $content = $table->getHtml($this->actions->isReccomendationAllowed());
        if (count($data) == 0) {
            $content .= $this->getNoAvailableTrainings();
        }

        $this->g_tpl->setContent($content);
    }

    protected function getPagination() : \ILIAS\UI\Implementation\Component\ViewControl\Pagination
    {
        $current_page = (int) $_GET[self::PAGINATION_PARAM];
        $link = $this->g_ctrl->getLinkTarget($this, self::CMD_SHOW_BOOKINGS, "", false, false);
        $limit = (int) $this->g_user->getPref(self::HITS_PER_PAGE);

        $max_number = $this->actions->getBookedTrainingsCountFor($this->search_user_ids);
        return $this->g_f->viewControl()->pagination()
            ->withTotalEntries($max_number)
            ->withPageSize($limit)
            ->withCurrentPage($current_page)
            ->withTargetURL($link, self::PAGINATION_PARAM)
            ->withDropdownAt(self::DROPDOWN_AT_PAGES);
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

    public function txt($cmd)
    {
        return $this->actions->getObject()->pluginTxt($cmd);
    }

    /**
     * Get all needed view control objects
     *
     * @return array
     */
    protected function getViewControlObjects()
    {
        $view_controls = array();
        $view_controls[] = $this->getModeControl();
        $view_controls[] = $this->getEmployeeFilter();
        $view_controls[] = $this->getSortControl();
        $view_controls = array_filter($view_controls, function ($c) {
            return $c !== null;
        });

        return $view_controls;
    }

    /**
     * Get mode control
     *
     * @return ViewControl\Mode
     */
    protected function getModeControl()
    {
        $actions = array(
            $this->g_lng->txt(self::T_USER) => htmlentities($this->getCurrentUrlWithParam(self::P_TABLEMODE, self::T_USER)),
            $this->g_lng->txt(self::T_COURSE) => htmlentities($this->getCurrentUrlWithParam(self::P_TABLEMODE, self::T_COURSE))
        );

        $aria_label = "change_the_currently_displayed_mode";
        return $this->g_f->viewControl()->mode($actions, $aria_label)->withActive($this->g_lng->txt($this->table_mode));
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
     * Get employee filter control
     *
     * @return ViewControl\Quickfilter | null
     */
    protected function getEmployeeFilter()
    {
        $employees = array("" => "Alle");
        $employees = $employees + $this->getUsersWhereCurrentCanViewBookings((int) $this->g_user->getId());
        if (count($employees) > 0) {
            $link = $this->g_ctrl->getLinkTarget($this, self::CMD_CHANGE_USER);
            return $this->g_f->viewControl()->quickfilter($employees)
                ->withTargetURL($link, self::S_USER)
                ->withDefaultValue("")
                ->withLabel($this->g_lng->txt("employees"));
        }

        return null;
    }

    /**
     * Get sorting control
     *
     * @return ViewControl\Sortation
     */
    protected function getSortControl()
    {
        $link = $this->g_ctrl->getLinkTarget($this, self::CMD_SORT);
        $mode = $this->getSortMode($_GET);

        $control = $this->g_f->viewControl()->sortation($this->getSortOptions())
                        ->withTargetURL($link, self::F_SORT_VALUE);

        $_GET[$control->getParameterName()] = $mode;

        return $control->withLabel($this->getSortModeLabel($mode));
    }

    /**
     * Get the option for sorting of table
     *
     * @return string[]
     */
    public function getSortOptions()
    {
        if ($this->table_mode === self::T_COURSE) {
            return array(
                DB::SORT_BY_TITLE_ASC => $this->g_lng->txt(self::S_TITLE_ASC),
                DB::SORT_BY_TITLE_DESC => $this->g_lng->txt(self::S_TITLE_DESC),
                DB::SORT_BY_PERIOD_ASC => $this->g_lng->txt(self::S_PERIOD_ASC),
                DB::SORT_BY_PERIOD_DESC => $this->g_lng->txt(self::S_PERIOD_DESC)
            );
        } else {
            return array(
                DB::SORT_BY_NAME_ASC => $this->g_lng->txt(self::S_BY_NAME_ASC),
                DB::SORT_BY_NAME_DESC => $this->g_lng->txt(self::S_BY_NAME_DESC),
                DB::SORT_BY_PERIOD_ASC => $this->g_lng->txt(self::S_PERIOD_ASC),
                DB::SORT_BY_PERIOD_DESC => $this->g_lng->txt(self::S_PERIOD_DESC)
            );
        }
    }

    /**
     * Change user courses are searched for to selected user
     *
     * @return void
     */
    protected function changeUser()
    {
        $get = $_GET;
        if (isset($get[self::S_USER])
            && $get[self::S_USER] !== ""
            && array_key_exists((int) $get[self::S_USER], $this->getUsersWhereCurrentCanViewBookings((int) $this->g_user->getId()))
        ) {
            $this->search_user_ids = array((int) $get[self::S_USER]);
        }
    }

    /**
     * Change mode of the table
     *
     * @return void
     */
    protected function changeTableMode()
    {
        $get = $_GET;
        if (isset($get[self::P_TABLEMODE]) && $get[self::P_TABLEMODE] !== "") {
            $this->table_mode = $get[self::P_TABLEMODE];
        }
    }

    /**
     * @param	array	$get
     * @return	mixed
     */
    protected function getSortMode(array $get)
    {
        //defaults:
        if (!array_key_exists(self::F_SORT_VALUE, $get)) {
            if ($this->table_mode == self::T_COURSE) {
                return DB::SORT_BY_TITLE_DESC;
            } else {
                return DB::SORT_BY_NAME_DESC;
            }
        }
        //map by mode:
        $sortation = $get[self::F_SORT_VALUE];
        if ($this->table_mode == self::T_COURSE) {
            if ($sortation === DB::SORT_BY_NAME_ASC) {
                $sortation = DB::SORT_BY_TITLE_ASC;
            }
            if ($sortation === DB::SORT_BY_NAME_DESC) {
                $sortation = DB::SORT_BY_TITLE_DESC;
            }
        }

        if ($this->table_mode == self::T_USER) {
            if ($sortation === DB::SORT_BY_TITLE_ASC) {
                $sortation = DB::SORT_BY_NAME_ASC;
            }
            if ($sortation === DB::SORT_BY_TITLE_DESC) {
                $sortation = DB::SORT_BY_NAME_DESC;
            }
        }
        return $sortation;
    }

    /**
     * @param	mixed	$sortation
     * @return	string
     */
    protected function getSortModeLabel($sortation)
    {
        switch ($sortation) {
            case DB::SORT_BY_TITLE_ASC:
                return $this->g_lng->txt(self::S_TITLE_ASC);
            case DB::SORT_BY_TITLE_DESC:
                return $this->g_lng->txt(self::S_TITLE_DESC);
            case DB::SORT_BY_NAME_ASC:
                return $this->g_lng->txt(self::S_BY_NAME_ASC);
            case DB::SORT_BY_NAME_DESC:
                return $this->g_lng->txt(self::S_BY_NAME_DESC);
            case DB::SORT_BY_PERIOD_ASC:
                return $this->g_lng->txt(self::S_PERIOD_ASC);
            case DB::SORT_BY_PERIOD_DESC:
                return $this->g_lng->txt(self::S_PERIOD_DESC);
            default:
                throw new \LogicException("Unknown sortation mode: $sortation");
        }
    }

    protected function getAccess()
    {
        return $this->g_access;
    }
}
