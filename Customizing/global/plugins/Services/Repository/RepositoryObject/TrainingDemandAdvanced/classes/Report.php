<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainingDemandAdvanced;

use ILIAS\TMS\ActionBuilder;
use ILIAS\TMS\Filter;
use ILIAS\TMS\TableRelations;
use ILIAS\TMS\ReportUtilities\TreeObjectDiscovery;

class Report
{
    const HEAD_COURSE_TABLE = "hhd_crs";
    const HEAD_COURSE_TUT_TABLE = "hhd_crs_tut";
    const HEAD_COURSE_TOPICS_TABLE = "hhd_crs_topics";
    const HEAD_COURSE_CATEGORIES_TABLE = "hhd_crs_categories";
    const HEAD_USERCOURSE_TABLE = "hhd_usrcrs";
    const HEAD_USERCOURSE_NIGHTS_TABLE = "hhd_usrcrs_nights";
    const HEAD_USERCOURSE_ROLES = "hhd_usrcrs_roles";

    const WITH_IDD = 1;
    const WITHOUT_IDD = 2;
    const DANGER_CANCEL = 3;
    const OVERBOOKED = 4;
    const AVAILABLE_PLACES = 5;
    const FREETEXT_VALUE = -1;

    /**
     * @var	Filter\Filters\Filter|null
     */
    protected $filter = null;

    /**
     * @var	TableRelations\TableSpace|null
     */
    protected $space = null;

    /**
     * @var \ilEduBiographyPlugin
     */
    protected $plugin;

    /**
     * @var	TableRelations\GraphFactory
     */
    protected $gf;

    /**
     * @var	TableRelations\TableFactory
     */
    protected $tf;

    /**
     * @var	Filter\PredicateFactory
     */
    protected $pf;

    /**
     * @var	Filter\TypeFactory
     */
    protected $tyf;

    /**
     * @var	Filter\FilterFactory
     */
    protected $ff;

    /**
     * @var	ilDBInterface
     */
    protected $db;

    protected $action_link_helper;
    /**
     * @var int
     */
    protected $current_user_id;

    public function __construct(
        \ilTrainingDemandAdvancedPlugin $plugin,
        \ilDBInterface $db,
        ActionLinksHelper $action_link_helper,
        TreeObjectDiscovery $o_d,
        Settings\Settings $settings,
        \ilObject $object,
        int $current_user_id
    ) {
        $this->plugin = $plugin;

        $this->gf = new TableRelations\GraphFactory();
        $this->pf = new Filter\PredicateFactory();
        $this->tf = new TableRelations\TableFactory($this->pf, $this->gf);
        $this->tyf = new Filter\TypeFactory();
        $this->ff = new Filter\FilterFactory($this->pf, $this->tyf);

        $this->db = $db;

        $this->action_link_helper = $action_link_helper;
        $this->o_d = $o_d;
        $this->settings = $settings;
        $this->object = $object;
        $this->current_user_id = $current_user_id;
    }

