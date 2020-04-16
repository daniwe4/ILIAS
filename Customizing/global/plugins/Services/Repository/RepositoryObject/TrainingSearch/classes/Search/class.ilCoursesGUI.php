<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\TrainingSearch\Search;
use \ILIAS\TMS\MyUsersHelper;
use ILIAS\UI;

/**
 * Displays the TMS training search
 */
class ilCoursesGUI
{
    use MyUsersHelper;

    const REPOSITORY_REF_ID = 1;

    const CMD_SHOW = "show";
    const CMD_SHOW_MODAL = "showModal";
    const CMD_FILTER = "filter";
    const CMD_CHANGE_USER = "changeUser";
    const CMD_QUICKFILTER = "quickFilter";
    const CMD_SORT = "sort";

    const PAGE_SIZE = 50;
    const PAGINATION_PARAM = "pagination";
    const DROPDOWN_AT_PAGES = 1;

    const F_TEXT_SEARCH = "f_textsearch";
    const F_TYPE = "f_type";
    const F_TOPIC = "f_topic";
    const F_DURATION = "f_duration";
    const F_DURATION_START = "f_duration_start";
    const F_DURATION_END = "f_duration_end";
    const F_SORT_VALUE = "f_sort_value";
    const F_ONLY_BOOKABLE = "f_only_bookable";
    const F_IDD_RELEVANT = "f_idd_relevant";
    const F_CATEGORY = "f_category";
    const F_TARGET_GROUP = "f_target_group";
    const F_VENUE_SEARCH_TAGS = "f_venue_search_tags";

    const S_USER = "s_user";

    const TARGET = "target";
    const CMD = "cmd";

    public static $blocked_link_create_params = array(
        self::S_USER
    );

    public static $alllowed_params = array(
        self::F_TEXT_SEARCH,
        self::F_TYPE,
        self::F_TOPIC,
        self::F_DURATION,
        self::F_DURATION_START,
        self::F_DURATION_END,
        self::F_SORT_VALUE,
        self::F_ONLY_BOOKABLE,
        self::F_IDD_RELEVANT,
        self::F_CATEGORY,
        self::F_TARGET_GROUP,
        Search\Options::SORTATION_TITLE_ASC,
        Search\Options::SORTATION_PERIOD_ASC,
        Search\Options::SORTATION_CITY_ASC,
        Search\Options::SORTATION_TITLE_DESC,
        Search\Options::SORTATION_PERIOD_DESC,
        Search\Options::SORTATION_CITY_DESC,
        Search\Options::SORTATION_DEFAULT,
        self::S_USER,
        self::CMD,
        self::F_VENUE_SEARCH_TAGS
    );

    protected static $save_parameter = array(
        self::S_USER,
        self::F_TEXT_SEARCH,
        self::F_TYPE,
        self::F_TOPIC,
        self::F_DURATION_START,
        self::F_DURATION_END,
        self::F_SORT_VALUE,
        self::F_ONLY_BOOKABLE,
        self::F_IDD_RELEVANT,
        self::F_CATEGORY,
        self::F_VENUE_SEARCH_TAGS
    );

    /**
     * @var TrainingSearch\DB
     */
    protected $db;

    /**
     * @var Serach\Helper
     */
    protected $helper;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var UI\Factory
     */
    protected $factory;

    /**
     * @var UI\Renderer
     */
    protected $renderer;

    /**
     * @var ilObjTrainingSearch
     */
    protected $object;

    /**
     * UserId of the user that is going to be booked. Initially set to current ilUser.
     * Initial the current ilUser.
     * This might be changed, if the current user is allowed to book for others.
     *
     * @var int
     */
    protected $search_user_id;

    /**
     * @var TMSSession
     */
    protected $session;

    public function __construct(
        ilCtrl $ctrl,
        ilTemplate $tpl,
        ilObjUser $user,
        UI\Factory $factory,
        UI\Renderer $renderer,
        ilObjTrainingSearch $object,
        Search\DB $db,
        ilTrainingSearchPageGUI $page_gui,
        ilAccess $access,
        ilSetting $setting
    ) {
        $this->db = $db;
        $this->tpl = $tpl;
        $this->ctrl = $ctrl;
        $this->user = $user;
        $this->txt = $object->getTxtClosure();
        $this->object = $object;
        $this->factory = $factory;
        $this->renderer = $renderer;
        $this->page_gui = $page_gui;
        $this->access = $access;
        $this->setting = $setting;

        $this->search_user_id = (int) $user->getId();
    }

