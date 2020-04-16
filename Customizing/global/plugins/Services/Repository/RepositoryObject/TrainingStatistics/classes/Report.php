<?php

namespace CaT\Plugins\TrainingStatistics;

use ILIAS\TMS\Filter;
use ILIAS\TMS\TableRelations;
use ILIAS\TMS\ReportUtilities\TreeObjectDiscovery;
use CaT\Plugins\CourseMember\LPOptions\ilDB as LPODB;

class Report
{
    const HEAD_COURSE_TABLE = "hhd_crs";
    const HEAD_COURSE_TOPICS_TABLE = "hhd_crs_topics";
    const HEAD_COURSE_CATEGORIES_TABLE = "hhd_crs_categories";
    const HEAD_USERCOURSE_TABLE = "hhd_usrcrs";

    const AGGREGATE_ID_CRS_TYPE = 'crs_type';
    const AGGREGATE_ID_EDU_PROGRAMME = 'edu_programme';
    const AGGREGATE_ID_CRS_TOPICS = 'crs_topics';
    const AGGREGATE_ID_CATEGORIES = 'categories';


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

    protected $relevant_training_ids = null;

    public static $aggregate_options =
        [self::AGGREGATE_ID_CRS_TYPE
        ,self::AGGREGATE_ID_EDU_PROGRAMME
        ,self::AGGREGATE_ID_CRS_TOPICS
        ,self::AGGREGATE_ID_CATEGORIES];

    public function __construct(
        \ilTrainingStatisticsPlugin $plugin,
        \ilDBInterface $db,
        TreeObjectDiscovery $o_d,
        Settings\Settings $settings,
        \ilObject $object
    ) {
        $this->plugin = $plugin;

        $this->gf = new TableRelations\GraphFactory();
        $this->pf = new Filter\PredicateFactory();
        $this->tf = new TableRelations\TableFactory($this->pf, $this->gf);
        $this->tyf = new Filter\TypeFactory();
        $this->ff = new Filter\FilterFactory($this->pf, $this->tyf);

        $this->db = $db;

        $this->action_links = $action_links;
        $this->o_d = $o_d;
        $this->settings = $settings;
        $this->object = $object;
    }

    /**
     * Get the data for the report.
     *
     * @return	array
     */
    public function fetchData()
    {
        return $this->fetchDataBySpace($this->space());
    }

    public function fetchOverviewData()
    {
        return $this->fetchDataBySpace($this->overviewSpace());
    }

    protected function fetchDataBySpace(TableRelations\Tables\TableSpace $space)
    {
        $res = $this->db->query($this->interpreter()->getSql($space->query()));
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
        return $this->postprocessRowCommon($row);
    }


