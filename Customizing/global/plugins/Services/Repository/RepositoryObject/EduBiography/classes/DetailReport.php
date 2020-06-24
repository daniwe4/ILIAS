<?php

namespace CaT\Plugins\EduBiography;

use CaT\Plugins\EduBiography\Settings\Settings;
use  CaT\Plugins\EduBiography\FileStorage;

class DetailReport extends Report
{
    const HEAD_COURSE_TABLE = "hhd_crs";
    const HEAD_COURSE_TUT_TABLE = "hhd_crs_tut";
    const HEAD_COURSE_TOPICS_TABLE = "hhd_crs_topics";
    const HEAD_COURSE_CATEGORIES_TABLE = "hhd_crs_categories";
    const HEAD_USERCOURSE_TABLE = "hhd_usrcrs";
    const HEAD_USERCOURSE_NIGHTS_TABLE = "hhd_usrcrs_nights";
    const REPORT_IDENTIFIER = "detail";
    const SEND_RECOMMENDATION_ID = "SR01";

    /**
     * @var int
     */
    protected $user_id;
    /**
     * @var \ilTree
     */
    protected $g_tree;
    /**
     * @var \ilAccess
     */
    protected $g_access;

    /**
     * @var Settings
     */
    protected $settings;

    // GOA special hack for #3410
    /**
     * @var FileStorage\ilCertificateStorage
     */
    protected $file_storage;
    // GOA special hack for #3410

    public function __construct(
        \ilEduBiographyPlugin $plugin,
        \ilDBInterface $ilDB,
        int $user_id,
        \ilCtrl $ctrl,
        UserOrguLocator $uol,
        \ilObjUser $g_user,
        \ilTree $g_tree,
        \ilAccess $g_access,
        Settings $settings,
        FileStorage\ilCertificateStorage $file_storage
    ) {
        parent::__construct($plugin, $ilDB);
        $this->user_id = $user_id;
        $this->uol = $uol;
        $this->ctrl = $ctrl;
        $this->g_user = $g_user;
        $this->g_tree = $g_tree;
        $this->g_access = $g_access;
        $this->settings = $settings;
        $this->file_storage = $file_storage;
    }

    public function usrId()
    {
        return $this->user_id;
    }

