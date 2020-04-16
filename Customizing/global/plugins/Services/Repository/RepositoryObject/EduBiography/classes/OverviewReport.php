<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography;

use CaT\Plugins\EduBiography\Settings\Settings;
use CaT\Plugins\EduBiography\LinkGeneratorGUI;

require_once 'Services/TMS/ReportUtilities/classes/class.ilUDFWrapper.php';

class OverviewReport extends Report
{
    use \ilUDFWrapper;

    const HEAD_COURSE_TABLE = "hhd_crs";
    const HEAD_COURSE_TOPICS_TABLE = "hhd_crs_topics";
    const HEAD_COURSE_CATEGORIES_TABLE = "hhd_crs_categories";
    const HEAD_USERCOURSE_TABLE = "hhd_usrcrs";
    const USR_DATA_TABLE = 'usr_data';
    const REPORT_IDENTIFIER = "overview";

    protected $usr;
    protected $visible_users;
    protected $uol;
    /**
     * @var Settings
     */
    protected $settings;

    public function __construct(
        \ilEduBiographyPlugin $plugin,
        \ilDBInterface $ilDB,
        \ilObjUser $usr,
        UserOrguLocator $uol,
        LinkGeneratorGUI $lgg,
        \ilLanguage $lng,
        Settings $settings
    ) {
        parent::__construct($plugin, $ilDB);
        $this->usr = $usr;
        $this->uol = $uol;
        $this->lgg = $lgg;
        $this->lng = $lng;
        $this->settings = $settings;
    }

    public function getReportIdentifier()
    {
        return self::REPORT_IDENTIFIER;
    }

    /**
     * @inheritdoc
     */
    protected function buildFilter()
    {
        $plugin = $this->plugin;
        $ff = $this->ff;
        $tyf = $this->tyf;
        return
            $ff->sequence(
                $ff->singleselect(
                    $plugin->txt('filter_year'),
                    '',
                    $this->yearOptions()
                )->default_choice($this->getDefaultYear()),
                $ff->sequence(
                    $ff->text($plugin->txt('filter_lastname'), ''),
                    $ff->multiselectsearch(
                        $plugin->txt('filter_types'),
                        '',
                        $this->crsTypeOptions()
                    ),
                    $ff->multiselectsearch(
                        $plugin->txt('filter_edu_programme'),
                        '',
                        $this->eduProgrammeOptions()
                    ),
                    $ff->multiselectsearch(
                        $plugin->txt('filter_topics'),
                        '',
                        $this->crsTopicOptions()
                    ),
                    $ff->multiselectsearch(
                        $plugin->txt('filter_categories'),
                        '',
                        $this->crsCategoriesOptions()
                    ),
                    $ff->multiselectsearch(
                        $plugin->txt('orgus_filter'),
                        '',
                        $this->orgusOptions()
                    ),
                    $ff->option($plugin->txt('orgus_filter_recursive'), '')
                )
            )->map(
                function ($year, $lastname, $types, $edu_programme, $topics, $categories, $orgus, $recursive) {
                    return ['year' => $year
                        ,'lastname' => $lastname
                        ,'types' => $types
                        ,'edu_programme' => $edu_programme
                        ,'topics' => $topics
                        ,'categories' => $categories
                        ,'orgus' => $orgus
                        ,'orgus_recursive' => $recursive
                    ];
                },
                $tyf->dict(
                [
                    'year' => $tyf->int()
                    ,'lastname' => $tyf->string()
                    ,'types' => $tyf->lst($tyf->string())
                    ,'edu_programme' => $tyf->lst($tyf->string())
                    ,'topics' => $tyf->lst($tyf->string())
                    ,'categories' => $tyf->lst($tyf->string())
                    ,'orgus' => $tyf->lst($tyf->string())
                    ,'orgus_recursive' => $tyf->bool()
                ]
            )
        );
    }

    /**
     * @inheritdoc
     */
    protected function buildSpace()
    {
        $superior_overview_and_invisible =
            $this->settings->hasSuperiorOverview() &&
            count($this->settings->getInvisibleCourseTopics());

        $edu_tracking_active = $this->isEduTrackingActive();

        $crs_data = $this->tf->Table(self::HEAD_COURSE_TABLE, 'crs')
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('title'))
                        ->addField($this->tf->field('crs_type'))
                        ->addField($this->tf->field('provider'))
                        ->addField($this->tf->field('begin_date'))
                        ->addField($this->tf->field('end_date'))
                        ->addField($this->tf->field('tut'))
                        ->addField($this->tf->field('edu_programme'));