    public function executeCommand()
    {
        $this->changeUser();
        $cmd = $this->ctrl->getCmd();

        if (is_null($cmd) || $cmd == "") {
            $this->ctrl->clearParameters($this);
            $cmd = self::CMD_SHOW;
        }

        switch ($cmd) {
            case self::CMD_SHOW:
                $this->show();
                break;
            case self::CMD_CHANGE_USER:
                $this->showUserResult();
                break;
            case self::CMD_FILTER:
                $this->filter();
                break;
            case self::CMD_QUICKFILTER:
                $this->quickFilter();
                break;
            case self::CMD_SORT:
                $this->sort();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    /**
     * Shows all bookable trainings
     */
    protected function show()
    {
        $this->getTMSSession()->setCurrentSearch((int) $this->object->getRefId());

        if ($this->pageConfigured()) {
            $this->showConfiguredPage();
        } else {
            $bookable_trainings = $this->getItemsForTable();
            $this->showTrainings($bookable_trainings, self::CMD_SHOW);
        }
    }

    protected function pageConfigured()
    {
        if ($this->access->checkAccess("write", "", $this->object->getRefId())) {
            return false;
        }

        if (!$this->setting->get("enable_cat_page_edit")) {
            return false;
        }

        include_once("./Services/COPage/classes/class.ilPageUtil.php");
        if (!ilPageUtil::_existsAndNotEmpty(
            "xtrs",
            $this->object->getId()
        )) {
            return false;
        }

        return true;
    }

    protected function showConfiguredPage()
    {
        $xpage_id = ilContainer::_lookupContainerSetting(
            $this->object->getId(),
            "xhtml_page"
        );
        if ($xpage_id > 0) {
            include_once("Services/XHTMLPage/classes/class.ilXHTMLPage.php");
            $xpage = new ilXHTMLPage($xpage_id);
            $this->tpl->setContent($xpage->getContent());
        }

        include_once("./Services/Container/classes/class.ilContainerPage.php");
        include_once("./Services/Container/classes/class.ilContainerPageGUI.php");

        $this->tpl->setContent($this->page_gui->showPage());
    }

    /**
     * Shows all bookable trainings
     */
    protected function showUserResult()
    {
        $bookable_trainings = $this->getItemsForTable();
        $this->showTrainings($bookable_trainings, self::CMD_CHANGE_USER);
    }

    /**
     * Post processing for filter values
     */
    public function filter()
    {
        $bookable_trainings = $this->getItemsForTable();
        $this->showTrainings($bookable_trainings, self::CMD_FILTER);
    }

    /**
     * Sorts all table entries according to selection
     */
    protected function sort()
    {
        $bookable_trainings = $this->getItemsForTable();
        $this->showTrainings($bookable_trainings, self::CMD_SORT);
    }

    /**
     * Post processing for quick filter values
     */
    public function quickFilter()
    {
        $bookable_trainings = $this->getItemsForTable();
        $this->showTrainings($bookable_trainings, self::CMD_QUICKFILTER);
    }

    protected function getItemsForTable()
    {
        $get = $_GET;
        $options = new Search\Options(
            $this->search_user_id,
            $this->object->getSearchLocationRefId()
        );

        if (array_key_exists(self::F_SORT_VALUE, $get) && $get[self::F_SORT_VALUE] != "") {
            $options = $options->withSortation($get[self::F_SORT_VALUE]);
        }

        $options = $this->appendFilterValuesFrom($get, $options);
        $this->saveParameter($get);

        $bookable_trainings = $this->db->getCoursesFor($options);

        return $bookable_trainings;
    }

    /**
     * Carry filter params to consecutive calls of class.
     */
    protected function saveParameter(array $filter_params)
    {
        foreach ($filter_params as $param => $value) {
            if (in_array($param, self::$save_parameter)) {
                $this->ctrl->setParameter($this, $param, $value);
            }
        }
    }

    /**
     * Show bookable trainings
     *
     * @param Course[] 	$bookable_trainings
     */
    protected function showTrainings(array $bookable_trainings, string $cmd)
    {
        $table = $this->object->getDI()["search.tablegui"];
        $table->setData($bookable_trainings);
        $this->ctrl->saveParameter($this, self::$save_parameter);

        $modal = $this->prepareModal();
        $button1 = $this->factory->button()->standard($this->txt('search'), '#')
            ->withOnClick($modal->getShowSignal());

        $current_page = (int) $_GET[self::PAGINATION_PARAM];
        $view_controls = array($button1);
        $view_controls = array_merge($view_controls, $this->addSortationObjects());

        $link = $this->ctrl->getLinkTarget($this, $cmd, "", false, false);
        $pagination = $this->factory->viewControl()->pagination()
            ->withTotalEntries(count($bookable_trainings))
            ->withPageSize(self::PAGE_SIZE)
            ->withCurrentPage($current_page)
            ->withTargetURL($link, self::PAGINATION_PARAM)
            ->withDropdownAt(self::DROPDOWN_AT_PAGES);
        $offset = $pagination->getOffset();
        $limit = self::PAGE_SIZE;

        if (count($bookable_trainings) > 0 && $pagination->getNumberOfPages() > 1) {
            $view_controls[] = $pagination;
        }

        $content = $this->renderer->render($modal) .
            $table->render(
                $view_controls,
                $offset,
                $limit,
                $this->search_user_id,
                $this->object->getSettings()->isRecommendationAllowed()
            );

        if (count($bookable_trainings) == 0) {
            $content .= $this->getNoAvailableTrainings();
        }

        if ($this->access->checkAccess("write", "", $this->object->getRefId())) {
            require_once "Services/Form/classes/class.ilTextInputGUI.php";
            $item = new \ilTextInputGUI("", "");
            $link = ilLink::_getStaticLink(
                $this->object->getRefId(),
                $this->object->getType(),
                true,
                "&cmd=" . ilCoursesGUI::CMD_FILTER
            );
            $link = $this->appendCurrentGetParamters($link);
            $link = str_replace(ILIAS_HTTP_PATH, ".", $link);
            $item->setValue($link);
            $content .= "<br />" . $this->txt("link") . ":<br />" . $item->render();
        }

        $this->tpl->setContent($content);
    }

    /**
     * look into current $_GET params and append left-overs that are
     * not controlled by this component.
     *
     * @param string $url
     * @return string
     */
    protected function appendCurrentGetParamters($url)
    {
        $query = html_entity_decode(parse_url($url, PHP_URL_QUERY));
        parse_str($query, $params);
        foreach ($_GET as $key => $value) {
            if (!array_key_exists($key, $params) &&
                in_array($key, self::$alllowed_params) &&
                !in_array($key, self::$blocked_link_create_params) &&
                $value != -1
            ) {
                $url .= '&' . $key . '=' . $value;
            }
        }
        return $url;
    }

    /**
     * Add all sorting and filter items for the table
     *
     * @return Sortation[]
     */
    protected function addSortationObjects() : array
    {
        $ret = [];
        $self_id = (int) $this->user->getId();
        $employees = $this->getUserWhereCurrentCanBookFor((int) $self_id);
        unset($employees[$self_id]);
        uasort($employees, function ($a, $b) {
            return strcasecmp($a, $b);
        });
        $employees = [$self_id => $this->txt('my_training_search_options')] + $employees;
        if (count($employees) > 1) {
            $link = ilLink::_getStaticLink(
                $this->object->getRefId(),
                $this->object->getType(),
                true,
                "&cmd=" . ilCoursesGUI::CMD_CHANGE_USER
            );

            $ret[] = $this->factory->viewControl()->quickfilter($employees)
                ->withTargetURL($link, self::S_USER)
                ->withDefaultValue($this->user->getId())
                ->withLabel($this->txt("employees"));
        }

        require_once("Services/Component/classes/class.ilPluginAdmin.php");
        if (ilPluginAdmin::isPluginActive('xccl')) {
            $plugin = ilPluginAdmin::getPluginObjectById('xccl');
            $actions = $plugin->getActions();
            $link = ilLink::_getStaticLink(
                $this->object->getRefId(),
                $this->object->getType(),
                true,
                "&cmd=" . ilCoursesGUI::CMD_QUICKFILTER
            );

            $options = array("" => $this->txt("show_all"));
            $type_options = $actions->getTypeOptions();
            uasort($type_options, function ($a, $b) {
                return strcasecmp($a, $b);
            });
            $ret[] = $this->factory->viewControl()->quickfilter($options + $type_options)
                        ->withTargetURL($link, self::F_TYPE)
                        ->withDefaultValue("")
                        ->withLabel($plugin->txt("conf_options_type"));

            $relevant_topics = $this->object->getSettings()->relevantTopics();
            $topic_options = array_intersect_key($actions->getTopicOptions(), array_flip($relevant_topics));
            if (count($topic_options) > 0) {
                uasort($topic_options, function ($a, $b) {
                    return strcasecmp($a, $b);
                });
                $ret[] = $this->factory->viewControl()->quickfilter($options + $topic_options)
                            ->withTargetURL($link, self::F_TOPIC)
                            ->withDefaultValue("")
                            ->withLabel($plugin->txt("conf_options_topic"));
            }
        }

        // Default sort order to period descending.
        $link = ilLink::_getStaticLink(
            $this->object->getRefId(),
            $this->object->getType(),
            true,
            "&cmd=" . ilCoursesGUI::CMD_SORT
        );
        $ret[] = $this->factory->viewControl()->sortation($this->getSortOptions())
                        ->withTargetURL($link, self::F_SORT_VALUE)
                        ->withLabel($this->txt("s_period_asc"));

        return $ret;
    }

    /**
     * Get empty search-results message
     */
    protected function getNoAvailableTrainings() : string
    {
        return $this->txt('no_trainings_available');
    }

    public function prepareModal() : \ILIAS\UI\Implementation\Component\Modal\Modal
    {
        $form = new catMethodVariableFormGUI();
        $form->setId(uniqid('form'));
        $form->setMethod("get");
        $form->setFormAction(ilLink::_getStaticLink(
            $this->object->getRefId(),
            $this->object->getType()
        ));

        $item = new \ilHiddenInputGUI(self::TARGET);
        $item->setValue('xtrs_' . $this->object->getRefId());
        $form->addItem($item);

        $item = new \ilHiddenInputGUI(self::S_USER);
        $item->setValue($this->search_user_id);
        $form->addItem($item);

        $sort_item = $this->getSortItem();
        if ($sort_item != "") {
            $item = new \ilHiddenInputGUI(self::F_SORT_VALUE);
            $item->setValue($sort_item);
            $form->addItem($item);
        }

        return $this->prepareModalForm($form);
    }

    protected function getSortItem() : string
    {
        $get = $_GET;

        if (array_key_exists(self::F_SORT_VALUE, $get)) {
            return $get[self::F_SORT_VALUE];
        }

        return "";
    }

    /**
     * Change user courses are searched for to selected user
     */
    protected function changeUser()
    {
        $get = $_GET;
        if (isset($get[self::S_USER]) && $get[self::S_USER] !== "") {
            $this->search_user_id = (int) $get[self::S_USER];
        }
    }

    /**
     * Get the option for sorting of table
     *
     * @return string[]
     */
    public function getSortOptions() : array
    {
        $vals = array(
            Search\Options::SORTATION_TITLE_ASC => $this->txt("s_title_asc"),
            Search\Options::SORTATION_TITLE_DESC => $this->txt("s_title_desc"),
            Search\Options::SORTATION_PERIOD_ASC => $this->txt("s_period_asc"),
            Search\Options::SORTATION_PERIOD_DESC => $this->txt("s_period_desc"),
            Search\Options::SORTATION_CITY_ASC => $this->txt("s_city_asc"),
            Search\Options::SORTATION_CITY_DESC => $this->txt("s_city_desc"),
        );

        uasort($vals, function ($a, $b) {
            return strcasecmp($a, $b);
        });

        return $vals;
    }

    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }

    protected function appendFilterValuesFrom(array $values, Search\Options $options) : Search\Options
    {
        if (\ilPluginAdmin::isPluginActive('xccl')) {
            $plugin = \ilPluginAdmin::getPluginObjectById('xccl');
            $actions = $plugin->getActions();
        } else {
            $actions = null;
        }

        if (array_key_exists(self::F_TEXT_SEARCH, $values)) {
            $title = trim($values[self::F_TEXT_SEARCH]);
            if ($title != "") {
                $options = $options->withTextFilter($title);
            }
        }

        if (array_key_exists(self::F_TYPE, $values)) {
            $type = $values[self::F_TYPE];
            if ($type != -1 && $type !== "" && !is_null($actions)) {
                $type_options = $actions->getTypeOptions();
                $options = $options->withTypeFilter($type_options[$type]);
            }
        }

        if (array_key_exists(self::F_CATEGORY, $values)) {
            $category = $values[self::F_CATEGORY];
            if ($category != -1 && $category !== "" && !is_null($actions)) {
                $category_options = $actions->getCategoryOptions();
                $options = $options->withCategoryFilter($category_options[$category]);
            }
        }

        if (array_key_exists(self::F_TOPIC, $values)) {
            $topic = $values[self::F_TOPIC];
            if ($topic != -1 && $topic !== "" && !is_null($actions)) {
                $topic_options = $actions->getTopicOptions();
                $options = $options->withTopicFilter($topic_options[$topic]);
            }
        }

        if (array_key_exists(self::F_TARGET_GROUP, $values)) {
            $target_groups = $values[self::F_TARGET_GROUP];
            if ($target_groups != -1 && $target_groups !== "" && !is_null($actions)) {
                $target_group_options = $actions->getTargetGroupOptions();
                $options = $options->withTargetGroupsFilter($target_group_options[$target_groups]);
            }
        }

        if (array_key_exists(self::F_DURATION, $values)) {
            $filter[self::F_DURATION] = $values[self::F_DURATION];
            $options = $options->withDurationFilter(
                new \DateTime($values[self::F_DURATION]['start']),
                new \DateTime($values[self::F_DURATION]['end'])
            );
        } elseif (array_key_exists(self::F_DURATION_START, $values)
                && array_key_exists(self::F_DURATION_END, $values)) {
            $options = $options->withDurationFilter(
                new \DateTime($values[self::F_DURATION_START]),
                new \DateTime($values[self::F_DURATION_END])
            );
        }

        $options = $options
            ->withOnlyBookableFilter((bool) $values[self::F_ONLY_BOOKABLE])
            ->withIDDRelevantFilter((bool) $values[self::F_IDD_RELEVANT]);

        if (\ilPluginAdmin::isPluginActive('venues')) {
            if (array_key_exists(self::F_VENUE_SEARCH_TAGS, $values) &&
                trim($values[self::F_VENUE_SEARCH_TAGS]) != "" &&
                (int) $values[self::F_VENUE_SEARCH_TAGS] != -1
            ) {
                $search_tag = trim($values[self::F_VENUE_SEARCH_TAGS]);
                $options = $options->withVenueSearchTagFilter((int) $search_tag);
            }
        }

        return $options;
    }

    protected function prepareModalForm(\ilPropertyFormGUI $form) : \ILIAS\UI\Implementation\Component\Modal\Modal
    {
        require_once('./Services/Form/classes/class.ilTextInputGUI.php');
        require_once('./Services/Form/classes/class.ilDateDurationInputGUI.php');
        require_once("Services/Component/classes/class.ilPluginAdmin.php");

        $item = new \ilTextInputGUI($this->txt('full_text'), self::F_TEXT_SEARCH);
        $item->setInfo(sprintf($this->txt('full_text_info'), $this->txt('title')));
        $form->addItem($item);

        if (\ilPluginAdmin::isPluginActive('xccl')) {
            $plugin = \ilPluginAdmin::getPluginObjectById('xccl');
            $actions = $plugin->getActions();

            $item = new \ilSelectInputGUI($this->txt('type'), self::F_TYPE);
            $type_options = $actions->getTypeOptions();
            uasort($type_options, function ($a, $b) {
                return strcasecmp($a, $b);
            });
            $options = array(-1 => "Alle") + $type_options;
            $item->setOptions($options);
            $form->addItem($item);

            $category_options = array_intersect_key(
                $actions->getCategoryOptions(),
                array_flip($this->object->getSettings()->relevantCategories())
            );
            if (count($category_options) > 0) {
                $item = new \ilSelectInputGUI($this->txt('category'), self::F_CATEGORY);
                uasort($category_options, function ($a, $b) {
                    return strcasecmp($a, $b);
                });
                $options = array(-1 => "Alle") + $category_options;
                $item->setOptions($options);
                $form->addItem($item);
            }

            $topic_options = array_intersect_key(
                $actions->getTopicOptions(),
                array_flip($this->object->getSettings()->relevantTopics())
            );
            if (count($topic_options) > 0) {
                $item = new \ilSelectInputGUI($this->txt('topic'), self::F_TOPIC);
                uasort($topic_options, function ($a, $b) {
                    return strcasecmp($a, $b);
                });
                $options = array(-1 => "Alle") + $topic_options;
                $item->setOptions($options);
                $form->addItem($item);
            }

            $target_group_options = array_intersect_key(
                $actions->getTargetGroupOptions(),
                array_flip($this->object->getSettings()->relevantTargetGroups())
            );
            if (count($target_group_options) > 0) {
                $item = new \ilSelectInputGUI($this->txt('target_groups'), self::F_TARGET_GROUP);
                uasort($target_group_options, function ($a, $b) {
                    return strcasecmp($a, $b);
                });
                $options = array(-1 => "Alle") + $target_group_options;
                $item->setOptions($options);
                $form->addItem($item);
            }
        }

        if (\ilPluginAdmin::isPluginActive('venues')) {
            $plugin = \ilPluginAdmin::getPluginObjectById('venues');
            $used_search_tags = $plugin->getUsedSearchTags();
            if (count($used_search_tags) > 0) {
                $item = new \ilSelectInputGUI($this->txt('search_tags'), self::F_VENUE_SEARCH_TAGS);
                uasort($used_search_tags, function ($a, $b) {
                    return strcasecmp($a, $b);
                });

                $options = array(-1 => "Alle") + $used_search_tags;
                $item->setOptions($options);
                $form->addItem($item);
            }
        }

        $item = new \ilDateDurationInputGUI($this->txt('duration'), self::F_DURATION);
        $item->setStart(new \ilDateTime(date("Y-01-01 00:00:00"), IL_CAL_DATETIME));
        $item->setEnd(new \ilDateTime(date("Y-12-31 23:59:59"), IL_CAL_DATETIME));
        $form->addItem($item);

        $item = new \ilCheckboxInputGUI($this->txt('only_bookable'), self::F_ONLY_BOOKABLE);
        $form->addItem($item);

        require_once("Services/Component/classes/class.ilPluginAdmin.php");
        if (\ilPluginAdmin::isPluginActive("xetr")) {
            $item = new \ilCheckboxInputGUI($this->txt('idd_relevant'), self::F_IDD_RELEVANT);
            $form->addItem($item);
        }

        $item = new \ilHiddenInputGUI('cmd');
        $item->setValue(self::CMD_FILTER);
        $form->addItem($item);

        $get = $_GET;
        if (isset($get['cmd']) &&
            in_array(
                $get['cmd'],
                array(self::CMD_CHANGE_USER,
                    self::CMD_QUICKFILTER,
                    self::CMD_SORT,
                    self::CMD_FILTER
                )
            )
        ) {
            if (!isset($get[self::TARGET])) {
                $get[self::TARGET] = "xtrs_" . $get["ref_id"];
            }

            if (isset($get[self::F_DURATION_START]) && isset($get[self::F_DURATION_END])) {
                $get[self::F_DURATION] = [
                    'start' => $get[self::F_DURATION_START],
                    'end' => $get[self::F_DURATION_END],
                ];
            }

            $form->setValuesByArray($get);
        }

        // Build a submit button (action button) for the modal footer
        $form_id = 'form_' . $form->getId();
        $submit = $this->factory->button()->primary($this->txt('search'), "#")->withOnLoadCode(function ($id) use ($form_id) {
            return "$('#{$id}').click(function() { $('#{$form_id}').submit(); return false; });";
        });

        $reset = $this->factory->button()->standard($this->txt('reset'), "#")->withOnLoadCode(function ($id) use ($form_id) {
            $dur1 = '$("input[name=\'f_duration[start]\']").val("' . date("01.01.Y") . '");';
            $dur2 = '$("input[name=\'f_duration[end]\']").val("' . date("31.12.Y") . '");';
            return "$('#{$id}').click(function() {
				$('#f_textsearch').val('');
				$('#f_type option').removeAttr('selected').filter('[value=-1]').attr('selected', true);
				$('#f_topic option').removeAttr('selected').filter('[value=-1]').attr('selected', true);
				$('#f_target_group option').removeAttr('selected').filter('[value=-1]').attr('selected', true);
				$('#f_venue_search_tags option').removeAttr('selected').filter('[value=-1]').attr('selected', true);
				$('#f_category option').removeAttr('selected').filter('[value=-1]').attr('selected', true);
				$('#f_not_min_member').prop('checked', false );
				$('#f_only_bookable').prop('checked', false );
				$('#f_idd_relevant').prop('checked', false );
				" . $dur1 . "
				" . $dur2 . "
				return false;
			});";
        });

        $modal = $this->factory->modal()->roundtrip($this->txt('filter'), $this->factory->legacy($form->getHTML()))
            ->withActionButtons([$reset, $submit]);

        return $modal;
    }

    protected function getAccess() : ilAccess
    {
        return $this->access;
    }
}