    public function getReportIdentifier()
    {
        return self::REPORT_IDENTIFIER;
    }

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
                        $plugin->txt('filter_booking_status'),
                        '',
                        $this->bookingStatusOptions()
                    ),
                    $ff->multiselectsearch(
                        $plugin->txt('filter_participation_status'),
                        '',
                        $this->participationStatusOptions()
                    )
                )
            )->map(function ($year, $types, $edu_programme, $topics, $categories, $booking_status, $participation_status) {
                return [	'year' => $year
                                ,'types' => $types
                                ,'edu_programme' => $edu_programme
                                ,'topics' => $topics
                                ,'categories' => $categories
                                ,'booking_status' => $booking_status
                                ,'participation_status' => $participation_status];
            }, $tyf->dict(
                [
                        'year' => $tyf->int()
                        ,'types' => $tyf->lst($tyf->string())
                        ,'edu_programme' => $tyf->lst($tyf->string())
                        ,'topics' => $tyf->lst($tyf->string())
                        ,'categories' => $tyf->lst($tyf->string())
                        ,'booking_status' => $tyf->lst($tyf->string())
                        ,'participation_status' => $tyf->lst($tyf->string())
                        ]
            ));
    }

    protected function buildSpace()
    {
        $superior_overview_and_invisible =
            $this->settings->hasSuperiorOverview() &&
            count($this->settings->getInvisibleCourseTopics());

        $edu_tracking_active = $this->isEduTrackingActive();
        $accomodation_active = $this->isAccomodationActive();

        $crs_data = $this->tf->Table(self::HEAD_COURSE_TABLE, 'crs')
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('title'))
                        ->addField($this->tf->field('crs_type'))
                        ->addField($this->tf->field('venue'))
                        ->addField($this->tf->field('provider'))
                        ->addField($this->tf->field('begin_date'))
                        ->addField($this->tf->field('end_date'))
                        ->addField($this->tf->field('tut'))
                        ->addField($this->tf->field('edu_programme'));

        if ($edu_tracking_active) {
            $crs_data = $crs_data->addField($this->tf->field('idd_learning_time'));
        }

        if ($accomodation_active) {
            $crs_data = $crs_data
                ->addField($this->tf->field('accomodation'));
        }

        $crs_data = $crs_data->addField($this->tf->field('fee'));

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
                        ->request($this->tf->groupConcat('tutors', $this->tf->concat('tutor_names', $tutors_data->field('firstname'), $tutors_data->field('lastname'), ' ')))
                        ->addDependency($this->tf->TableJoin($tutors_data, $tutors_src, $tutors_data->field('usr_id')->EQ($tutors_src->field('list_data'))))
                        ->groupBy($tutors_src->field('crs_id'));
        $tutors = $this->tf->DerivedTable($tutors_space, 'tut');

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


        $categories_src = $this->tf->Table(self::HEAD_COURSE_CATEGORIES_TABLE, 'cat_src')
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('list_data'));
        $categories_space = $this->tf->TableSpace()
                        ->addTablePrimary($categories_src)
                        ->setRootTable($categories_src)
                        ->groupBy($categories_src->field('crs_id'));
        $categories_space->request($categories_src->field('crs_id'));
        $categories = $this->tf->DerivedTable($categories_space, 'categories');

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

        $participations = $participations
                        ->addConstraint(
                            $participations->field('usr_id')->EQ()->int((int) $this->user_id)
                                            ->_AND($participations->field('booking_status')->IN(
                                                $this->pf->list_string_by_array(
                                                    ['participant'
                                                    ,'waiting'
                                                    ,'waiting_cancelled'
                                                    ,'approval_pending']
                                                )
                                            )->_OR($participations->field('booking_status')->EQ($this->pf->str('cancelled_after_deadline'))
                                                    ->_AND($participations->field('booking_date')->IS_NULL()->_NOT())))
                                            );

        $space = $this->tf->TableSpace()
                        ->addTablePrimary($participations)
                        ->addTableSecondary($tutors)
                        ->addTableSecondary($crs_data)
                        ->addTableSecondary($invisible_topics)
                        ->addTableSecondary($topics_filter)
                        ->addTableSecondary($categories)
                        ->setRootTable($participations)
                        ->addDependency(
                            $this->tf->TableJoin($participations, $crs_data, $participations->field('crs_id')->EQ($crs_data->field('crs_id')))
                        )
                        ->addDependency(
                            $this->tf->TableLeftJoin($participations, $tutors, $participations->field('crs_id')->EQ($tutors->field('crs_id')))
                        )
                        ->addDependency(
                            $this->tf->TableLeftJoin($participations, $invisible_topics, $participations->field('crs_id')->EQ($invisible_topics->field('crs_id')))
                        )
                        ->addDependency(
                            $this->tf->TableJoin($topics_filter, $participations, $participations->field('crs_id')->EQ($topics_filter->field('crs_id')))
                        )
                        ->addDependency(
                            $this->tf->TableJoin($participations, $categories, $participations->field('crs_id')->EQ($categories->field('crs_id')))
                        );

        if ($accomodation_active) {
            $nights_src = $this->tf->Table(self::HEAD_USERCOURSE_NIGHTS_TABLE, 'nights_src')
                            ->addField($this->tf->field('crs_id'))
                            ->addField($this->tf->field('usr_id'))
                            ->addField($this->tf->field('list_data'));

            $nights_space = $this->tf->TableSpace()
                            ->addTablePrimary($nights_src)
                            ->setRootTable($nights_src)
                            ->groupBy($nights_src->field('crs_id'))
                            ->groupBy($nights_src->field('usr_id'));
            $nights_space->request($nights_src->field('crs_id'))
                            ->request($nights_src->field('usr_id'))
                            ->request($this->tf->groupConcat('nights', $this->tf->dateFormat('nights', $nights_src->field('list_data'))))
                            ->addFilter($nights_src->field('usr_id')->EQ()->int((int) $this->user_id));
            $nights = $this->tf->DerivedTable($nights_space, 'nights');

            $space = $space
                ->addTableSecondary($nights)
                ->addDependency(
                    $this->tf->TableLeftJoin($participations, $nights, $participations->field('crs_id')->EQ($nights->field('crs_id'))
                        ->_AND($participations->field('usr_id')->EQ($nights->field('usr_id'))))
                );
        }

        if ($superior_overview_and_invisible) {
            $space->addFilter(
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

        $this->possiblyApplyYearToSpace($settings['year']);
        $this->possiblyApplyTypesToSpace($settings['types']);
        $this->possiblyApplyEduProgrammeToSpace($settings['edu_programme']);
        $this->possiblyApplyTopicsToSpace($settings['topics']);
        $this->possiblyApplyCategoriesToSpace($settings['categories']);
        $this->possiblyApplyBookingStatusToSpace($settings['booking_status']);
        $this->possiblyApplyParticipationStatusToSpace($settings['participation_status']);
    }

    private function getFilterSettings(array $settings)
    {
        $filter = $this->filter();
        $settings = call_user_func_array(array($filter, "content"), $settings);
        return $settings;
    }

    private function possiblyApplyYearToSpace($year)
    {
        if ($year === 0) {
            $year = $this->getDefaultYear();
        }
        $begin_date = $year . '-01-01';
        $end_date = $year . '-12-31';
        $begin_date_f = $this->space()->table('crs')->field('begin_date');
        $end_date_f = $this->space()->table('crs')->field('end_date');

        $booking_date_f = $this->space()->table('usrcrs')->field('booking_date');
        $waiting_date_f = $this->space()->table('usrcrs')->field('waiting_date');
        $participation_date_f = $this->space()->table('usrcrs')->field('ps_acquired_date');

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
        $this->space()->addFilter($predicate_self_learn->_OR($predicate_not_self_learn));
    }

    private function possiblyApplyTypesToSpace(array $types)
    {
        if (count($types) > 0) {
            $type_f = $this->space()->table('crs')->field('crs_type');
            $predicate = $type_f->IN($this->pf->list_string_by_array($types));
            $this->space()->addFilter($predicate);
        }
    }

    private function possiblyApplyTopicsToSpace(array $topics)
    {
        if (count($topics) > 0) {
            $topics_space = $this->space->table('topics_filter')->space();
            $topics_f = $topics_space->table('top_src')->field('list_data');
            $topics_space->addFilter($topics_f->IN($this->pf->list_string_by_array($topics)));
            $this->space->forceRelevant($this->space->table('topics_filter'));
        }
    }

    private function possiblyApplyCategoriesToSpace(array $categories)
    {
        if (count($categories) > 0) {
            $categories_space = $this->space->table('categories')->space();
            $categories_f = $categories_space->table('cat_src')->field('list_data');
            $categories_space->addFilter($categories_f->IN($this->pf->list_string_by_array($categories)));
            $this->space->forceRelevant($this->space->table('categories'));
        }
    }

    private function possiblyApplyEduProgrammeToSpace(array $edu_programmes)
    {
        if (count($edu_programmes) > 0) {
            $edu_programme_f = $this->space()->table('crs')->field('edu_programme');
            $predicate = $edu_programme_f->IN($this->pf->list_string_by_array($edu_programmes));
            $this->space()->addFilter($predicate);
        }
    }

    private function possiblyApplyBookingStatusToSpace(array $booking_status)
    {
        if (count($booking_status) > 0) {
            $type_f = $this->space->table('usrcrs')->field('booking_status');
            $predicate = $type_f->IN($this->pf->list_string_by_array($booking_status));
            $this->space()->addFilter($predicate);
        }
    }

    private function possiblyApplyParticipationStatusToSpace(array $participation_status)
    {
        if (count($participation_status) > 0) {
            $type_f = $this->space->table('usrcrs')->field('participation_status');
            $predicate = $type_f->IN($this->pf->list_string_by_array($participation_status));
            $this->space()->addFilter($predicate);
        }
    }

    public function configureTable(\SelectableReportTableGUI $table)
    {
        $space = $this->space();
        $table->setRowTemplate('tpl.edubio_row.html', $this->plugin->getDirectory());
        $table
            ->defineFieldColumn(
                $this->plugin->txt('crs_title'),
                'crs_title',
                [ 'crs_title' => $space->table('crs')->field('title')
                    ,'crs_id' => $space->table('crs')->field('crs_id')
                    ]
            )
            ->defineFieldColumn(
                $this->plugin->txt('crs_type'),
                'crs_type',
                ['crs_type' => $space->table('crs')->field('crs_type')],
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('crs_date'),
                'crs_date',
                [ 'begin_date' => $this->tf->ifThenElse(
                    'begin_date',
                    $space->table('crs')->field('begin_date')->IS_NULL()
                            ->_OR($space->table('crs')->field('begin_date')
                                    ->EQ($this->pf->str('0001-01-01'))),
                    $space->table('usrcrs')->field('booking_date'),
                    $space->table('crs')->field('begin_date')
                )
                    , 'end_date' => $this->tf->ifThenElse(
                        'end_date',
                        $space->table('crs')->field('end_date')->IS_NULL()
                            ->_OR($space->table('crs')->field('end_date')
                                    ->EQ($this->pf->str('0001-01-01'))),
                        $space->table('usrcrs')->field('ps_acquired_date'),
                        $space->table('crs')->field('end_date')
                    )
                    ],
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('venue'),
                'venue',
                ['venue' => $space->table('crs')->field('venue')],
                true
            );

        if ($this->isAccomodationActive()) {
            $table = $table
                ->defineFieldColumn(
                    $this->plugin->txt('accomodation'),
                    'accomodation',
                    ['accomodation' => $space->table('crs')->field('accomodation')],
                    true
                )
                ->defineFieldColumn(
                    $this->plugin->txt('nights'),
                    'nights',
                    ['nights' => $space->table('nights')->field('nights')],
                    true
                );
        }

        $table = $table
            ->defineFieldColumn(
                $this->plugin->txt('provider'),
                'provider',
                ['provider' => $space->table('crs')->field('provider')],
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('trainer'),
                'tutors',
                ['tutors' => $space->table('tut')->field('tutors')],
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('fee'),
                'fee',
                ['fee' => $space->table('crs')->field('fee')],
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('booking_status'),
                'booking_status',
                ['booking_status' => $space->table('usrcrs')->field('booking_status')],
                false,
                true,
                false,
                true
            )
            ->defineFieldColumn(
                $this->plugin->txt('participation_status'),
                'participation_status',
                ['participation_status' => $space->table('usrcrs')->field('participation_status')],
                false,
                true,
                false,
                true
            );

        if ($this->isEduTrackingActive()) {
            $table = $table->defineFieldColumn(
                $this->plugin->txt('sum_idd_achieved'),
                'idd_learning_time',
                ['idd_learning_time' => $this->tf->IfThenElse(
                    'idd_learning_time',
                    $this->pf->_ALL(
                        $this->space->table('usrcrs')
                            ->field('participation_status')
                            ->EQ($this->pf->str('successful')),
                        $this->space->table('usrcrs')
                            ->field('booking_status')
                            ->EQ($this->pf->str('participant'))
                    ),
                    $this->tf->IfThenElse('', $space->table('usrcrs')->field('idd_learning_time')->IS_NULL(), $space->table('crs')->field('idd_learning_time'), $space->table('usrcrs')->field('idd_learning_time')),
                    $this->tf->ConstInt('', 0)
                )]
            )
            ->defineFieldColumn(
                $this->plugin->txt('sum_idd_forecast'),
                'idd_learning_time_booked',
                ['idd_learning_time_booked' => $this->tf->IfThenElse(
                    'idd_learning_time_booked'
                    ,
                    $this->pf->_ALL(
                        $this->space->table('usrcrs')
                            ->field('participation_status')
                            ->EQ($this->pf->str('successful')),
                        $this->space->table('usrcrs')
                            ->field('booking_status')
                            ->EQ($this->pf->str('participant'))
                    ),
                    $this->tf->IfThenElse('', $space->table('usrcrs')->field('idd_learning_time')->IS_NULL(), $space->table('crs')->field('idd_learning_time'), $space->table('usrcrs')->field('idd_learning_time')),
                    $this->tf->IfThenElse('', $this->space->table('usrcrs')->field('booking_status')
                            ->IN($this->pf->list_string_by_array(['participant','waiting','approval_pending']))
                            ->_AND($space->table('usrcrs')->field('participation_status')->NEQ($this->pf->str('absent'))
                            ->_OR($space->table('usrcrs')->field('participation_status')->IS_NULL())), $this->space->table('crs')->field('idd_learning_time'), $this->tf->ConstInt('', 0))
                )]
            );
        }

        $table = $table->defineFieldColumn(
            $this->plugin->txt('actions'),
            'actions',
            [],
            false,
            false,
            false
            );
        $table->setDefaultOrderColumn('crs_title', \SelectableReportTableGUI::ORDER_ASC);
        $table->prepareTableAndSetRelevantFields($space);
        $this->space = $space;

        return $table;
    }

    protected function postprocessRowHTML(array $row)
    {
        $row = $this->postprocessRowCommon($row);
        $ref_id = array_shift(\ilObject::_getAllReferences($row['crs_id']));
        if ($ref_id) {
            $tpl = new \ilTemplate("tpl.link_entry.html", true, true, $this->plugin->getDirectory());
            $tpl->setVariable('TITLE', $row['crs_title']);
            $tpl->setVariable('LINK', \ilLink::_getStaticLink($ref_id, 'crs'));
            $row['crs_title'] = $tpl->get();
        }

        $row["actions"] = $this->getActionMenu($row["crs_id"], $row["participation_status_code"]);
        return $row;
    }

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

        if (in_array($row['booking_status'], array("cancelled_after_deadline", "waiting_cancelled"))) {
            $row['participation_status'] = "-";
        }
        $row['booking_status'] = $this->plugin->txt($row['booking_status']);
        if ((string) $row['participation_status'] === '') {
            $row['participation_status'] = 'none';
        }

        $row['participation_status_code'] = $row['participation_status'];

        if ($row['participation_status'] != "-") {
            $row['participation_status'] = $this->plugin->txt($row['participation_status']);
        }

        if ($this->isEduTrackingActive()) {
            $row['idd_learning_time'] = $this->minutesToTimeString((int) $row['idd_learning_time']) . ' ' . $this->plugin->txt('hours');
            $row['idd_learning_time_booked'] = $this->minutesToTimeString((int) $row['idd_learning_time_booked']) . ' ' . $this->plugin->txt('hours');
        } else {
            unset($row['idd_learning_time']);
            unset($row['idd_learning_time_booked']);
        }

        $fee = "-";
        if (!is_null($row["fee"]) && $row["fee"] !== "") {
            $fee = number_format((float) $row["fee"], 2, ",", "");
        }
        $row["fee"] = $fee;

        foreach ($row as $key => &$value) {
            if ($value === null || $value == "") {
                $value = '-';
            }
        }

        return $row;
    }

    /**
     * Get the action menu
     *
     * @param int 	$crs_id
     * @param string 	$participation_status
     *
     * @return string
     */
    protected function getActionMenu($crs_id, $participation_status)
    {
        include_once("Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
        $current_selection_list = new \ilAdvancedSelectionListGUI();
        $current_selection_list->setAsynch(false);
        $current_selection_list->setAsynchUrl(true);
        $current_selection_list->setListTitle($this->plugin->txt("actions"));
        $current_selection_list->setId($crs_id);
        $current_selection_list->setSelectionHeaderClass("small");
        $current_selection_list->setItemLinkClass("xsmall");
        $current_selection_list->setLinksMode("il_ContainerItemCommand2");
        $current_selection_list->setHeaderIcon(\ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
        $current_selection_list->setUseImages(false);
        $current_selection_list->setAdditionalToggleElement("id" . $crs_id, "ilContainerListItemOuterHighlight");

        foreach ($this->getActionMenuItems($crs_id, $participation_status) as $key => $value) {
            $current_selection_list->addItem($value["title"], "", $value["link"], $value["image"], "", $value["frame"]);
        }

        return $current_selection_list->getHTML();
    }

    /**
     * Get times for action menu
     *
     * @param int 	$crs_id
     * @param string 	$participation_status
     *
     * @return string[]
     */
    protected function getActionMenuItems($crs_id, $participation_status)
    {
        $items = array();

        $cert_download_validator = new \ilCertificateDownloadValidator();
        $downloadable = $cert_download_validator->isCertificateDownloadable($this->user_id, $crs_id);
        $part_successful = $participation_status == "successful";

        // GOA special hack for #3410
        $check_imported_certificate = false;
        if ($crs_id < 0) {
            $file_storage = new FileStorage\ilCertificateStorage($crs_id);
            $file_storage = $file_storage->withUserId($this->usrId());
            $check_imported_certificate =
                in_array(CLIENT_ID, \ilEduBiographyReportGUI::$import_client) &&
                !is_null(
                    $file_storage->getPathOfCurrentCertificate(
                        \ilEduBiographyReportGUI::GOA20_FILE_NAME
                    )
                )
            ;
        }
        // GOA special hack for #3410

        if (
            ($check_imported_certificate || $downloadable) &&
            $part_successful &&
            $this->allowedDownload()
        ) {
            $this->ctrl->setParameterByClass(
                "ilEduBiographyReportGUI",
                \ilEduBiographyReportGUI::P_CRS_ID,
                $crs_id
            );
            $this->ctrl->setParameterByClass(
                "ilEduBiographyReportGUI",
                \ilEduBiographyReportGUI::P_CERT_USER_ID,
                $this->user_id
            );
            $link = $this->ctrl->getLinkTargetByClass(
                "ilEduBiographyReportGUI",
                \ilEduBiographyReportGUI::CMD_DELIVER_CERTIFICATE,
                "",
                true
            );
            $this->ctrl->setParameterByClass(
                "ilEduBiographyReportGUI",
                \ilEduBiographyReportGUI::P_CRS_ID,
                null
            );
            $this->ctrl->setParameterByClass(
                "ilEduBiographyReportGUI",
                \ilEduBiographyReportGUI::P_CERT_USER_ID,
                null
            );

            $items[] = array("title" => $this->plugin->txt("deliver_certificate"), "link" => $link, "image" => "", "frame" => "");
        }

        $link = $this->getLinkToScaledFeedback($crs_id);
        if (!is_null($link)) {
            $items[] = array("title" => $this->plugin->txt("to_scaled_feedback"), "link" => $link, "image" => "", "frame" => "");
        }

        $ref_id = array_shift(\ilObject::_getAllReferences($crs_id));
        if (!is_null($ref_id) && $this->settings->getRecommendationAllowed()) {
            $ref_id = (int) $ref_id;
            $recommendation_data = $this->getContentBuilderDataForTemplateId(
                self::SEND_RECOMMENDATION_ID,
                $ref_id
            );

            if (!is_null($recommendation_data->getTemplateId())) {
                $subject = rawurlencode($recommendation_data->getSubject());
                $body = rawurlencode($recommendation_data->getPlainMessage());

                $mail_link = "mailto:?body=$body&subject=$subject";
                $items[] = array(
                    "title" => $this->plugin->txt("recommend_training"),
                    "link" => $mail_link,
                    "image" => "",
                    "frame" => ""
                );
            }
        }

        return $items;
    }

    protected function getLinkToScaledFeedback($crs_id)
    {
        $scaled_feedback = $this->getScaledFeedback($crs_id);
        if (is_null($scaled_feedback)) {
            return null;
        }

        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/ScaledFeedback/classes/class.ilObjScaledFeedbackGUI.php";
        $this->ctrl->setParameterByClass("ilObjScaledFeedbackGUI", "ref_id", $scaled_feedback);
        $link = $this->ctrl->getLinkTargetByClass(
            [
                "ilObjPluginDispatchGUI",
                "ilObjScaledFeedbackGUI"
            ],
            \ilObjScaledFeedbackGUI::CMD_SHOW,
            "",
            false,
            false
        );
        $this->ctrl->setParameterByClass("ilObjFcaledFeedbackGUI", "ref_id", null);

        return $link;
    }

    protected function getScaledFeedback($crs_id)
    {
        $ref_id = array_shift(\ilObject::_getAllReferences($crs_id));
        if (!is_null($ref_id)) {
            $scaled_feedbacks = $this->g_tree->getSubTree(
                $this->g_tree->getNodeData($ref_id),
                false,
                "xfbk"
            );

            foreach ($scaled_feedbacks as $scaled_feedback) {
                if ($this->g_access->checkAccess("read", "", $scaled_feedback)) {
                    return $scaled_feedback;
                }
            }
        }

        return null;
    }

    protected function getContentBuilderDataForTemplateId(
        string $tpl_id,
        int $crs_ref_id
    ) : \ilTMSMailContentBuilder {
        $mail = new \ilTMSMailing();
        $contexts = $mail->getStandardContexts();
        $contexts["ilTMSMailContextCourse"] = new \ilTMSMailContextCourse($crs_ref_id);
        $contexts["ilTMSMailContextUser"] = new \ilTMSMailContextUser($this->user_id);
        $cb = $mail->getContentBuilder();
        $cb = $cb->withContexts($contexts);
        return $cb->withData($tpl_id);
    }


    protected $allowed_cert_download = null;
    /**
     * Check user is allowed to download the certificate. Return is buffered.
     */
    public function allowedDownload() : bool
    {
        if ($this->user_id == $this->g_user->getId()) {
            return true;
        }
        if ($this->allowed_cert_download === null) {
            $this->allowed_cert_download = $this->uol->isUserIdVisibleToUser($this->user_id, $this->g_user);
        }
        return $this->allowed_cert_download;
    }

    protected function getSettings() : Settings
    {
        return $this->settings;
    }
}