        if ($edu_tracking_active) {
            $crs_data = $crs_data->addField($this->tf->field('idd_learning_time'));
        }

        $topics_src = $this->tf->Table(self::HEAD_COURSE_TOPICS_TABLE, 'top_src')
            ->addField($this->tf->field('crs_id'))
            ->addField($this->tf->field('list_data'));

        $invisible_topics_space = $this->tf->TableSpace()
                        ->addTablePrimary($topics_src)
                        ->setRootTable($topics_src)
                        ->groupBy($topics_src->field('crs_id'));

        $visible_count_field = $superior_overview_and_invisible ?
                                $this->tf->Sum(
                                    '',
                                    $this->tf->IfThenElse(
                                        '',
                                        $topics_src->field('list_data')->IN(
                                            $this->pf->list_string_by_array($this->settings->getInvisibleCourseTopics())
                                        )->_NOT(),
                                        $this->tf->ConstInt('', 1),
                                        $this->tf->ConstInt('', 0)
                                    )
                                ) :
                                $this->tf->ConstInt('', 1);

        $invisible_count_field = $superior_overview_and_invisible ?
                                $this->tf->Sum(
                                    '',
                                    $this->tf->IfThenElse(
                                        '',
                                        $topics_src->field('list_data')->IN(
                                            $this->pf->list_string_by_array($this->settings->getInvisibleCourseTopics())
                                        ),
                                        $this->tf->ConstInt('', 1),
                                        $this->tf->ConstInt('', 0)
                                    )
                                ) :
                                $this->tf->ConstInt('', 0);

        $invisible_topics_space->request($topics_src->field('crs_id'))
                        ->request($visible_count_field, 'visible_topics')
                        ->request($invisible_count_field, 'invisible_topics');

        $invisible_topics = $this->tf->DerivedTable($invisible_topics_space, 'invisible_topics');

        $topics_filter_space = $this->tf->TableSpace()
                        ->addTablePrimary($topics_src)
                        ->setRootTable($topics_src)
                        ->groupBy($topics_src->field('crs_id'))
                        ->request($topics_src->field('crs_id'));
        $topics_filter = $this->tf->DerivedTable($topics_filter_space, 'topics_filter');