    /**
     * Get the data for the report.
     *
     * @return	array
     */
    public function fetchData()
    {
        //die($this->interpreter()->getSql($this->space()->query()));
        $res = $this->db->query($this->interpreter()->getSql($this->space()->query()));
        $return = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $return[] = $this->postprocessRowHTML($row);
        }
        return $return;
    }

    /**
     * Postprocess a row from the database to display it as HTML.
     *
     * @param	array	$row
     * @return	array
     */
    protected function postprocessRowHTML(array $row)
    {
        if (array_key_exists('ref_id', $row) && $row['ref_id'] !== null) {
            $row['actions'] = $this->actionMenuFor((int) $row['ref_id']);
        }
        return $this->postprocessRowCommon($row);
    }


    protected function actionMenuFor(int $ref_id)
    {
        $l = new \ilAdvancedSelectionListGUI();
        $l->setListTitle($this->plugin->txt("please_choose"));

        $action_link_helper = $this->action_link_helper->withRefId($ref_id);
        $action_links = $action_link_helper->getAdministratedTrainingActionLinks(
            ActionBuilder::CONTEXT_MY_ADMIN_TRAININGS
        );
        foreach ($action_links as $title => $link) {
            $l->addItem(
                $title,
                $title,
                $link,
                '',
                '',
                '_blank'
            );
        }

        $l->setId("selection_list_" . $ref_id);
        return $l->getHTML();
    }


    /**
     * Postprocess a row from the database to display it as anything.
     *
     * @param	array	$row
     * @return	array
     */
    protected function postprocessRowCommon(array $row)
    {
        if ($row['booking_dl_date'] !== null && $row['booking_dl_date'] > '0001-01-01') {
            $row['booking_dl_date'] = \DateTime::createFromFormat('Y-m-d', $row['booking_dl_date'])->format('d.m.Y');
        }
        if ($row['storno_dl_date'] !== null && $row['storno_dl_date'] > '0001-01-01') {
            $row['storno_dl_date'] = \DateTime::createFromFormat('Y-m-d', $row['storno_dl_date'])->format('d.m.Y');
        }

        if ($this->isEduTrackingActive()) {
            if (array_key_exists('idd_learning_time', $row)) {
                $row['idd_learning_time'] = $this->minutesToTimeString((int) $row['idd_learning_time']);
            }
        } else {
            unset($row['idd_learning_time']);
        }

        if (array_key_exists('begin_date', $row) && $row['begin_date'] !== null && $row['begin_date'] > '0001-01-01') {
            $date = \DateTime::createFromFormat('Y-m-d', $row['begin_date'])->format('d.m.Y');
            $row['crs_date'] = $date;
        }
        if (array_key_exists('end_date', $row) && $row['end_date'] !== null && $row['end_date'] > '0001-01-01') {
            $date = \DateTime::createFromFormat('Y-m-d', $row['end_date'])->format('d.m.Y');
            $row['crs_date_end'] = $date;
        }

        if (array_key_exists('max_members', $row)
            && ((string) $row['max_members'] === '0'
            || (string) $row['max_members'] === '')) {
            $row['max_members'] = $this->plugin->txt('infinite');
        }
        if (array_key_exists('min_members', $row)
            && ((string) $row['min_members'] === '0'
            || (string) $row['min_members'] === '')) {
            $row['min_members'] = $this->plugin->txt('none');
        }
        if (array_key_exists('nights_resolved_count', $row)) {
            if (!is_null($row['nights_resolved_count'])) {
                $row['nights_resolved_count'] = implode(
                    ' ',
                    array_map(
                        function ($arg) {
                            if ((string) $arg !== '') {
                                $ex = explode(':', $arg);
                                return $ex[0] . ' (' . $ex[1] . ')';
                            }
                        },
                        explode(',', $row['nights_resolved_count'])
                    )
                );
            }
        }

        $row = $this->clearZeroes($row);
        $row = $this->replaceEmptyWithMinus($row);
        return $row;
    }

    protected static $nummeric_cols = [
        'count_booked'
        , 'count_waiting'
        , 'count_members'
    ];

    protected function clearZeroes(array $row)
    {
        foreach (self::$nummeric_cols as $col) {
            if (array_key_exists($col, $row) && (string) $row[$col] === '') {
                $row[$col] = '0';
            }
        }
        return $row;
    }

    protected function replaceEmptyWithMinus(array $row) : array
    {
        foreach ($row as $key => $entry) {
            if ($entry == null || $entry == "") {
                $row[$key] = "-";
            }
        }
        return $row;
    }


    /**
     * Get the filter for the report.
     *
     * @return	ILIAS\TMS\Filter\Filters\Filter
     */
    public function filter()
    {
        if ($this->filter === null) {
            $this->filter = $this->buildFilter();
        }
        return $this->filter;
    }

    /**
     * Get a formatted array of filter selections.
     *
     * @param	mixed[]	$settings
     * @return	mixed[]
     */
    private function getFilterSettings(array $settings)
    {
        $filter = $this->filter();
        $settings = call_user_func_array(array($filter, "content"), $settings);
        return $settings;
    }


    /**
     * Build filter configuration.
     *
     * @return	ILIAS\TMS\Filter\Filters\Filter
     */
    protected function buildFilter()
    {
        $ff = $this->ff;
        $plugin = $this->plugin;
        $tyf = $this->tyf;
        $year = (new \DateTime())->format('Y');
        $dl_filter_default_date = \DateTime::createFromFormat('Y-m-d', $year . '-12-31');

        $filter = $ff->sequence(
            $ff->dateperiod(
                $plugin->txt('crs_starts_in'),
                ''
            ),
            $ff->date(
                $plugin->txt('booking_dl_date_filter'),
                ''
            )->default_date($dl_filter_default_date),
            $ff->date(
                $plugin->txt('storno_dl_date_filter'),
                ''
            )->default_date($dl_filter_default_date),
            $ff->multiselect(
                $plugin->txt('idd_filter'),
                '',
                [self::WITH_IDD => $plugin->txt('with_idd'),
                self::WITHOUT_IDD => $plugin->txt('without_idd')],
                $this->isEduTrackingActive()
            ),
            $ff->multiselect(
                $plugin->txt('idd_filter'),
                '',
                [
                    self::DANGER_CANCEL => $plugin->txt('danger_cancel'),
                    self::OVERBOOKED => $plugin->txt('danger_overbooked'),
                    self::AVAILABLE_PLACES => $plugin->txt('filter_available_places')
                ]
            ),
            $ff->sequence(
                $ff->multiselectsearch(
                    $plugin->txt('filter_venues'),
                    '',
                    $this->venueOptions()
                ),
                $ff->multiselectsearch(
                    $plugin->txt('filter_types'),
                    '',
                    $this->crsTypeOptions()
                ),
                $ff->multiselectsearch(
                    $plugin->txt('filter_categories'),
                    '',
                    $this->crsCategoriesOptions()
                ),
                $ff->multiselectsearch(
                    $plugin->txt('filter_topics'),
                    '',
                    $this->crsTopicOptions()
                ),
                $ff->multiselectsearch(
                    $plugin->txt('filter_templates'),
                    '',
                    $this->templatesOptions()
                ),
                $ff->multiselectsearch(
                    $plugin->txt('filter_accomodations'),
                    '',
                    $this->accomodationOptions(),
                    $this->isAccomodationActive()
                ),
                $ff->multiselectsearch(
                    $plugin->txt('filter_provider'),
                    '',
                    $this->providerOptions()
                ),
                $ff->multiselectsearch(
                    $plugin->txt('edu_programme'),
                    '',
                    $this->eduProgrammeOptions()
                )
            )
        );

        return $this->addMappingWithOptionalFilters($filter);
    }


    /**
     * Add the filter mapping with optional filters
     *
     * @param Filter 	$filter
     *
     * @return Filter
     */
    protected function addMappingWithOptionalFilters($filter)
    {
        $tyf = $this->tyf;
        $types = [
            'period_start' => $tyf->either($tyf->cls("\\DateTime"), $tyf->string()),
            'period_end' => $tyf->either($tyf->cls("\\DateTime"), $tyf->string()),
            'booking_dl_date' => $tyf->either($tyf->cls("\\DateTime"), $tyf->string()),
            'storno_dl_date' => $tyf->either($tyf->cls("\\DateTime"), $tyf->string())
        ];

        if ($this->isEduTrackingActive()) {
            $types['idd'] = $tyf->lst($tyf->int());
        }
        $types = $types + [
            'danger' => $tyf->lst($tyf->int()),
            'venues' => $tyf->lst($tyf->string()),
            'types' => $tyf->lst($tyf->string()),
            'categories' => $tyf->lst($tyf->string()),
            'topics' => $tyf->lst($tyf->string()),
            'templates' => $tyf->lst($tyf->int())
        ];
        if ($this->isAccomodationActive()) {
            $types['accomodations'] = $tyf->lst($tyf->string());
        }
        $types = $types + [
            'providers' => $tyf->lst($tyf->string()),
            'edu_programmes' => $tyf->lst($tyf->string())
        ];

        $map_types = array_keys($types);
        $mapper = function () use ($map_types) {
            $args = func_get_args();
            $ret = [];
            foreach ($map_types as $counter => $type) {
                $ret[$type] = $args[$counter];
            }
            return $ret;
        };
        return $filter->map($mapper, $tyf->dict($types));
    }


    /**
     * Applies the filter settings to the data.
     *
     * @param	array	$settings
     * @return	void
     */
    public function applyFilterToSpace(array $settings)
    {
        $settings = $this->getFilterSettings($settings);
        $this->maybeApplyCrsStartFilter($settings['period_start'], $settings['period_end']);
        $this->maybeApplyBookingDeadlineFilter($settings['booking_dl_date']);
        $this->maybeApplyStornoDeadlineFilter($settings['storno_dl_date']);
        $this->maybeApplyVenueFilter($settings['venues']);
        $this->maybeApplyTypesFilter($settings['types']);
        $this->maybeApplyCategoriesFilter($settings['categories']);
        $this->maybeApplyTopicsFilter($settings['topics']);
        $this->maybeApplyTemplatesFilter($settings['templates']);
        $this->maybeApplyProviderFilter($settings['providers']);
        $this->maybeApplyEduProgrammeFilter($settings['edu_programmes']);

        if ($this->isEduTrackingActive()) {
            $this->maybeApplyIDDFilter($settings['idd']);
        }
        if ($this->isAccomodationActive()) {
            $this->maybeApplyAccomodationsFilter($settings['accomodations']);
        }

        $this->maybeApplyDangerFilter($settings['danger']);
    }

    protected function maybeApplyCrsStartFilter($period_start, $period_end)
    {
        if (!$period_start && !$period_end) {
            return;
        }
        $start = $this->space()->table('crs')->field('begin_date');

        if ($period_start && $period_end) {
            $filter = $start->GE()->date($period_start)
                ->_AND($start->LE()->date($period_end));
        }
        if ($period_start && !$period_end) {
            $filter = $start->GE()->date($period_start);
        }
        if (!$period_start && $period_end) {
            $filter = $start->LE()->date($period_end);
        }
        $filter = $filter->_OR($start->IS_NULL());
        $this->space()->addFilter($filter);
    }

    protected function maybeApplyBookingDeadlineFilter($before)
    {
        if (!$before) {
            return;
        }

        $start = $this->space()->table('crs')->field('begin_date');
        $booking_dl = $this->space()->table('crs')->field('booking_dl_date');

        $booking_dl_exists = $this->pf->_NOT($booking_dl->IS_NULL())->_AND($booking_dl->GT()->str('0001-01-01'));

        $booking_dl_exists_not = $booking_dl->IS_NULL()->_OR($booking_dl->EQ()->str('0001-01-01'));

        $start_exists = $this->pf->_NOT($start->IS_NULL())->_AND($start->GT()->str('0001-01-01'));

        $start_exists_not = $start->IS_NULL()->_OR($start->EQ()->str('0001-01-01'));

        $predicate = $booking_dl_exists->_AND($booking_dl->LE()->date($before))
                                ->_OR($booking_dl_exists_not
                                    ->_AND($start_exists)
                                    ->_AND($start->LE()->date($before)))
                                ->_OR($booking_dl_exists_not->_AND($start_exists_not));
        $this->space()->addFilter($predicate);
    }

    protected function maybeApplyStornoDeadlineFilter($before)
    {
        if (!$before) {
            return;
        }
        $start = $this->space()->table('crs')->field('begin_date');
        $storno_dl = $this->space()->table('crs')->field('storno_dl_date');

        $storno_dl_exists = $this->pf->_NOT($storno_dl->IS_NULL())->_AND($storno_dl->GT()->str('0001-01-01'));

        $storno_dl_exists_not = $storno_dl->IS_NULL()->_OR($storno_dl->EQ()->str('0001-01-01'));

        $start_exists = $this->pf->_NOT($start->IS_NULL())->_AND($start->GT()->str('0001-01-01'));

        $start_exists_not = $start->IS_NULL()->_OR($start->EQ()->str('0001-01-01'));

        $predicate = $storno_dl_exists->_AND($storno_dl->LE()->date($before))
                                ->_OR($storno_dl_exists_not
                                    ->_AND($start_exists)
                                    ->_AND($start->LE()->date($before)))
                                ->_OR($storno_dl_exists_not->_AND($start_exists_not));
        $this->space()->addFilter($predicate);
    }

    protected function maybeApplyVenueFilter(array $venues)
    {
        if (count($venues) == 1 &&
            in_array(self::FREETEXT_VALUE, $venues)
        ) {
            $this->space()->addFilter($this->space()->table('crs')->field('venue_freetext')->EQ($this->pf->int(1)));
        } else {
            if (count($venues) > 0) {
                if (in_array(self::FREETEXT_VALUE, $venues)) {
                    $p_filter = $this->space()->table('crs')->field('venue_freetext')->EQ($this->pf->int(1));
                    $key = array_search(self::FREETEXT_VALUE, $venues);
                    unset($venues[$key]);
                    $p_filter = $p_filter->_OR($this->space()->table('crs')->field('venue')->IN($this->pf->list_string_by_array($venues)));
                } else {
                    $p_filter = $this->space()->table('crs')->field('venue')->IN($this->pf->list_string_by_array($venues));
                }
                $this->space()->addFilter($p_filter);
            }
        }
    }

    protected function maybeApplyTypesFilter(array $types)
    {
        if (count($types) > 0) {
            $this->space()->addFilter($this->space()->table('crs')->field('crs_type')->IN($this->pf->list_string_by_array($types)));
        }
    }


    protected function maybeApplyCategoriesFilter(array $categories)
    {
        if (count($categories) > 0) {
            $categories_space = $this->space->table('categories')->space();
            $categories_f = $categories_space->table('cat_src')->field('list_data');
            $categories_space->addFilter($categories_f->IN($this->pf->list_string_by_array($categories)));
            $this->space->forceRelevant($this->space->table('categories'));
        }
    }

    protected function maybeApplyTopicsFilter(array $topics)
    {
        if (count($topics) > 0) {
            $topics_space = $this->space->table('topics')->space();
            $topics_f = $topics_space->table('top_src')->field('list_data');
            $topics_space->addFilter($topics_f->IN($this->pf->list_string_by_array($topics)));
            $this->space->forceRelevant($this->space->table('topics'));
        }
    }


    protected function maybeApplyTemplatesFilter(array $templates)
    {
        if (count($templates) > 0) {
            $this->space()->addFilter($this->space()->table('copy_mappings')->field('source_id')->IN($this->pf->list_int_by_array($templates)));
        }
    }

    protected function maybeApplyAccomodationsFilter(array $accomodations)
    {
        if (count($accomodations) > 0) {
            $this->space()->addFilter($this->space()->table('crs')->field('accomodation')->IN($this->pf->list_string_by_array($accomodations)));
        }
    }

    protected function maybeApplyProviderFilter(array $providers)
    {
        if (count($providers) == 1 &&
            in_array(self::FREETEXT_VALUE, $providers)
        ) {
            $this->space()->addFilter($this->space()->table('crs')->field('provider_freetext')->EQ($this->pf->int(1)));
        } else {
            if (count($providers) > 0) {
                if (in_array(self::FREETEXT_VALUE, $providers)) {
                    $p_filter = $this->space()->table('crs')->field('provider_freetext')->EQ($this->pf->int(1));
                    $key = array_search(self::FREETEXT_VALUE, $providers);
                    unset($providers[$key]);
                    $p_filter = $p_filter->_OR($this->space()->table('crs')->field('provider')->IN($this->pf->list_string_by_array($providers)));
                } else {
                    $p_filter = $this->space()->table('crs')->field('provider')->IN($this->pf->list_string_by_array($providers));
                }
                $this->space()->addFilter($p_filter);
            }
        }
    }


    protected function maybeApplyEduProgrammeFilter(array $edu_programmes)
    {
        if (count($edu_programmes) > 0) {
            $edu_programme_f = $this->space()->table('crs')->field('edu_programme');
            $predicate = $edu_programme_f->IN($this->pf->list_string_by_array($edu_programmes));
            $this->space()->addFilter($predicate);
        }
    }

    protected function maybeApplyIDDFilter(array $idd_filter_set)
    {
        if (count($idd_filter_set) > 0) {
            $f_idd = $this->space()->table('crs')->field('idd_learning_time');
            $predicates = [];
            if (in_array(self::WITH_IDD, $idd_filter_set)) {
                $predicates[] = $this->pf->_NOT($f_idd->IS_NULL())->_AND($f_idd->GT()->int(0));
            }
            if (in_array(self::WITHOUT_IDD, $idd_filter_set)) {
                $predicates[] = $f_idd->IS_NULL()->_OR($f_idd->EQ($this->pf->int(0)));
            }
            $current = array_shift($predicates);
            while ($next = array_shift($predicates)) {
                $current = $current->_OR($next);
            }
            $this->space()->addFilter($current);
        }
    }


    protected function maybeApplyDangerFilter(array $danger_settings)
    {
        $today = new \DateTime('now');
        if (count($danger_settings) > 0) {
            $predicates = [];
            if (in_array(self::DANGER_CANCEL, $danger_settings)) {
                $predicates[] = $this->dangerCancelPredicate($today);
            }
            if (in_array(self::OVERBOOKED, $danger_settings)) {
                $predicates[] = $this->overbookedPredicate($today);
            }
            if (in_array(self::AVAILABLE_PLACES, $danger_settings)) {
                $predicates[] = $this->availablePlacesPredicate();
            }

            $current = array_shift($predicates);
            while ($next = array_shift($predicates)) {
                $current = $current->_OR($next);
            }
            $this->space()->addFilter($current);
        }
    }

    protected function crsTypeOptions()
    {
        return $this->getDistinct(self::HEAD_COURSE_TABLE, 'crs_type');
    }

    protected function crsTopicOptions()
    {
        return $this->getDistinct(self::HEAD_COURSE_TOPICS_TABLE, 'list_data');
    }

    protected function crsCategoriesOptions()
    {
        return $this->getDistinct(self::HEAD_COURSE_CATEGORIES_TABLE, 'list_data');
    }

    protected function eduProgrammeOptions()
    {
        return $this->getDistinct(self::HEAD_COURSE_TABLE, 'edu_programme');
    }

    protected function venueOptions()
    {
        $query = 'SELECT venue, venue_freetext'
            . '	FROM ' . self::HEAD_COURSE_TABLE
            . '	WHERE venue IS NOT NULL '
            . '		AND venue != \'\''
            . '		AND venue	!= \'-\''
            . '	ORDER BY venue'
        ;

        $res = $this->db->query($query);
        $return = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            if ((bool) $rec["venue_freetext"] === true) {
                $return[self::FREETEXT_VALUE] = $this->plugin->txt("hist_freetext");
            } else {
                $return[$rec["venue"]] = $rec["venue"];
            }
        }
        return $return;
    }

    protected function accomodationOptions()
    {
        return $this->getDistinct(self::HEAD_COURSE_TABLE, 'accomodation');
    }

    protected function providerOptions()
    {
        $query = 'SELECT provider, provider_freetext'
            . '	FROM ' . self::HEAD_COURSE_TABLE
            . '	WHERE provider IS NOT NULL '
            . '		AND provider != \'\''
            . '		AND provider	!= \'-\''
            . '	ORDER BY provider'
        ;

        $res = $this->db->query($query);
        $return = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            if ((bool) $rec["provider_freetext"] === true) {
                $return[self::FREETEXT_VALUE] = $this->plugin->txt("hist_freetext");
            } else {
                $return[$rec["provider"]] = $rec["provider"];
            }
        }
        return $return;
    }

    protected function templatesOptions()
    {
        $query = 'SELECT crs_id, title'
                . '	FROM ' . self::HEAD_COURSE_TABLE
                . '	WHERE is_template = ' . $this->db->quote(1, 'integer');
        $res = $this->db->query($query);
        $return = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $return[$rec['crs_id']] = $rec['title'];
        }
        return $return;
    }

    protected function getDistinct($table, $column)
    {
        $res = $this->db->query(
            'SELECT DISTINCT ' . $column
                . '	FROM ' . $table
                . '	WHERE ' . $column . ' IS NOT NULL '
                . '		AND ' . $column . ' != \'\''
                . '		AND ' . $column . '	!= \'-\''
                . '	ORDER BY ' . $column
        );
        $return = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $return[$rec[$column]] = $rec[$column];
        }
        return $return;
    }

    /**
     * Get the table space the report uses.
     *
     * @return TableRelations\AbstractTable
     */
    public function space()
    {
        if (!$this->space) {
            $this->space = $this->buildSpace();
        }
        return $this->space;
    }

    /**
     * @return TableRelations\AbstractTable
     */
    protected function buildSpace()
    {
        $crs_data = $this->tf->Table(self::HEAD_COURSE_TABLE, 'crs')
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('title'))
                        ->addField($this->tf->field('crs_type'))
                        ->addField($this->tf->field('venue'))
                        ->addField($this->tf->field('provider'))
                        ->addField($this->tf->field('begin_date'))
                        ->addField($this->tf->field('end_date'))
                        ->addField($this->tf->field('tut'))
                        ->addField($this->tf->field('edu_programme'))
                        ->addField($this->tf->field('booking_dl_date'))
                        ->addField($this->tf->field('storno_dl_date'))
                        ->addField($this->tf->field('venue_freetext'))
                        ->addField($this->tf->field('venue_from_course'))
                        ->addField($this->tf->field('provider_freetext'));
        if ($this->isEduTrackingActive()) {
            $crs_data = $crs_data->addField($this->tf->field('idd_learning_time'));
        }
        if ($this->isAccomodationActive()) {
            $crs_data = $crs_data->addField($this->tf->field('accomodation'));
        }


        $crs_data = $crs_data->addField($this->tf->field('max_members'))
                        ->addField($this->tf->field('min_members'))
                        ->addField($this->tf->field('is_template'));

        $crs_data->addConstraint(
            $crs_data->field('is_template')
                            ->EQ($this->pf->int(0))
            ->_OR($crs_data->field('is_template')->IS_NULL())
        );

        $tutors_src = $this->tf->Table(self::HEAD_COURSE_TUT_TABLE, 'tut_src')
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('list_data'));
        $tutors_data = $this->tf->Table('usr_data', 'tr_names')
                        ->addField($this->tf->field('usr_id'))
                        ->addField($this->tf->field('firstname'))
                        ->addField($this->tf->field('lastname'));
        $tutors_space = $this->tf->TableSpace()
                        ->addTablePrimary($tutors_src)
                        ->addTablePrimary($tutors_data);
        $tutors_space = $tutors_space->setRootTable($tutors_space->table('tut_src'))
                        ->request($tutors_space->table('tut_src')->field('crs_id'), 'crs_id')
                        ->request($this->tf->groupConcat('tutors', $this->tf->concat('tutor_names', $tutors_data->field('firstname'), $tutors_data->field('lastname'), ' '), ', '))
                        ->addDependency($this->tf->TableJoin($tutors_data, $tutors_src, $tutors_data->field('usr_id')->EQ($tutors_src->field('list_data'))))
                        ->groupBy($tutors_src->field('crs_id'));
        $tutors = $this->tf->DerivedTable($tutors_space, 'tut');

        $topics_src = $this->tf->Table(self::HEAD_COURSE_TOPICS_TABLE, 'top_src')
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('list_data'));
        $topics_space = $this->tf->TableSpace()
                        ->addTablePrimary($topics_src)
                        ->setRootTable($topics_src)
                        ->groupBy($topics_src->field('crs_id'));
        $topics_space->request($topics_src->field('crs_id'));
        $topics = $this->tf->DerivedTable($topics_space, 'topics');

        $categories_src = $this->tf->Table(self::HEAD_COURSE_CATEGORIES_TABLE, 'cat_src')
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('list_data'));
        $categories_space = $this->tf->TableSpace()
                        ->addTablePrimary($categories_src)
                        ->setRootTable($categories_src)
                        ->groupBy($categories_src->field('crs_id'));
        $categories_space->request($categories_src->field('crs_id'));
        $categories = $this->tf->DerivedTable($categories_space, 'categories');



        $bookings_src = $this->tf->Table(self::HEAD_USERCOURSE_TABLE, 'usrcrs')
                        ->addField($this->tf->field('usr_id'))
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('booking_status'));
        $bookings_src = $bookings_src
                        ->addConstraint($bookings_src->field('booking_status')->IN(
                            $this->pf->list_string_by_array(
                                ['participant'
                                ,
                                'waiting']
                            )
                        ));
        $bookings_space = $this->tf->TableSpace()
                        ->addTablePrimary($bookings_src)
                        ->setRootTable($bookings_src)
                        ->request($bookings_src->field('crs_id'))
                        ->request($this->tf->sum(
                            'cnt_booked',
                            $this->tf->ifThenElse(
                                '',
                                $bookings_src->field('booking_status')->EQ($this->pf->str('participant')),
                                $this->tf->constInt('', 1),
                                $this->tf->constInt('', 0)
                            )
                        ))
                        ->request($this->tf->sum(
                            'cnt_waiting',
                            $this->tf->ifThenElse(
                                '',
                                $bookings_src->field('booking_status')->EQ($this->pf->str('waiting')),
                                $this->tf->constInt('', 1),
                                $this->tf->constInt('', 0)
                            )
                        ))
                        ->request($this->tf->countAll('cnt_members'))
                        ->groupBy($bookings_src->field('crs_id'));

        $bookings = $this->tf->DerivedTable($bookings_space, 'bookings');

        $nights_booking = $this->tf->Table(self::HEAD_USERCOURSE_TABLE, 'nights_booking')
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('usr_id'))
                        ->addField($this->tf->field('booking_status'));

        $nights_booking->addConstraint($nights_booking->field('booking_status')->IN(
            $this->pf->list_string_by_array(
                ['participant'
                ,
                'waiting']
            )
        ));

        $nights_src_total = $this->tf->Table(self::HEAD_USERCOURSE_NIGHTS_TABLE, 'nights_src_total')
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('usr_id'))
                        ->addField($this->tf->field('list_data'));
        $nights_space_total = $this->tf->TableSpace()
                        ->addTablePrimary($nights_src_total)
                        ->addTablePrimary($nights_booking)
                        ->setRootTable($nights_src_total)
                        ->addDependency(
                            $this->tf->TableJoin(
                                $nights_src_total,
                                $nights_booking,
                                $nights_src_total->field('crs_id')->EQ($nights_booking->field('crs_id'))
                                ->_AND($nights_src_total->field('usr_id')->EQ($nights_booking->field('usr_id')))
                            )
                        );
        $nights_space_total->request($nights_src_total->field('crs_id'))
                        ->request($this->tf->countAll('nights'), 'nights_count_all')
                        ->groupBy($nights_src_total->field('crs_id'));
        $nights_total = $this->tf->DerivedTable($nights_space_total, 'nights_total');


        $nights_src_resolved = $this->tf->Table(self::HEAD_USERCOURSE_NIGHTS_TABLE, 'nights_src_resolved')
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('usr_id'))
                        ->addField($this->tf->field('list_data'));
        $nights_space_resolved = $this->tf->TableSpace()
                        ->addTablePrimary($nights_src_resolved)
                        ->addTablePrimary($nights_booking)
                        ->setRootTable($nights_src_resolved)
                        ->addDependency(
                            $this->tf->TableJoin(
                                $nights_src_resolved,
                                $nights_booking,
                                $nights_src_resolved->field('crs_id')->EQ($nights_booking->field('crs_id'))
                                ->_AND($nights_src_resolved->field('usr_id')->EQ($nights_booking->field('usr_id')))
                            )
                        );
        $nights_space_resolved->request($nights_src_resolved->field('crs_id'))
                        ->request($nights_src_resolved->field('list_data'), 'night')
                        ->request($this->tf->countAll('cnt'))
                        ->groupBy($nights_src_resolved->field('crs_id'))
                        ->groupBy($nights_src_resolved->field('list_data'))
                        ->orderBy(['crs_id','night'], 'asc');


        $nights_resolved = $this->tf->DerivedTable($nights_space_resolved, 'nights_resolved');

        $nights_resolved_count_space = $this->tf->TableSpace()
                                    ->addTablePrimary($nights_resolved)
                                    ->setRootTable($nights_resolved)
                                    ->request($nights_resolved->field('crs_id'))
                                    ->request($this->tf->groupConcat(
                                        'nights_count_resolved',
                                        $this->tf->concat(
                                            'nights',
                                            $this->tf->dateFormat('', $nights_resolved->field('night')),
                                            $nights_resolved->field('cnt'),
                                            ':'
                                        ),
                                        ', ',
                                        $nights_resolved->field('night')
                                    ))
                                    ->groupBy($nights_resolved->field('crs_id'));

        $nights_resolved_count = $this->tf->DerivedTable($nights_resolved_count_space, 'nights_resolved_count');

        $copy_mappings = $this->tf->Table('copy_mappings', 'copy_mappings')
                            ->addField($this->tf->field('obj_id'))
                            ->addField($this->tf->field('source_id'));

        $object_reference = $this->tf->Table('object_reference', 'object_reference')
                            ->addField($this->tf->field('obj_id'))
                            ->addField($this->tf->field('ref_id'))
                            ->addField($this->tf->field('deleted'));

        $space = $this->tf->TableSpace()
            ->addTablePrimary($crs_data)
            ->addTableSecondary($tutors)
            ->addTableSecondary($bookings)
            ->addTableSecondary($topics)
            ->addTableSecondary($categories)
            ->addTableSecondary($nights_total)
            ->addTableSecondary($nights_resolved_count)
            ->addTableSecondary($copy_mappings)
            ->addTableSecondary($object_reference)
            ->setRootTable($crs_data)
            ->addDependency(
                $this->tf->TableLeftJoin($crs_data, $bookings, $bookings->field('crs_id')->EQ($crs_data->field('crs_id')))
            )
            ->addDependency(
                $this->tf->TableLeftJoin($crs_data, $tutors, $crs_data->field('crs_id')->EQ($tutors->field('crs_id')))
            )
            ->addDependency(
                $this->tf->TableJoin($crs_data, $topics, $crs_data->field('crs_id')->EQ($topics->field('crs_id')))
            )
            ->addDependency(
                $this->tf->TableJoin($crs_data, $categories, $crs_data->field('crs_id')->EQ($categories->field('crs_id')))
            )
            ->addDependency(
                $this->tf->TableLeftJoin($crs_data, $nights_total, $nights_total->field('crs_id')->EQ($crs_data->field('crs_id')))
            )
            ->addDependency(
                $this->tf->TableLeftJoin($crs_data, $nights_resolved_count, $nights_resolved_count->field('crs_id')->EQ($crs_data->field('crs_id')))
            )
            ->addDependency(
                $this->tf->TableJoin($crs_data, $copy_mappings, $copy_mappings->field('obj_id')->EQ($crs_data->field('crs_id')))
            )
            ->addDependency(
                $this->tf->TableLeftJoin($crs_data, $object_reference, $object_reference->field('obj_id')
                    ->EQ($crs_data->field('crs_id'))->_AND($object_reference->field('deleted')->IS_NULL()))
            )
            ->addFilter($crs_data->field('end_date')->GE()->date(new \DateTime()));

        $space = $this->possiblyConstrainCourses($space);
        $space = $this->possiblyConstrainCoursesByLocalRoles($space);
        return $space;
    }

    protected function possiblyConstrainCourses(TableRelations\Tables\TableSpace $space)
    {
        if ($this->settings->isGlobal()) {
            return $space;
        }
        $relevant = $this->relevantTrainingIds();
        if (count($relevant) === 0) {
            return $space->addFilter($this->pf->_FALSE());
        }
        return $space
                ->addFilter(
                    $space
                        ->table('crs')
                        ->field('crs_id')
                    ->IN($this->pf->list_int_by_array($relevant))
                );
    }

    protected function possiblyConstrainCoursesByLocalRoles(TableRelations\Tables\TableSpace $space)
    {
        $set_local_roles = $this->settings->getLocalRoles();
        if (count($set_local_roles) === 0) {
            return $space;
        }

        $query = 'SELECT DISTINCT crs_id FROM ' . self::HEAD_USERCOURSE_ROLES . PHP_EOL
            . 'WHERE usr_id = ' . $this->current_user_id . PHP_EOL
            . 'AND list_data in (\''
            . implode('\',\'', $set_local_roles)
            . '\')';
        $res = $this->db->query($query);

        $relevant = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $relevant[] = (int) $row['crs_id'];
        }
        if (count($relevant) === 0) {
            return $space->addFilter($this->pf->_FALSE());
        }
        return $space->addFilter(
            $space->table('crs')->field('crs_id')->IN($this->pf->list_int_by_array($relevant))
        );
    }


    /**
     * @return	TableRelations\SqlQueryInterpreter
     */
    protected function interpreter()
    {
        if (!$this->interpreter) {
            $this->interpreter = new TableRelations\SqlQueryInterpreter(
                new Filter\SqlPredicateInterpreter($this->db),
                $this->pf,
                $this->db
            );
        }
        return $this->interpreter;
    }

    /**
     * Configures the table that displays the reports data.
     *
     * @param	SelectableReportTableGUI	$table
     * @return	void
     */
    public function configureTable(\SelectableReportTableGUI $table)
    {
        $today = new \DateTime('now');
        $space = $this->space();
        $table->setRowTemplate('tpl.report_row.html', $this->plugin->getDirectory());
        $table
            ->defineFieldColumn(
                $this->plugin->txt('title'),
                'title',
                ['title' => $space->table('crs')->field('title')]
            )
            ->defineFieldColumn(
                $this->plugin->txt('crs_type'),
                'crs_type',
                ['crs_type' => $space->table('crs')->field('crs_type')],
                true,
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('crs_date'),
                'crs_date',
                ['begin_date' => $space->table('crs')->field('begin_date')],
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('crs_date_end'),
                'crs_date_end',
                ['end_date' => $space->table('crs')->field('end_date')],
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('venue'),
                'venue',
                ['venue' => $space->table('crs')->field('venue')],
                true,
                true
            );

        if ($this->isAccomodationActive()) {
            $table = $table
            ->defineFieldColumn(
                $this->plugin->txt('accomodation'),
                'accomodation',
                ['accomodation' => $this->tf->ifThenElse(
                    'accomodation',
                    $space->table('crs')->field('venue_from_course')->EQ()->int(1),
                    $space->table('crs')->field('venue'),
                    $space->table('crs')->field('accomodation')
                )],
                true,
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('nights_resolved_count'),
                'nights_resolved_count',
                ['nights_resolved_count' => $space->table('nights_resolved_count')->field('nights_count_resolved')],
                true,
                false
            )
            ->defineFieldColumn(
                $this->plugin->txt('nights_total'),
                'nights_total',
                ['nights_total' => $space->table('nights_total')->field('nights_count_all')],
                true,
                true
            );
        }

        $table = $table
            ->defineFieldColumn(
                $this->plugin->txt('provider'),
                'provider',
                ['provider' => $space->table('crs')->field('provider')],
                true,
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('trainer'),
                'tutors',
                ['tutors' => $space->table('tut')->field('tutors')],
                true,
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('min_members'),
                'min_members',
                ['min_members' => $space->table('crs')->field('min_members')],
                true,
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('max_members'),
                'max_members',
                ['max_members' => $space->table('crs')->field('max_members')],
                true,
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('cnt_booked'),
                'count_booked',
                ['count_booked' => $space->table('bookings')->field('cnt_booked')],
                true,
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('cnt_free'),
                'count_free',
                ['count_free' =>
                    $this->tf->ifThenElse(
                        'hasmaxmember',
                        $space->table('crs')->field('max_members')->GT()->int(0),
                        $this->tf->minus(
                            '',
                            $space->table('crs')->field('max_members'),

                            //$space->table('bookings')->field('cnt_booked')
                            $this->tf->ifThenElse(
                                'has_bookings',
                                $this->pf->_NOT($space->table('bookings')->field('cnt_booked')->IS_NULL()),
                                $space->table('bookings')->field('cnt_booked'),
                                $this->tf->constInt('', 0)
                            )
                        ),
                        $this->tf->constString('-')
                    )
                ],
                true,
                true,
                false,
                true,
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('cnt_waiting'),
                'count_waiting',
                ['count_waiting' => $space->table('bookings')->field('cnt_waiting')],
                true,
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('cnt_members'),
                'count_members',
                ['count_members' => $space->table('bookings')->field('cnt_members')],
                true,
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('booking_dl_date'),
                'booking_dl_date',
                ['booking_dl_date' => $space->table('crs')->field('booking_dl_date')],
                true,
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('storno_dl_date'),
                'storno_dl_date',
                ['storno_dl_date' => $space->table('crs')->field('storno_dl_date')],
                true,
                true
            );

        if ($this->isEduTrackingActive()) {
            $table = $table->defineFieldColumn(
                $this->plugin->txt('idd_learning_time'),
                'idd_learning_time',
                ['idd_learning_time' => $space->table('crs')->field('idd_learning_time')],
                true,
                true
            );
        }

        $table = $table->defineFieldColumn(
            $this->plugin->txt('danger_cancel'),
            'danger_cancel',
            ['danger_cancel' => $this->tf->ifThenElse(
                'danger_cancel',
                $this->dangerCancelPredicate($today),
                $this->tf->constString('', 'X'),
                $this->tf->constString('')
            )],
            true,
            true
        )
            ->defineFieldColumn(
                $this->plugin->txt('overbooked'),
                'overbooked',
                ['overbooked' => $this->tf->ifThenElse(
                    'overbooked',
                    $this->overbookedPredicate($today),
                    $this->tf->constString('', 'X'),
                    $this->tf->constString('')
                )],
                true,
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('actions'),
                'actions',
                ['ref_id' => $space->table('object_reference')->field('ref_id')],
                false,
                false,
                true
            );

        $table->setDefaultSelectedColumns([
            'crs_type'
            ,'count_members'
            ,'danger_cancel'
            ,'overbooked'
            ,'crs_date'
            ,'crs_date_end'
        ]);
        $table->setDefaultOrderColumn('crs_date', \SelectableReportTableGUI::ORDER_ASC);
        $table->prepareTableAndSetRelevantFields($space);
        $this->space = $space;
        return $table;
    }

    private function dangerCancelPredicate(\DateTime $today)
    {
        $crs = $this->space()->table('crs');
        $min_members = $crs->field('min_members');
        $bookings = $this->space()->table('bookings');
        return $this->pf->_NOT($min_members->IS_NULL())
                    ->_AND($min_members->GT()->int(0))
                    ->_AND(
                        $min_members->GT($bookings->field('cnt_booked'))
                            ->_OR($bookings->field('cnt_booked')->IS_NULL())
                    )
                    ->_AND($crs->field('booking_dl_date')->LT()->date($today));
    }

    private function overbookedPredicate(\DateTime $today)
    {
        $crs = $this->space()->table('crs');
        $max_members = $crs->field('max_members');
        $bookings = $this->space()->table('bookings');
        return $this->pf->_NOT($max_members->IS_NULL())
            ->_AND($bookings->field('cnt_waiting')->GT()->int(0));
    }

    private function availablePlacesPredicate()
    {
        $crs = $this->space()->table('crs');
        $bookings = $this->space()->table('bookings');
        $max_members = $crs->field('max_members');
        $booked = $bookings->field('cnt_booked');

        return $this->pf->_ANY(
            $this->pf->_OR(
                $max_members->IS_NULL(),
                $max_members->EQ()->int(0)
                    ),
            $this->pf->_AND(
                $max_members->GT()->int(0),
                $booked->LT($max_members)
                    ),
            $booked->IS_NULL()
                );
    }

    /**
     * Transforms minutes to showable time string
     *
     * @param int 	$minutes
     *
     * @return string
     */
    protected function minutesToTimeString(int $minutes)
    {
        $hours = (string) floor($minutes / 60);
        $minutes = (string) ($minutes - $hours * 60);

        return str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT);
    }

    protected function relevantTrainingIds()
    {
        $parent = $this->o_d->getParentOfObjectOfType($this->object, 'cat');
        if ($parent === null) {
            $parent = $this->o_d->getParentOfObjectOfType($this->object, 'root');
        }
        return $this->o_d->getAllChildrenIdsByTypeOfObject($parent, 'crs');
    }

    /**
     * Checks the edu tracking plugin is active
     *
     * @return bool
     */
    protected function isEduTrackingActive()
    {
        require_once("Services/Component/classes/class.ilPluginAdmin.php");
        return \ilPluginAdmin::isPluginActive("xetr");
    }

    /**
     * Checks if the accomodation-plugin is active
     *
     * @return bool
     */
    public function isAccomodationActive()
    {
        require_once("Services/Component/classes/class.ilPluginAdmin.php");
        return \ilPluginAdmin::isPluginActive("xoac");
    }
}