    /**
     * Postprocess a row from the database to display it as anything.
     *
     * @param	array	$row
     * @return	array
     */
    protected function postprocessRowCommon(array $row)
    {
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
        return $ff->sequence(
            $ff->dateperiod(
                $plugin->txt('crs_date'),
                ''
            ),
            $ff->sequence(
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
                    $plugin->txt('edu_programme'),
                    '',
                    $this->eduProgrammeOptions()
                )
            )
        )->map(
            function (
                $period_start,
                $period_end,
                $types,
                $categories,
                $topics,
                $templates,
                $edu_programmes
            ) {
                return ['period_start' => $period_start,
                            'period_end' => $period_end,
                            'types' => $types,
                            'categories' => $categories,
                            'topics' => $topics,
                            'templates' => $templates,
                            'edu_programmes' => $edu_programmes
                            ];
            },
            $tyf->dict(
                [	'period_start' => $tyf->cls("\\DateTime"),
                        'period_end' => $tyf->cls("\\DateTime"),
                        'types' => $tyf->lst($tyf->string()),
                        'categories' => $tyf->lst($tyf->string()),
                        'topics' => $tyf->lst($tyf->string()),
                        'templates' => $tyf->lst($tyf->int()),
                        'edu_programmes' => $tyf->lst($tyf->string())]
            )
        );
        ;
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
        $this->maybeApplyCrsDatesFilter($settings['period_start'], $settings['period_end']);
        $this->maybeApplyTypesFilter($settings['types']);
        $this->maybeApplyCategoriesFilter($settings['categories']);
        $this->maybeApplyTopicsFilter($settings['topics']);
        $this->maybeApplyTemplatesFilter($settings['templates']);
        $this->maybeApplyEduProgrammeFilter($settings['edu_programmes']);
    }

    protected function maybeApplyCrsDatesFilter(\DateTime $period_start, \DateTime $period_end)
    {
        $this->maybeApplyCrsDatesFilterToSpace($period_start, $period_end, $this->space()->table('courses')->space());
        $this->maybeApplyCrsDatesFilterToSpace($period_start, $period_end, $this->overviewSpace());
    }

    protected function maybeApplyCrsDatesFilterToSpace(
        \DateTime $period_start,
        \DateTime $period_end,
        TableRelations\Tables\TableSpace $space
    ) {
        $begin_date = $period_start->format('Y-m-d');
        $end_date = $period_end->format('Y-m-d');

        $begin_date_f = $space->table('crs')->field('begin_date');
        $end_date_f = $space->table('crs')->field('end_date');

        $booking_date_f = $space->table('usrcrs')->field('booking_date');
        $participation_date_f = $space->table('usrcrs')->field('ps_acquired_date');

        $begin_date_not_null = $begin_date_f->IS_NULL()->_NOT();
        $end_date_not_null = $end_date_f->IS_NULL()->_NOT();

        $booking_date_not_null = $booking_date_f->IS_NULL()->_NOT();
        $participation_date_not_null = $participation_date_f->IS_NULL()->_NOT();

        $is_self_learn = $begin_date_f->IS_NULL()->_OR($begin_date_f->EQ($this->pf->str('0001-01-01')));
        $is_not_self_learn = $is_self_learn->_NOT();

        $predicate_not_self_learn =
            $is_not_self_learn->_AND(
                $this->pf->_ALL(
                    $begin_date_not_null,
                    $end_date_not_null,
                    $begin_date_f->LE()->str($end_date),
                    $end_date_f->GE()->str($begin_date)
                )->_OR(
                    $this->pf->_ALL(
                        $begin_date_not_null,
                        $begin_date_f->LE()->str($end_date),
                        $begin_date_f->GE()->str($begin_date)
                    )
                )
            );

        $predicate_self_learn =
            $is_self_learn->_AND(
                $this->pf->_ALL(
                    $booking_date_not_null,
                    $participation_date_not_null,
                    $booking_date_f->LE()->str($end_date),
                    $participation_date_f->GE()->str($begin_date)
                )
                ->_OR(
                    $this->pf->_ALL(
                        $booking_date_not_null,
                        $booking_date_f->LE()->str($end_date),
                        $booking_date_f->GE()->str($begin_date)
                    )
                )
            );

        $space->addFilter($predicate_self_learn->_OR($predicate_not_self_learn));
    }

    protected function maybeApplyTypesFilter(array $types)
    {
        if (count($types) > 0) {
            $this->maybeApplyTypesFilterToSpace($types, $this->space()->table('courses')->space());
            $this->maybeApplyTypesFilterToSpace($types, $this->overviewSpace());
        }
    }

    protected function maybeApplyTypesFilterToSpace(
        array $types,
        TableRelations\Tables\TableSpace $space
    ) {
        $space	->addFilter(
            $space->table('crs')
                    ->field('crs_type')->IN($this->pf->list_string_by_array($types))
        );
    }

    protected function maybeApplyCategoriesFilter(array $categories)
    {
        if (count($categories) > 0) {
            $this->maybeApplyCategoriesFilterToSpace($categories, $this->space()->table('courses')->space());
            $this->maybeApplyCategoriesFilterToSpace($categories, $this->overviewSpace());
        }
    }

    protected function maybeApplyCategoriesFilterToSpace(
        array $categories,
        TableRelations\Tables\TableSpace $space
    ) {
        $space	->table('categories_filter')
                ->space()->addFilter(
                    $space
                        ->table('categories_filter')
                        ->space()
                        ->table('categories')
                    ->field('list_data')->IN($this->pf->list_string_by_array($categories))
                );
        $space->forceRelevant($space->table('categories_filter'));
    }

    protected function maybeApplyTopicsFilter(array $topics)
    {
        if (count($topics) > 0) {
            $this->maybeApplyTopicsFilterToSpace($topics, $this->space()->table('courses')->space());
            $this->maybeApplyTopicsFilterToSpace($topics, $this->overviewSpace());
        }
    }


    protected function maybeApplyTopicsFilterToSpace(
        array $topics,
        TableRelations\Tables\TableSpace $space
    ) {
        $space	->table('topics_filter')
                ->space()->addFilter(
                    $space
                        ->table('topics_filter')
                        ->space()
                        ->table('topics')
                    ->field('list_data')->IN($this->pf->list_string_by_array($topics))
                );
        $space->forceRelevant($space->table('topics_filter'));
    }

    protected function maybeApplyTemplatesFilter(array $templates)
    {
        if (count($templates) > 0) {
            $this->maybeApplyTemplatesFilteToSpace($templates, $this->space()->table('courses')->space());
            $this->maybeApplyTemplatesFilteToSpace($templates, $this->overviewSpace());
        }
    }

    protected function maybeApplyTemplatesFilteToSpace(
        array $templates,
        TableRelations\Tables\TableSpace $space
    ) {
        $space->addFilter($space->table('copy_mappings')->field('source_id')->IN($this->pf->list_int_by_array($templates)));
    }

    protected function maybeApplyEduProgrammeFilter(array $edu_programmes)
    {
        if (count($edu_programmes) > 0) {
            $this->maybeApplyEduProgrammeFilterToSpace($edu_programmes, $this->space()->table('courses')->space());
            $this->maybeApplyEduProgrammeFilterToSpace($edu_programmes, $this->overviewSpace());
        }
    }

    protected function maybeApplyEduProgrammeFilterToSpace(
        array $edu_programmes,
        TableRelations\Tables\TableSpace $space
    ) {
        $space	->addFilter(
            $space->table('crs')
                    ->field('edu_programme')->IN($this->pf->list_string_by_array($edu_programmes))
        );
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

    protected function templatesOptions()
    {
        $relevant_training_ids = $this->relevantTrainingIds();
        if (count($relevant_training_ids) === 0) {
            return [];
        }
        $query = 'SELECT crs_id, title'
                . '	FROM ' . self::HEAD_COURSE_TABLE
                . '	WHERE is_template = ' . $this->db->quote(1, 'integer')
                . '		AND ' . $this->db->in('crs_id', $relevant_training_ids, false, 'integer');
        $res = $this->db->query($query);
        $return = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $return[(int) $rec['crs_id']] = $rec['title'];
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
     *
     */
    public function overviewSpace()
    {
        if (!$this->overview_space) {
            $this->overview_space = $this->buildSpaceCourses();
            $this->overview_space->setRootTable($this->overview_space->table('crs'));
        }
        return $this->overview_space;
    }

    /**
     * Get the table space the report uses.
     *
     * @return TableRelations\AbstractTable
     */
    public function space()
    {
        if (!$this->space) {
            $this->space = $this->getGroupedSpace($this->buildSpaceCourses());
        }
        return $this->space;
    }

    /**
     * @return TableRelations\AbstractTable
     */
    protected function buildSpaceCourses()
    {
        $crs_data = $this->tf->Table(self::HEAD_COURSE_TABLE, 'crs')
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('crs_type'))
                        ->addField($this->tf->field('begin_date'))
                        ->addField($this->tf->field('end_date'))
                        ->addField($this->tf->field('edu_programme'))
                        ->addField($this->tf->field('is_template'));
        $crs_data->addConstraint(
            $crs_data->field('is_template')
                            ->EQ($this->pf->int(0))
            ->_OR($crs_data->field('is_template')->IS_NULL())
        );

        $topics = $this->tf->Table(self::HEAD_COURSE_TOPICS_TABLE, 'topics')
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('list_data'));
        $topics_filter_space = $this->tf->TableSpace()
                            ->addTablePrimary($topics)
                            ->setRootTable($topics)
                            ->request($topics->field('crs_id'), 'crs_id')
                            ->groupBy($topics->field('crs_id'));
        $topics_filter = $this->tf->DerivedTable($topics_filter_space, 'topics_filter');


        $categories = $this->tf->Table(self::HEAD_COURSE_CATEGORIES_TABLE, 'categories')
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('list_data'));
        $categories_filter_space = $this->tf->TableSpace()
                            ->addTablePrimary($categories)
                            ->setRootTable($categories)
                            ->request($categories->field('crs_id'), 'crs_id')
                            ->groupBy($categories->field('crs_id'));
        $categories_filter = $this->tf->DerivedTable($categories_filter_space, 'categories_filter');


        $bookings = $this->tf->Table(self::HEAD_USERCOURSE_TABLE, 'usrcrs')
                        ->addField($this->tf->field('usr_id'))
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('booking_status'))
                        ->addField($this->tf->field('participation_status'))
                        ->addField($this->tf->field('booking_date'))
                        ->addField($this->tf->field('ps_acquired_date'));
        $bookings = $bookings
                        ->addConstraint($bookings->field('booking_status')->IN(
                            $this->pf->list_string_by_array(
                                [
                                    'participant',
                                    'waiting',
                                    'waiting_cancelled'
                                ]
                            )
                        ));


        $copy_mappings = $this->tf->Table('copy_mappings', 'copy_mappings')
                            ->addField($this->tf->field('obj_id'))
                            ->addField($this->tf->field('source_id'));

        $space = $this->tf->TableSpace()
                        ->addTablePrimary($crs_data)
                        ->addTablePrimary($bookings)
                        ->addTableSecondary($copy_mappings)
                        ->addTableSecondary($topics_filter)
                        ->addTableSecondary($categories_filter)
                        ->setRootTable($crs_data)
                        ->addDependency(
                            $this->tf->TableLeftJoin($crs_data, $bookings, $bookings->field('crs_id')->EQ($crs_data->field('crs_id')))
                        )
                        ->addDependency(
                            $this->tf->TableLeftJoin($crs_data, $copy_mappings, $copy_mappings->field('obj_id')->EQ($crs_data->field('crs_id')))
                        )
                        ->addDependency(
                            $this->tf->TableJoin($crs_data, $topics_filter, $crs_data->field('crs_id')->EQ($topics_filter->field('crs_id')))
                        )
                        ->addDependency(
                            $this->tf->TableJoin($crs_data, $categories_filter, $crs_data->field('crs_id')->EQ($categories_filter->field('crs_id')))
                        )
                        ;
        return $this->possiblyConstrainCourses($space);
    }



    protected function getGroupedSpace(TableRelations\Tables\TableSpace $space_courses)
    {
        $space_courses->request($space_courses->table('crs')->field('crs_id'), 'crs_id')
                    ->request($space_courses->table('usrcrs')->field('usr_id'), 'usr_id')
                    ->request($space_courses->table('usrcrs')->field('booking_status'), 'booking_status')
                    ->request($space_courses->table('usrcrs')->field('participation_status'), 'participation_status');
        $table_courses = $this->tf->DerivedTable($space_courses, 'courses');
        $space = $this->tf->TableSpace()
                        ->addTablePrimary($table_courses);
        switch ($this->settings->aggregateId()) {
            case Settings\Settings::AGGREGATE_ID_NONE:
                $this->addVanillaCrsTableTo($space);
                break;
            case self::AGGREGATE_ID_CRS_TYPE:
                $this->addCrsTypeEduProgrammeTableTo($space);
                $space->groupBy($space->table('crs_type_edu_programme')->field('crs_type'));
                break;
            case self::AGGREGATE_ID_EDU_PROGRAMME:
                $this->addCrsTypeEduProgrammeTableTo($space);
                $space->groupBy($space->table('crs_type_edu_programme')->field('edu_programme'));
                break;
            case self::AGGREGATE_ID_CRS_TOPICS:
                $this->addTopicsTableToSpace($space);
                $space->groupBy($space->table('topics')->field('list_data'));
                break;
            case self::AGGREGATE_ID_CATEGORIES:
                $this->addCategoriesTableToSpace($space);
                $space->groupBy($space->table('categories')->field('list_data'));
                break;
            default:
                throw new Exception('unknown aggregate id');
        }
        return $space;
    }

    protected function addVanillaCrsTableTo(TableRelations\Tables\TableSpace $space)
    {
        $courses = $space->table('courses');
        $crs_data = $this->tf->Table(self::HEAD_COURSE_TABLE, 'crs')
                        ->addField($this->tf->field('crs_id'));
        $space->addTablePrimary($crs_data)
                ->addDependency(
                    $this->tf->TableLeftJoin($crs_data, $courses, $courses->field('crs_id')->EQ($crs_data->field('crs_id')))
                );
        $space->setRootTable($crs_data);
    }

    protected function addCrsTypeEduProgrammeTableTo(TableRelations\Tables\TableSpace $space)
    {
        $courses = $space->table('courses');

        $crs_data = $this->tf->Table(self::HEAD_COURSE_TABLE, 'crs')
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('crs_type'))
                        ->addField($this->tf->field('edu_programme'));

        $crs_data_space = $this->tf->TableSpace()
                            ->addTablePrimary($crs_data)
                            ->setRootTable($crs_data)
                            ->request($crs_data->field('crs_id'), 'crs_id')
                            ->request(
                                $this->tf->ifThenElse(
                                    '',
                                    $crs_data->field('crs_type')->IS_NULL()
                                        ->_OR($crs_data->field('crs_type')->EQ($this->pf->str('-')))
                                        ->_OR($crs_data->field('crs_type')->EQ($this->pf->str(''))),
                                    $this->tf->constString('', $this->plugin->txt('no_assignment')),
                                    $crs_data->field('crs_type')
                                ),
                                'crs_type'
                            )
                            ->request(
                                $this->tf->ifThenElse(
                                    '',
                                    $crs_data->field('edu_programme')->IS_NULL()
                                        ->_OR($crs_data->field('edu_programme')->EQ($this->pf->str('-')))
                                        ->_OR($crs_data->field('edu_programme')->EQ($this->pf->str(''))),
                                    $this->tf->constString('', $this->plugin->txt('no_assignment')),
                                    $crs_data->field('edu_programme')
                                ),
                                'edu_programme'
                            );
        $crs_type_edu_programme = $this->tf->DerivedTable($crs_data_space, 'crs_type_edu_programme');
        $space->addTablePrimary($crs_type_edu_programme)
                ->addDependency(
                    $this->tf->TableLeftJoin($crs_type_edu_programme, $courses, $courses->field('crs_id')->EQ($crs_type_edu_programme->field('crs_id')))
                );
        $space->setRootTable($crs_type_edu_programme);
    }


    protected function addTopicsTableToSpace(TableRelations\Tables\TableSpace $space)
    {
        $courses = $space->table('courses');
        $topics_data = $this->tf->Table(self::HEAD_COURSE_TOPICS_TABLE, 'topics')
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('list_data'));
        $crs_data = $this->tf->Table(self::HEAD_COURSE_TABLE, 'crs')
                        ->addField($this->tf->field('crs_id'));
        $topics_space = $this->tf->TableSpace()
                            ->addTablePrimary($crs_data)
                            ->addTablePrimary($topics_data)
                            ->setRootTable($crs_data)
                            ->addDependency(
                                $this->tf->TableLeftJoin($crs_data, $topics_data, $topics_data->field('crs_id')->EQ($crs_data->field('crs_id')))
                            )
                            ->request($crs_data->field('crs_id'), 'crs_id')
                            ->request(
                                $this->tf->ifThenElse(
                                    '',
                                    $topics_data->field('list_data')->IS_NULL()
                                        ->_OR($topics_data->field('list_data')->EQ($this->pf->str('-')))
                                        ->_OR($topics_data->field('list_data')->EQ($this->pf->str(''))),
                                    $this->tf->constString('', $this->plugin->txt('no_assignment')),
                                    $topics_data->field('list_data')
                                ),
                                'list_data'
                            );
        $topics = $this->tf->DerivedTable($topics_space, 'topics');
        $space->addTablePrimary($topics)
                ->addDependency($this->tf->TableLeftJoin($topics, $courses, $courses->field('crs_id')->EQ($topics->field('crs_id'))))
                ->setRootTable($topics);
    }

    protected function addCategoriesTableToSpace(TableRelations\Tables\TableSpace $space)
    {
        $courses = $space->table('courses');
        $categories_data = $this->tf->Table(self::HEAD_COURSE_CATEGORIES_TABLE, 'categories')
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('list_data'));
        $crs_data = $this->tf->Table(self::HEAD_COURSE_TABLE, 'crs')
                        ->addField($this->tf->field('crs_id'));
        $categories_space = $this->tf->TableSpace()
                            ->addTablePrimary($crs_data)
                            ->addTablePrimary($categories_data)
                            ->setRootTable($crs_data)
                            ->addDependency(
                                $this->tf->TableLeftJoin($crs_data, $categories_data, $categories_data->field('crs_id')->EQ($crs_data->field('crs_id')))
                            )
                            ->request($crs_data->field('crs_id'), 'crs_id')
                            ->request(
                                $this->tf->ifThenElse(
                                    '',
                                    $categories_data->field('list_data')->IS_NULL()
                                        ->_OR($categories_data->field('list_data')->EQ($this->pf->str('-')))
                                        ->_OR($categories_data->field('list_data')->EQ($this->pf->str(''))),
                                    $this->tf->constString('', $this->plugin->txt('no_assignment')),
                                    $categories_data->field('list_data')
                                ),
                                'list_data'
                            );
        $categories = $this->tf->DerivedTable($categories_space, 'categories');
        $space->addTablePrimary($categories)
                ->addDependency($this->tf->TableLeftJoin($categories, $courses, $courses->field('crs_id')->EQ($categories->field('crs_id'))))
                ->setRootTable($categories);
    }


    protected function possiblyConstrainCourses(TableRelations\Tables\TableSpace $space)
    {
        if ($this->settings->global()) {
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


    protected function maybeEnrichByPStatusTitle($title_column_participated, $title_column_absent, $title_column_in_progress)
    {
        assert('is_string($title_column_participated)');
        assert('is_string($title_column_absent)');
        assert('is_string($title_column_in_progress)');
        if (\ilPluginAdmin::isPluginActive('xcmb')) {
            $map = ['participated' => [],'absent' => [],'in_progress' => []];
            foreach (\ilPlugin::getPluginObject('Services', 'Repository', 'robj', 'CourseMember')
                        ->getLPOptionsDB()
                        ->select(true) as $option) {
                $o_title = $option->getTitle();
                switch ($option->getILIASLP()) {
                    case \ilLPStatus::LP_STATUS_COMPLETED_NUM:
                        $map['participated'][] = $o_title;
                        break;
                    case \ilLPStatus::LP_STATUS_FAILED_NUM:
                        $map['absent'][] = $o_title;
                        break;
                    case \ilLPStatus::LP_STATUS_IN_PROGRESS_NUM:
                        $map['in_progress'][] = $o_title;
                        break;
                }
            }
            $parts = $map['participated'];
            if (count($parts) > 0) {
                sort($parts);
                $title_column_participated = $this->attachSubsToSuper($title_column_participated, $parts);
            }
            $absent = $map['absent'];
            if (count($absent) > 0) {
                sort($absent);
                $title_column_absent = $this->attachSubsToSuper($title_column_absent, $absent);
            }
            $in_progress = $map['in_progress'];
            if (count($in_progress) > 0) {
                sort($in_progress);
                $title_column_in_progress = $this->attachSubsToSuper($title_column_in_progress, $in_progress);
            }
        }
        return [$title_column_participated
                ,$title_column_absent
                ,$title_column_in_progress];
    }


    protected function attachSubsToSuper($super, array $subs)
    {
        return $super . ': ' . implode(', ', $subs);
    }

    /**
     * Configures the table that displays the reports data.
     *
     * @param	SelectableReportTableGUI	$table
     * @return	void
     */
    public function configureTable(\SelectableReportTableGUI $table)
    {
        return $this->configureJointTableWithSpace(
            $this->addGouppedRowToTable($table),
            $this->space(),
            $this->space()->table('courses'),
            $this->space()->table('courses')
        );
    }
    public function configureOverviewTable(\SelectableReportTableGUI $table)
    {
        return $this->configureJointTableWithSpace(
            $table,
            $this->overviewSpace(),
            $this->overviewSpace()->table('crs'),
            $this->overviewSpace()->table('usrcrs')
        );
    }

    /**
     * Configure columns jint for both, detail and overview
     * tables.
     *
     * @param	\SelectableReportTableGUI	$table
     * @param	TableRelations\Tables\TableSpace	$space
     * @param	TableRelations\Tables\AbstractTable	$courses
     * @param	TableRelations\Tables\AbstractTable	$bookings
     * @return	void
     */
    protected function configureJointTableWithSpace(
        \SelectableReportTableGUI $table,
        TableRelations\Tables\TableSpace $space,
        TableRelations\Tables\AbstractTable $courses,
        TableRelations\Tables\AbstractTable $bookings
    ) {
        $table->setRowTemplate('tpl.report_row.html', $this->plugin->getDirectory());

        $title_column_participated = $this->plugin->txt('cnt_participated');
        $title_column_absent = $this->plugin->txt('cnt_absent');
        $title_column_in_progress = $this->plugin->txt('cnt_in_progress');

        list($title_column_participated, $title_column_absent, $title_column_in_progress) =
            $this->maybeEnrichByPStatusTitle($title_column_participated, $title_column_absent, $title_column_in_progress);

        $table
            ->defineFieldColumn(
                $this->plugin->txt('cnt_courses'),
                'cnt_courses',
                ['cnt_courses' => $this->tf->countField('cnt_courses', $courses->field('crs_id'), true)],
                true,
                true,
                false,
                true,
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('cnt_users'),
                'cnt_users',
                ['cnt_users' => $this->tf->countField('cnt_users', $bookings->field('usr_id'), true)],
                true,
                true,
                false,
                true,
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('cnt_booked'),
                'cnt_booked',
                ['cnt_booked' => $this->tf->sum(
                    'cnt_booked',
                    $this->tf->ifThenElse(
                        '',
                        $bookings->field('booking_status')->EQ($this->pf->str('participant')),
                        $this->tf->constInt('', 1),
                        $this->tf->constInt('', 0)
                    )
                )],
                true,
                true,
                false,
                true,
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('cnt_waiting'),
                'cnt_waiting',
                ['cnt_waiting' => $this->tf->sum(
                    'cnt_waiting',
                    $this->tf->ifThenElse(
                        '',
                        $bookings->field('booking_status')->EQ($this->pf->str('waiting')),
                        $this->tf->constInt('', 1),
                        $this->tf->constInt('', 0)
                    )
                )],
                true,
                true,
                false,
                true,
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('cnt_waiting_cancelled'),
                'cnt_waiting_cancelled',
                ['cnt_waiting_cancelled' => $this->tf->sum(
                    'cnt_waiting_cancelled',
                    $this->tf->ifThenElse(
                        '',
                        $bookings->field('booking_status')->EQ($this->pf->str('waiting_cancelled')),
                        $this->tf->constInt('', 1),
                        $this->tf->constInt('', 0)
                    )
                )],
                true,
                true,
                false,
                true,
                true
            )
            ->defineFieldColumn(
                $title_column_participated,
                'cnt_participated',
                ['cnt_participated' => $this->tf->sum(
                    'cnt_participated',
                    $this->tf->ifThenElse(
                        '',
                        $bookings->field('booking_status')->EQ($this->pf->str('participant'))
                                    ->_AND($bookings->field('participation_status')->EQ($this->pf->str('successful'))),
                        $this->tf->constInt('', 1),
                        $this->tf->constInt('', 0)
                    )
                )],
                true,
                true,
                false,
                true,
                true
            )
            ->defineFieldColumn(
                $title_column_absent,
                'cnt_absent',
                ['cnt_absent' => $this->tf->sum(
                    'cnt_absent',
                    $this->tf->ifThenElse(
                        'x',
                        $bookings->field('booking_status')->EQ($this->pf->str('participant'))
                                    ->_AND($bookings->field('participation_status')->EQ($this->pf->str('absent'))),
                        $this->tf->constInt('', 1),
                        $this->tf->constInt('', 0)
                    )
                )],
                true,
                true,
                false,
                true,
                true
            )
            ->defineFieldColumn(
                $title_column_in_progress,
                'cnt_in_progress',
                ['cnt_in_progress' => $this->tf->sum(
                    'cnt_in_progress',
                    $this->tf->ifThenElse(
                        '',
                        $bookings->field('booking_status')->EQ($this->pf->str('participant'))
                                    ->_AND(
                                        $bookings->field('participation_status')->IS_NULL()
                                            ->_OR($bookings->field('participation_status')->IN(
                                                $this->pf->list_string_by_array(
                                                    ['successful'
                                                    ,
                                                    'absent']
                                                )
                                            )->_NOT())
                                    ),
                        $this->tf->constInt('', 1),
                        $this->tf->constInt('', 0)
                    )
                )],
                true,
                true,
                false,
                true,
                true
            );
        $table->setDefaultSelectedColumns(
            [
                'cnt_booked',
                'cnt_waiting',
                'cnt_waiting_cancelled',
                'cnt_participated',
                'cnt_absent',
                'cnt_in_progress'
            ]
        );
        $table->prepareTableAndSetRelevantFields($space);
        return $table;
    }

    /**
     * Depending on the settings for the aggregate id, add a
     * row to the table with the corresponding fields.
     *
     * @param	\SelectableReportTableGUI	$table
     */
    protected function addGouppedRowToTable(\SelectableReportTableGUI $table)
    {
        $space = $this->space;
        switch ($this->settings->aggregateId()) {
            case Settings\Settings::AGGREGATE_ID_NONE:
                break;
            case self::AGGREGATE_ID_CRS_TYPE:
                $table->defineFieldColumn(
                    $this->plugin->txt('crs_type'),
                    'crs_type',
                    ['crs_type' => $space->table('crs_type_edu_programme')->field('crs_type')]
                );
                break;
            case self::AGGREGATE_ID_EDU_PROGRAMME:
                $table->defineFieldColumn(
                    $this->plugin->txt('edu_programme'),
                    'edu_programme',
                    ['edu_programme' => $space->table('crs_type_edu_programme')->field('edu_programme')]
                );
                break;
            case self::AGGREGATE_ID_CRS_TOPICS:
                $table->defineFieldColumn(
                    $this->plugin->txt('crs_topics'),
                    'crs_topics',
                    ['crs_topics' => $space->table('topics')->field('list_data')]
                );
                break;
            case self::AGGREGATE_ID_CATEGORIES:
                $table->defineFieldColumn(
                    $this->plugin->txt('categories'),
                    'categories',
                    ['categories' => $space->table('categories')->field('list_data')]
                );
                break;
            default:
                throw new Exception('unknown aggregate id');
        }
        return $table;
    }

    protected function relevantTrainingIds()
    {
        if ($this->relevant_training_ids === null) {
            $parent = $this->o_d->getParentOfObjectOfType($this->object, 'cat');
            if ($parent === null) {
                $parent = $this->o_d->getParentOfObjectOfType($this->object, 'root');
            }
            $this->relevant_training_ids = $this->o_d->getAllChildrenIdsByTypeOfObject($parent, 'crs');
        }
        return $this->relevant_training_ids;
    }
}