        $categories = $this->tf->Table(self::HEAD_COURSE_CATEGORIES_TABLE, 'categories')
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('list_data'));

        $participations = $this->tf->Table(self::HEAD_USERCOURSE_TABLE, 'usrcrs')
                        ->addField($this->tf->field('usr_id'))
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('booking_status'))
                        ->addField($this->tf->field('participation_status'))
                        ->addField($this->tf->field('booking_date'))
                        ->addField($this->tf->field('waiting_date'))
                        ->addField($this->tf->field('ps_acquired_date'));

        if ($edu_tracking_active) {
            $participations = $participations->addField($this->tf->field('idd_learning_time'));
        }


        if (count($this->visibleUsers()) > 0) {
            $ps_v_user_predicate = $participations->field('usr_id')->IN($this->pf->list_int_by_array($this->visibleUsers()));
        } else {
            $ps_v_user_predicate = $this->pf->_FALSE();
        }
        $participations	->addConstraint($ps_v_user_predicate->_AND($participations->field('booking_status')->IN(
            $this->pf->list_string_by_array(['participant','waiting','cancelled','cancelled_after_deadline','approval_pending'])
        )));

        $crs_participations_space = $this->tf->TableSpace()
                                ->addTablePrimary($participations)
                                ->addTableSecondary($invisible_topics)
                                ->addTableSecondary($topics_filter)
                                ->addTableSecondary($categories)
                                ->addTableSecondary($crs_data)
                                ->addDependency(
                                    $this->tf->TableJoin($participations, $crs_data, $participations->field('crs_id')->EQ($crs_data->field('crs_id')))
                                )
                                ->addDependency(
                                    $this->tf->TableLeftJoin($participations, $invisible_topics, $participations->field('crs_id')->EQ($invisible_topics->field('crs_id')))
                                )
                                ->addDependency(
                                    $this->tf->TableJoin($topics_filter, $participations, $participations->field('crs_id')->EQ($topics_filter->field('crs_id')))
                                )
                                ->addDependency(
                                    $this->tf->TableJoin($participations, $categories, $participations->field('crs_id')->EQ($categories->field('crs_id')))
                                )
                                ->setRootTable($participations)
                                ->request($participations->field('usr_id'))
                                ->request($participations->field('crs_id'))
                                ->request($participations->field('participation_status'))
                                ->request($participations->field('booking_status'));

        if ($edu_tracking_active) {
            $crs_participations_space = $crs_participations_space->request($participations->field('idd_learning_time'), 'part_idd')
                                ->request($crs_data->field('idd_learning_time'), 'max_idd')
                                ->groupBy($participations->field('usr_id'))
                                ->groupBy($participations->field('crs_id'));
        }

        $crs_participations = $this->tf->derivedTable($crs_participations_space, 'crs_participations');

        $orgu_space = $this->tf->TableSpace()
            ->addTablePrimary(
                $this->tf->Table('il_orgu_ua', 'ua')
                    ->addField($this->tf->field('user_id'))
                    ->addField($this->tf->field('orgu_id'))
            )
            ->addTablePrimary(
                $this->tf->Table('object_reference', 'ref')
                    ->addField($this->tf->field('obj_id'))
                    ->addField($this->tf->field('ref_id'))
            )
            ->addTablePrimary(
                $this->tf->Table('object_data', 'data')
                    ->addField($this->tf->field('obj_id'))
                    ->addField($this->tf->field('title'))
            );

        if (count($this->visibleUsers()) > 0) {
            $orgus_v_user_predicate = $orgu_space->table('ua')->field('user_id')
                ->IN($this->pf->list_int_by_array($this->visibleUsers()));
        } else {
            $orgus_v_user_predicate = $this->pf->_FALSE();
        }

        $orgu_space->setRootTable($orgu_space->table('ua'))
            ->addDependency($this->tf->TableJoin(
                $orgu_space->table('ua'),
                $orgu_space->table('ref'),
                $orgu_space->table('ua')->field('orgu_id')->EQ($orgu_space->table('ref')->field('ref_id'))
            ))
            ->addDependency(
                $this->tf->TableJoin(
                    $orgu_space->table('ref'),
                    $orgu_space->table('data'),
                    $orgu_space->table('ref')->field('obj_id')->EQ($orgu_space->table('data')->field('obj_id'))
                )
            )
            ->addFilter($orgus_v_user_predicate)
            ->request($orgu_space->table('ua')->field('user_id'))
            ->groupBy($orgu_space->table('ua')->field('user_id'));


        $orgu_space_all = clone $orgu_space;
        $orgu_space_all->request($this->tf->groupConcat('orgus_all', $orgu_space->table('data')->field('title')));
        $orgu_space_filter = clone $orgu_space;

        $orgu_all = $this->tf->derivedTable($orgu_space_all, 'orgu_all');
        $orgu_filter = $this->tf->derivedTable($orgu_space_filter, 'orgu_filter');

        $usr_data = $this->tf->Table(self::USR_DATA_TABLE, 'usr')
                    ->addField($this->tf->field('usr_id'));

        // this includes first- and lastname!!!
        foreach ($this->getAllCourseVisibleStandardUserFields() as $field) {
            if ($field == "username") {
                $field = "login";
            }

            // skip orgunits. values will be added bei import UDF
            if ($field == "org_units") {
                continue;
            }

            $usr_data = $usr_data->addField($this->tf->field($field));
        }

        if (count($this->visibleUsers()) > 0) {
            $user_v_user_predicate = $usr_data->field('usr_id')->IN($this->pf->list_int_by_array($this->visibleUsers()));
        } else {
            $user_v_user_predicate = $this->pf->_FALSE();
        }

        $space = $this->tf->TableSpace()
            ->addTablePrimary($usr_data)
            ->addTableSecondary($crs_participations)
            ->addTableSecondary($orgu_all)
            ->addTableSecondary($orgu_filter)
            ->setRootTable($usr_data)
            ->addDependency($this->tf->TableLeftJoin($usr_data, $crs_participations, $crs_participations->field('usr_id')->EQ($usr_data->field('usr_id'))))
            ->addDependency($this->tf->TableJoin($usr_data, $orgu_all, $orgu_all->field('user_id')->EQ($usr_data->field('usr_id'))))
            ->addDependency($this->tf->TableJoin($usr_data, $orgu_filter, $orgu_filter->field('user_id')->EQ($usr_data->field('usr_id'))))
            ->request($usr_data->field('usr_id'))
            ->addFilter($user_v_user_predicate)
            ->groupBy($usr_data->field('usr_id'))
        ;

        $space = $this->appendUDFsToSpace(
            $this->tf,
            $this->pf,
            $space,
            $usr_data,
            'usr_id'
        );

        if ($superior_overview_and_invisible) {
            $crs_participations_space->addFilter(
                $this->pf->_ANY(
                    $invisible_topics->field('invisible_topics')->EQ($this->pf->int(0))->_OR($invisible_topics->field('invisible_topics')->IS_NULL()),
                    $invisible_topics->field('invisible_topics')->GT($this->pf->int(0))->_AND($invisible_topics->field('visible_topics')->GT($this->pf->int(0)))
                )
            );
        }

        return $space;
    }

    /**
     * Applys the filter settings to the data.
     *
     * @param	array	$settings
     * @return	void
     */
    public function applyFilterToSpace(array $settings)
    {
        $settings = $this->getFilterSettings($settings);

        $this->possiblyAddLastnameFilter($settings['lastname']);
        $this->possiblyApplyYearToSpace($settings['year']);
        $this->possiblyApplyTypesToSpace($settings['types']);
        $this->possiblyApplyEduProgrammeToSpace($settings['edu_programme']);
        $this->possiblyApplyTopicsToSpace($settings['topics']);
        $this->possiblyApplyCategoriesToSpace($settings['categories']);
        $this->possiblyApplyOrgusFilter($settings['orgus'], $settings['orgus_recursive']);
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
     * Apply filter selection to space.
     *
     * @return	void
     */
    private function possiblyAddLastnameFilter($lastname)
    {
        if (trim((string) $lastname) !== '') {
            $lastname_f_predicate = $this->space()->table('usr')->field('lastname')->LIKE($this->pf->str($lastname . '%'));
            $this->space()->addFilter($lastname_f_predicate);
        }
    }
    private function possiblyApplyYearToSpace($year)
    {
        if ($year === 0) {
            $year = $this->getDefaultYear();
        }
        $this->lgg->addParameter(self::KEY_DEFAULT_YEAR_PRESET, (string) $year);
        $crs_participations_space = $this->space()->table('crs_participations')->space();

        $begin_date = $year . '-01-01';
        $end_date = $year . '-12-31';
        $begin_date_f = $crs_participations_space->table('crs')->field('begin_date');
        $end_date_f = $crs_participations_space->table('crs')->field('end_date');

        $booking_date_f = $crs_participations_space->table('usrcrs')->field('booking_date');
        $waiting_date_f = $crs_participations_space->table('usrcrs')->field('waiting_date');
        $participation_date_f = $crs_participations_space->table('usrcrs')->field('ps_acquired_date');

        $begin_date_not_null = $begin_date_f->IS_NULL()->_NOT();
        $end_date_not_null = $end_date_f->IS_NULL()->_NOT();

        $booking_date_not_null = $booking_date_f->IS_NULL()->_NOT();
        $waiting_date_not_null = $waiting_date_f->IS_NULL()->_NOT();
        $participation_date_not_null = $participation_date_f->IS_NULL()->_NOT();

        $is_self_learn = $begin_date_f->IS_NULL()->_OR($begin_date_f->EQ($this->pf->str('0001-01-01')));
        $is_not_self_learn = $is_self_learn->_NOT();

        $predicate_not_self_learn =
            $is_not_self_learn->_AND(
                $this->pf->_ALL(
                    $end_date_not_null,
                    $end_date_f->LE()->str($end_date),
                    $end_date_f->GE()->str($begin_date)
                )->_OR(
                    $this->pf->_ALL(
                        $begin_date_not_null,
                        $end_date_f->IS_NULL(),
                        $begin_date_f->LE()->str($end_date),
                        $begin_date_f->GE()->str($begin_date)
                    )
                )
            );

        $predicate_self_learn =
            $is_self_learn->_AND(
                $this->pf->_ALL(
                    $participation_date_not_null,
                    $participation_date_f->LE()->str($end_date),
                    $participation_date_f->GE()->str($begin_date)
                )
                ->_OR(
                    $this->pf->_ALL(
                        $booking_date_not_null,
                        $participation_date_f->IS_NULL(),
                        $booking_date_f->LE()->str($end_date),
                        $booking_date_f->GE()->str($begin_date)
                    )
                )
                ->_OR(
                    $this->pf->_ALL(
                        $waiting_date_not_null,
                        $participation_date_f->IS_NULL(),
                        $waiting_date_f->LE()->str($end_date),
                        $waiting_date_f->GE()->str($begin_date)
                    )
                )
            );
        $crs_participations_space->addFilter($predicate_self_learn->_OR($predicate_not_self_learn));
    }
    private function possiblyApplyTypesToSpace(array $types)
    {
        if (count($types) > 0) {
            $crs_participations_space = $this->space()->table('crs_participations')->space();
            $type_f = $crs_participations_space->table('crs')->field('crs_type');
            $predicate = $type_f->IN($this->pf->list_string_by_array($types));
            $crs_participations_space->addFilter($predicate);
        }
    }
    private function possiblyApplyTopicsToSpace(array $topics)
    {
        if (count($topics) > 0) {
            $crs_participations_space = $this->space()->table('crs_participations')->space();
            $topics_space = $crs_participations_space->table('topics_filter')->space();
            $topics_space->addFilter($topics_space->table('top_src')->field('list_data')->IN($this->pf->list_string_by_array($topics)));
            $crs_participations_space->forceRelevant($crs_participations_space->table('topics_filter'));
        }
    }
    private function possiblyApplyCategoriesToSpace(array $categories)
    {
        if (count($categories) > 0) {
            $crs_participations_space = $this->space()->table('crs_participations')->space();
            $categories_f = $crs_participations_space->table('categories')->field('list_data');
            $crs_participations_space->addFilter($categories_f->IN($this->pf->list_string_by_array($categories)));
        }
    }
    private function possiblyApplyEduProgrammeToSpace(array $edu_programmes)
    {
        if (count($edu_programmes) > 0) {
            $crs_participations_space = $this->space()->table('crs_participations')->space();
            $edu_programme_f = $crs_participations_space->table('crs')->field('edu_programme');
            $crs_participations_space->addFilter($edu_programme_f->IN($this->pf->list_string_by_array($edu_programmes)));
        }
    }
    private function possiblyApplyOrgusFilter(array $orgus, bool $recursive)
    {
        if ($recursive) {
            foreach ($orgus as $orgu) {
                $child_orgus = $this->uol->getChildrenOf($orgu);
                $child_orgus = array_map(function ($id) {
                    return (int) $id;
                }, $child_orgus);
                $orgus = array_merge($orgus, $child_orgus);
            }
        }

        $orgus = array_unique($orgus);

        if (count($orgus) > 0) {
            $o_f_space = $this->space()->table('orgu_filter')->space();
            $o_ref_id_f = $o_f_space->table('ref')->field('ref_id');
            $o_f_space->addFilter($o_ref_id_f->IN($this->pf->list_int_by_array($orgus)));
            $this->space()->forceRelevant($this->space()->table('orgu_filter'));
        }
    }

    /**
     * @inheritdoc
     */
    public function configureTable(\SelectableReportTableGUI $table)
    {
        $space = $this->space();
        $table->setRowTemplate('tpl.edubio_row.html', $this->plugin->getDirectory());
        $table->setDefaultOrderField("lastname");
        $table->setDefaultOrderDirection("asc");

        $table
            ->defineFieldColumn(
                $this->plugin->txt('lastname'),
                'lastname',
                ['lastname' => $space->table('usr')->field('lastname')]
            )
            ->defineFieldColumn(
                $this->plugin->txt('firstname'),
                'firstname',
                ['firstname' => $space->table('usr')->field('firstname')]
            );

        /**
         * skip firstname and lastname because they have a fixed position in table
         * username isn't visible at all
         * org_units will be added by udf
         */
        foreach ($this->getAllCourseVisibleStandardUserFields() as $field) {
            if (in_array($field, ["firstname", "lastname", "username", "org_units"])) {
                continue;
            }

            $table = $table->defineFieldColumn(
                $this->lng->txt($field),
                "UDF_" . $field,
                ["UDF_" . $field => $space->table('usr')->field($field)],
                true
            );
        }

        $table = $this->addUDFColumnsToTable($space, $table);

        $table = $table
            ->defineFieldColumn(
                $this->plugin->txt('orgus'),
                'orgus',
                ['orgus' => $space->table('orgu_all')->field('orgus_all')],
                true
            );

        if ($this->isEduTrackingActive()) {
            $table = $table->defineFieldColumn(
                $this->plugin->txt('sum_idd_achieved'),
                'sum_idd_achieved',
                ['sum_idd_achieved' => $this->tf->sum(
                        'sum_idd_achieved',
                        $this->tf->IfThenElse(
                            '',
                            $this->pf->_ALL(
                                $this->space->table('crs_participations')
                                    ->field('participation_status')
                                    ->EQ($this->pf->str('successful')),
                                $this->space->table('crs_participations')
                                    ->field('booking_status')
                                    ->EQ($this->pf->str('participant'))
                            ),
                            $this->tf->IfThenElse(
                                '',
                                $this->space->table('crs_participations')->field('part_idd')->IS_NULL(),
                                $this->space->table('crs_participations')->field('max_idd'),
                                $this->space->table('crs_participations')->field('part_idd')
                            ),
                            $this->tf->ConstInt('', 0)
                        )
                    )],
                true
                )
                ->defineFieldColumn(
                    $this->plugin->txt('sum_idd_forecast'),
                    'sum_idd_forecast',
                    ['sum_idd_forecast' => $this->tf->sum(
                        'sum_idd_forecast',
                        $this->tf->IfThenElse(
                            '',
                            $this->pf->_ALL(
                                $this->space->table('crs_participations')
                                    ->field('participation_status')
                                    ->EQ($this->pf->str('successful')),
                                $this->space->table('crs_participations')
                                    ->field('booking_status')
                                    ->EQ($this->pf->str('participant'))
                            ),
                            $this->tf->IfThenElse(
                                '',
                                $this->space->table('crs_participations')->field('part_idd')->IS_NULL(),
                                $this->space->table('crs_participations')->field('max_idd'),
                                $this->space->table('crs_participations')->field('part_idd')
                            ),
                            $this->tf->IfThenElse(
                                '',
                                $this->space->table('crs_participations')->field('booking_status')
                                ->IN($this->pf->list_string_by_array(
                                    ['participant','waiting','approval_pending']
                                ))
                                ->_AND($space->table('crs_participations')->field('participation_status')->NEQ($this->pf->str('absent'))
                                    ->_OR($space->table('crs_participations')->field('participation_status')->IS_NULL())),
                                $this->space->table('crs_participations')->field('max_idd'),
                                $this->tf->ConstInt('', 0)
                            )
                        )
                    )],
                    true
                );
        }

        $table = $table->defineFieldColumn(
            $this->plugin->txt('cnt_booked'),
            'count_booked',
            ['count_booked' => $this->tf->sum('count_booked', $this->tf->ifThenElse(
                    '',
                    $this->space->table('crs_participations')->field('booking_status')->EQ($this->pf->str('participant')),
                    $this->tf->constInt('', 1),
                    $this->tf->constInt('', 0)
                ))],
            true
            )
            ->defineFieldColumn(
                $this->plugin->txt('cnt_waiting'),
                'count_waiting',
                ['count_waiting' => $this->tf->sum('count_waiting', $this->tf->ifThenElse(
                    '',
                    $this->space->table('crs_participations')->field('booking_status')
                        ->IN($this->pf->list_string_by_array(
                            ['waiting','approval_pending']
                        )),
                    $this->tf->constInt('', 1),
                    $this->tf->constInt('', 0)
                ))],
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('cnt_participated'),
                'count_participated',
                ['count_participated' => $this->tf->sum('count_participated', $this->tf->ifThenElse(
                    '',
                    $this->space->table('crs_participations')->field('participation_status')->EQ($this->pf->str('successful')),
                    $this->tf->constInt('', 1),
                    $this->tf->constInt('', 0)
                ))],
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('cnt_absent'),
                'count_absent',
                ['count_absent' => $this->tf->sum('count_absent', $this->tf->ifThenElse(
                    '',
                    $this->space->table('crs_participations')->field('participation_status')->EQ($this->pf->str('absent')),
                    $this->tf->constInt('', 1),
                    $this->tf->constInt('', 0)
                ))],
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('cnt_cancelled_after_dl'),
                'cnt_cancelled_after_dl',
                ['cnt_cancelled_after_dl' => $this->tf->sum('cnt_cancelled_after_dl', $this->tf->ifThenElse(
                    '',
                    $this->space->table('crs_participations')->field('booking_status')->EQ($this->pf->str('cancelled_after_deadline')),
                    $this->tf->constInt('', 1),
                    $this->tf->constInt('', 0)
                ))],
                true
            )
            ;

        $table->setDefaultSelectedColumns(
            array_intersect(
                $this->settings->getInitVisibleColumns(),
                array_keys($this->plugin->getAllVisibleFields())
            )
        );
        $table->prepareTableAndSetRelevantFields($space);

        $this->space = $space;

        return $table;
    }

    /**
     * @inheritdoc
     */
    protected function postprocessRowHTML(array $row)
    {
        $row = $this->postprocessRowCommon($row);
        if ($row['firstname']) {
            $row = $this->formatUserLinkField($row, 'firstname');
        }
        if ($row['lastname']) {
            $row = $this->formatUserLinkField($row, 'lastname');
        }
        if ($row['email']) {
            $row = $this->formatUserLinkField($row, 'email');
        }
        return $row;
    }

    /**
     * Create link to user detail report and substitute value with it.
     *
     * @param	mixed[]	$row
     * @param	string	$field
     * @return	mixed[]
     */
    protected function formatUserLinkField($row, $field)
    {
        $usr_id = (int) $row['usr_id'];
        $link = $this->lgg->getLinkForUserDetailReport($usr_id);
        $tpl = new \ilTemplate("tpl.link_entry.html", true, true, $this->plugin->getDirectory());
        $tpl->setVariable('TITLE', $row[$field]);
        $tpl->setVariable('LINK', $link);
        $row[$field] = $tpl->get();
        return $row;
    }

    /**
     * @inheritdoc
     */
    protected function postprocessRowCommon(array $row)
    {
        $end_date = $row['end_date'];
        $begin_date = $row['begin_date'];
        if (!$end_date && !$begin_date) {
            $row['crs_date'] = '-';
        } elseif (!$end_date) {
            $row['crs_date'] = date('d.m.Y', strtotime($begin_date));
        } else {
            $row['crs_date'] = date('d.m.Y', strtotime($begin_date)) . '-' . date('d.m.Y', strtotime($end_date));
        }
        foreach ($row as $key => &$value) {
            if ($value === null || $value == "") {
                $value = '-';
            }
        }

        if ($this->isEduTrackingActive()) {
            if ($row['sum_idd_achieved']) {
                $row['sum_idd_achieved'] = $this->minutesToTimeString((int) $row['sum_idd_achieved']) .
                    ' ' .
                    $this->plugin->txt('hours');
                ;
            } else {
                $row['sum_idd_achieved'] = $this->minutesToTimeString(0) .
                    ' ' .
                    $this->plugin->txt('hours');
                ;
            }
            if ($row['sum_idd_forecast']) {
                $row['sum_idd_forecast'] = $this->minutesToTimeString((int) $row['sum_idd_forecast']) .
                    ' ' .
                    $this->plugin->txt('hours');
                ;
            } else {
                $row['sum_idd_forecast'] = $this->minutesToTimeString(0) .
                    ' ' .
                    $this->plugin->txt('hours');
            }
        } else {
            unset($row['sum_idd_achieved']);
            unset($row['sum_idd_forecast']);
        }

        return $row;
    }

    /**
     * Fetch user-ids visible to report viewer.
     *
     * @return	int[]
     */
    protected function visibleUsers()
    {
        if (!$this->visible_users) {
            $this->visible_users = $this->uol->getVisibleUserIds($this->usr);
        }
        return $this->visible_users;
    }

    /**
     * Fetch orgu-ref-ids => titles visible to report viewer.
     *
     * @return	string[int]
     */
    protected function orgusOptions()
    {
        $return = $this->uol->orgusVisibleToUser($this->usr);
        asort($return);
        return $return;
    }

    /**
     * Get all standard user fields visible in Courses
     *
     * @return string[]
     */
    protected function getAllCourseVisibleStandardUserFields()
    {
        return $this->plugin->getAllCourseVisibleStandardUserFields();
    }

    protected function getSettings() : Settings
    {
        return $this->settings;
    }
}
