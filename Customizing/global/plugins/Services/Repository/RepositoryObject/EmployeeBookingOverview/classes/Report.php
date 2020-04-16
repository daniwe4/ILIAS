<?php
namespace CaT\Plugins\EmployeeBookingOverview;

/**
 * Interface for the pluginobject to make it more testable.
 *
 * @author
 * @copyright Extended GPL, see LICENSE
 */

use CaT\Plugins\EmployeeBookingOverview\Settings\Settings;
use ILIAS\TMS\Filter;
use ILIAS\TMS\TableRelations;
use ILIAS\TMS\ReportUtilities\TreeObjectDiscovery;

require_once 'Services/TMS/ReportUtilities/classes/class.ilUDFWrapper.php';

class Report
{
    const HEAD_COURSE_TABLE = "hhd_crs";
    const HEAD_COURSE_TOPICS_TABLE = "hhd_crs_topics";
    const HEAD_COURSE_CATEGORIES_TABLE = "hhd_crs_categories";
    const HEAD_USERCOURSE_TABLE = "hhd_usrcrs";
    const HEAD_COURSE_TUT_TABLE = "hhd_crs_tut";
    const USR_DATA_TABLE = 'usr_data';
    const HEAD_USERCOURSE_NIGHTS_TABLE = "hhd_usrcrs_nights";

    const AGGREGATE_ID_CRS_TYPE = 'crs_type';
    const AGGREGATE_ID_EDU_PROGRAMME = 'edu_programme';
    const AGGREGATE_ID_CRS_TOPICS = 'crs_topics';
    const AGGREGATE_ID_CATEGORIES = 'categories';

    use \ilUDFWrapper;

    /**
     * @var \ilObjEmployeeBookingOverview
     */
    protected $object;

    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @var \ilObjUser
     */
    protected $usr;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var TreeObjectDiscovery
     */
    protected $o_d;

    /**
     * @var UserOrguLocator
     */
    protected $uol;

    /**
     * @var Filter\PredicateFactory
     */
    protected $pf;

    /**
     * @var TableRelations\TableFactory
     */
    protected $tf;

    /**
     * @var Filter\TypeFactory
     */
    protected $tyf;

    /**
     * @var Filter\FilterFactory
     */
    protected $ff;

    /**
     * @var string
     */
    protected $plugin_dir;

    /**
     * @var Settings\Settings
     */
    protected $settings;

    /**
     * @var Filter\Filters\Filter | null
     */
    protected $filter = null;

    /**
     * @var TableRelations\TableSpace | null
     */
    protected $space = null;

    /**
     * @var bool | null
     */
    protected $edu_tracking_active = null;

    /**
     * @var bool | null
     */
    protected $accomodation_active = null;

    /**
     * @var bool | null
     */
    protected $course_members_active = null;

    /**
     * @var int[] | null
     */
    protected $relevant_training_ids = null;

    public function __construct(
        \ilObjEmployeeBookingOverview $object,
        \ilDBInterface $db,
        \ilObjUser $usr,
        \Closure $txt,
        TreeObjectDiscovery $o_d,
        UserOrguLocator $uol,
        Filter\PredicateFactory $pf,
        TableRelations\TableFactory $tf,
        Filter\TypeFactory $tyf,
        Filter\FilterFactory $ff,
        string $plugin_dir
    ) {
        $this->object = $object;
        $this->db = $db;
        $this->usr = $usr;
        $this->txt = $txt;
        $this->o_d = $o_d;
        $this->settings = $object->getSettings();
        $this->uol = $uol;
        $this->pf = $pf;
        $this->tf = $tf;
        $this->tyf = $tyf;
        $this->ff = $ff;
        $this->plugin_dir = $plugin_dir;
    }

    /**
     * Get the data for the report.
     *
     * @return	array
     */
    public function fetchData()
    {
        $res = $this->db->query($this->interpreter()->getSql($this->space()->query()));
        $return = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $return[] = $this->postprocessRowHTML($row);
        }
        return $return;
    }

    public function setUserQueryLink($query_link)
    {
        $this->query_link = $query_link;
        return $this;
    }

    /**
     * Get the filter for the report.
     *
     * @return Filter\Filters\Sequence
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
     * @return Filter\Filters\Sequence
     */
    protected function buildFilter()
    {
        $ff = $this->ff;
        $tyf = $this->tyf;
        return $ff->sequence(
            $ff->dateperiod(
                $this->txt('crs_date'),
                ''
            ),
            $ff->text($this->txt('filter_lastname_login_email'), '')->withQueryLink($this->query_link),
            $ff->multiselectsearch(
                $this->txt('orgus_filter'),
                '',
                $this->orgusOptions()
            ),
            $ff->option($this->txt('orgus_filter_recursive'), ''),
            $ff->sequence(
                $ff->multiselectsearch(
                    $this->txt('filter_types'),
                    '',
                    $this->crsTypeOptions()
                ),
                $ff->multiselectsearch(
                    $this->txt('filter_categories'),
                    '',
                    $this->crsCategoriesOptions()
                ),
                $ff->multiselectsearch(
                    $this->txt('filter_topics'),
                    '',
                    $this->crsTopicOptions()
                ),
                $ff->multiselectsearch(
                    $this->txt('edu_programme'),
                    '',
                    $this->eduProgrammeOptions()
                ),
                $ff->multiselectsearch(
                    $this->txt('booking_status'),
                    '',
                    $this->bookingStatusOptions()
                ),
                $ff->multiselectsearch(
                    $this->txt('participation_status'),
                    '',
                    $this->participationStatusOptions()
                )
            )
        )->map(
            function (
                $period_start,
                $period_end,
                $filter_val,
                $orgus,
                $recursive,
                $types,
                $categories,
                $topics,
                $edu_programmes,
                $booking_status,
                $participation_status
            ) {
                return ['period_start' => $period_start,
                            'period_end' => $period_end,
                            'usr_filter' => $filter_val,
                            'orgus' => $orgus,
                            'orgus_recursive' => $recursive,
                            'types' => $types,
                            'categories' => $categories,
                            'topics' => $topics,
                            'edu_programmes' => $edu_programmes,
                            'booking_status' => $booking_status,
                            'participation_status' => $participation_status
                            ];
            },
            $tyf->dict(
                [	'period_start' => $tyf->cls("\\DateTime"),
                    'period_end' => $tyf->cls("\\DateTime"),
                    'usr_filter' => $tyf->string(),
                    'orgus' => $tyf->lst($tyf->int()),
                    'orgus_recursive' => $tyf->bool(),
                    'types' => $tyf->lst($tyf->string()),
                    'categories' => $tyf->lst($tyf->string()),
                    'topics' => $tyf->lst($tyf->string()),
                    'edu_programmes' => $tyf->lst($tyf->string()),
                    'booking_status' => $tyf->lst($tyf->string()),
                    'participation_status' => $tyf->lst($tyf->string())]
            )
        );
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
        $this->applyCrsDatesFilter($settings['period_start'], $settings['period_end']);
        $this->possiblyAddUserFilter($settings['usr_filter']);
        $this->possiblyApplyTypesToSpace($settings['types']);
        $this->possiblyApplyEduProgrammeToSpace($settings['edu_programmes']);
        $this->possiblyApplyTopicsToSpace($settings['topics']);
        $this->possiblyApplyCategoriesToSpace($settings['categories']);
        $this->possiblyApplyBookingStatusToSpace($settings['booking_status']);
        $this->possiblyApplyParticipationStatusToSpace($settings['participation_status']);
        $this->applyOrgusFilter($settings['orgus'], $settings['orgus_recursive']);
    }

    protected function applyOrgusFilter(array $orgus, $recursive)
    {
        $relevant_users = [];
        if (count($orgus) > 0) {
            $relevant_users =
                        array_intersect($this->uol->getUserIdUnderAuthorityOfUserByOrgus(
                            (int) $this->usr->getId(),
                            $orgus,
                            $recursive
                        ), $this->visibleUsers());
        } else {
            $relevant_users = $this->visibleUsers();
        }
        if (count($relevant_users) === 0) {
            $this->space()->addFilter($this->pf->_FALSE());
        } else {
            $this->space()->addFilter(
                $this->space()
                        ->table('usrcrs')
                        ->field('usr_id')
                        ->IN($this->pf->list_int_by_array($relevant_users))
            );
        }
    }

    /**
     * Add a filter predicate to space which filters accoding to dates.
     * Here, if no course dates are given, the booking- and participation
     * dates will be taken into account.
     *
     * @param	\DateTime	$period_start
     * @param	\DateTime	$period_end
     * @return	void
     */
    protected function applyCrsDatesFilter(\DateTime $period_start, \DateTime $period_end)
    {
        $begin_date = $period_start->format('Y-m-d');
        $end_date = $period_end->format('Y-m-d');

        $begin_date_f = $this->space()->table('crs')->field('begin_date');
        $end_date_f = $this->space()->table('crs')->field('end_date');

        $booking_date_f = $this->space()->table('usrcrs')->field('booking_date');
        $participation_date_f = $this->space()->table('usrcrs')->field('ps_acquired_date');

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

        $this->space()->addFilter($predicate_self_learn->_OR($predicate_not_self_learn));
    }

    /**
     * Add a lastname filter predicate to space, if lastname does not
     * consist of whitespaces.
     *
     * @param	string	$lastname
     * @return	void
     */
    private function possiblyAddUserFilter($filter_val)
    {
        $filter_val = trim((string) $filter_val);
        if ($filter_val !== '') {
            $lastname_field = $this->space()->table('usr')->field('lastname');
            $email_field = $this->space()->table('usr')->field('email');
            $login_field = $this->space()->table('usr')->field('login');
            $this->space()->addFilter(
                $this->pf->_ANY(
                    $lastname_field->LIKE($this->pf->str($filter_val . '%')),
                    $email_field->LIKE($this->pf->str($filter_val . '%')),
                    $login_field->LIKE($this->pf->str($filter_val . '%'))
                )
            );
        }
    }

    /**
     * Add a filter predicate to space, that filters according to topics.
     *
     * @param	string[]	$topics
     * @return	void
     */
    private function possiblyApplyTopicsToSpace(array $topics)
    {
        if (count($topics) > 0) {
            $topics_space = $this->space()->table('topics_filter')->space();
            $topics_f = $topics_space->table('top_src')->field('list_data');
            $topics_space->addFilter($topics_f->IN($this->pf->list_string_by_array($topics)));
            $this->space()->forceRelevant($this->space()->table('topics_filter'));
        }
    }

    /**
     * Add a filter predicate to space, that filters according to categories.
     *
     * @param	string[]	$categories
     * @return	void
     */
    private function possiblyApplyCategoriesToSpace(array $categories)
    {
        if (count($categories) > 0) {
            $categories_space = $this->space->table('categories')->space();
            $categories_f = $categories_space->table('cat_src')->field('list_data');
            $categories_space->addFilter($categories_f->IN($this->pf->list_string_by_array($categories)));
            $this->space->forceRelevant($this->space->table('categories'));
        }
    }

    /**
     * Add a filter predicate to space, that filters according to edu programmes.
     *
     * @param	string[]	$edu_programmes
     * @return	void
     */
    private function possiblyApplyEduProgrammeToSpace(array $edu_programmes)
    {
        if (count($edu_programmes) > 0) {
            $edu_programme_f = $this->space()->table('crs')->field('edu_programme');
            $predicate = $edu_programme_f->IN($this->pf->list_string_by_array($edu_programmes));
            $this->space()->addFilter($predicate);
        }
    }

    /**
     * Add a filter predicate to space, that filters according to crs-type.
     *
     * @param	string[]	$types
     * @return	void
     */
    private function possiblyApplyTypesToSpace(array $types)
    {
        if (count($types) > 0) {
            $type_f = $this->space()->table('crs')->field('crs_type');
            $predicate = $type_f->IN($this->pf->list_string_by_array($types));
            $this->space()->addFilter($predicate);
        }
    }

    /**
     * Add a filter predicate to space, that filters participations according to
     * booking status.
     *
     * @param	string[]	$booking_status
     * @return	void
     */
    private function possiblyApplyBookingStatusToSpace(array $booking_status)
    {
        if (count($booking_status) > 0) {
            $booking_status_f = $this->space()->table('usrcrs')->field('booking_status');
            $predicate = $booking_status_f->IN($this->pf->list_string_by_array($booking_status));
            $this->space()->addFilter($predicate);
        }
    }

    /**
     * Add a filter predicate to space, that filters participations according to
     * participation status.
     *
     * @param	string[]	$participation_status
     * @return	void
     */
    private function possiblyApplyParticipationStatusToSpace(array $participation_status)
    {
        if (count($participation_status) > 0) {
            $standard_p_status = array_intersect($participation_status, self::$default_p_status);
            $predicate_default = $this->pf->_FALSE();
            if (count($standard_p_status) > 0) {
                $participation_status_f = $this->space()->table('usrcrs')->field('participation_status');
                $predicate_default = $participation_status_f->IN($this->pf->list_string_by_array($participation_status));
                if (in_array('none', $participation_status)) {
                    $predicate_default = $predicate_default->_OR($participation_status_f->IS_NULL());
                }
            }
            $predicate_custom = $this->pf->_FALSE();
            if ($this->isCourseMembersActive()) {
                $custom_p_status_f = $this->space()->table('usrcrs')->field('custom_p_status');
                $custom_p_status = array_diff($participation_status, self::$default_p_status);
                if (count($custom_p_status) > 0) {
                    $predicate_custom = $predicate_custom->_OR($custom_p_status_f->IN($this->pf->list_string_by_array($custom_p_status)));
                }
            }
            $this->space()->addFilter($this->pf->_ANY($predicate_default, $predicate_custom));
        }
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
        $invisible_topics = count($this->settings->getInvisibleCourseTopics()) > 0;
        $edu_tracking_active = $this->isEduTrackingActive();
        $accomodation_active = $this->isAccomodationActive();
        $course_members_active = $this->isCourseMembersActive();

        $crs_data = $this->tf->Table(self::HEAD_COURSE_TABLE, 'crs')
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('title'))
                        ->addField($this->tf->field('crs_type'))
                        ->addField($this->tf->field('venue'))
                        ->addField($this->tf->field('provider'))
                        ->addField($this->tf->field('begin_date'))
                        ->addField($this->tf->field('end_date'))
                        ->addField($this->tf->field('fee'))
                        ->addField($this->tf->field('edu_programme'));

        if ($edu_tracking_active) {
            $crs_data = $crs_data->addField($this->tf->field('idd_learning_time'));
        }

        if ($accomodation_active) {
            $crs_data = $crs_data
                ->addField($this->tf->field('accomodation'));
        }

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

        $visible_count_field = $invisible_topics ?
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

        $invisible_count_field = $invisible_topics ?
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
                        ->addField($this->tf->field('ps_acquired_date'))
                        ->addField($this->tf->field('waiting_date'));

        if ($edu_tracking_active) {
            $participations = $participations->addField($this->tf->field('idd_learning_time'));
        }
        if ($course_members_active) {
            $participations = $participations->addField($this->tf->field('custom_p_status'));
        }
        $participations = $participations
                        ->addConstraint($participations->field('booking_status')->IN(
                            $this->pf->list_string_by_array(
                                [
                                    'participant',
                                    'waiting',
                                    'approval_pending',
                                    'waiting_cancelled'
                                ]
                            )
                        )->_OR($participations->field('booking_status')->IN(
                            $this->pf->list_string_by_array(
                                [
                                    'cancelled_after_deadline'
                                ]
                            )
                        )->_AND($participations->field('booking_date')->IS_NULL()->_NOT())));
        $usr_data = $this->tf->Table(self::USR_DATA_TABLE, 'usr')
                    ->addField($this->tf->field('usr_id'))
                    ->addField($this->tf->field('lastname'))
                    ->addField($this->tf->field('login'))
                    ->addField($this->tf->field('email'));
        $usr_data_presentation = $this->configuredUserDataTable($this->tf, 'usr_data_presentation');
        $space = $this->tf->TableSpace()
                        ->addTablePrimary($participations)
                        ->addTablePrimary($usr_data)
                        ->addTableSecondary($tutors)
                        ->addTableSecondary($crs_data)
                        ->addTableSecondary($invisible_topics)
                        ->addTableSecondary($topics_filter)
                        ->addTableSecondary($categories)
                        ->addTableSecondary($usr_data_presentation)
                        ->setRootTable($participations)
                        ->addDependency(
                            $this->tf->TableJoin(
                                $participations,
                                $crs_data,
                                $participations->field('crs_id')->EQ($crs_data->field('crs_id'))
                            )
                        )
                        ->addDependency(
                            $this->tf->TableLeftJoin(
                                $participations,
                                $tutors,
                                $participations->field('crs_id')->EQ($tutors->field('crs_id'))
                            )
                        )
                        ->addDependency(
                            $this->tf->TableJoin(
                                $participations,
                                $topics_filter,
                                $participations->field('crs_id')->EQ($topics_filter->field('crs_id'))
                            )
                        )
                        ->addDependency(
                            $this->tf->TableLeftJoin(
                                $participations,
                                $invisible_topics,
                                $participations->field('crs_id')->EQ($invisible_topics->field('crs_id'))
                            )
                        )
                        ->addDependency(
                            $this->tf->TableJoin(
                                $participations,
                                $categories,
                                $participations->field('crs_id')->EQ($categories->field('crs_id'))
                            )
                        )
                        ->addDependency(
                            $this->tf->TableJoin(
                                $usr_data,
                                $participations,
                                $participations->field('usr_id')->EQ($usr_data->field('usr_id'))
                            )
                        )
                        ->addDependency(
                            $this->tf->TableJoin(
                                $usr_data_presentation,
                                $participations,
                                $participations->field('usr_id')->EQ($usr_data_presentation->field('usr_id'))
                            )
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
        $space = $this->appendUDFsToSpace(
            $this->tf,
            $this->pf,
            $space,
            $usr_data,
            'usr_id'
        );

        if ($invisible_topics) {
            $space->addFilter(
                $this->pf->_ANY(
                    $invisible_topics->field('invisible_topics')->EQ($this->pf->int(0))->_OR($invisible_topics->field('invisible_topics')->IS_NULL()),
                    $invisible_topics->field('invisible_topics')->GT($this->pf->int(0))->_AND($invisible_topics->field('visible_topics')->GT($this->pf->int(0)))
                )
            );
        }

        return $this->possiblyConstrainCourses($space);
    }

    /**
     * If local mode is set, constrain courses accordingly.
     * (only courses within containing categorsy of the report).
     *
     * @param	TableRelations\Tables\TableSpace	$space
     */
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
        $space = $this->space();
        $table->setRowTemplate('tpl.report_row.html', $this->plugin_dir);

        $table = $this->addUserDataToTable(
            $space,
            $table,
            'usr_data_presentation'
        );
        $table = $this->addUDFColumnsToTable($space, $table);
        $table
            ->defineFieldColumn(
                $this->txt('crs_title'),
                'crs_title',
                [ 'crs_title' => $space->table('crs')->field('title')
                    ,'crs_id' => $space->table('crs')->field('crs_id')
                    ]
            )
            ->defineFieldColumn(
                $this->txt('crs_type'),
                'crs_type',
                ['crs_type' => $space->table('crs')->field('crs_type')],
                true
            )
            ->defineFieldColumn(
                $this->txt('begin_date'),
                'begin_date',
                [ 'begin_date' => $this->tf->ifThenElse(
                    'begin_date',
                    $space->table('crs')->field('begin_date')->IS_NULL()
                            ->_OR($space->table('crs')->field('begin_date')
                                    ->EQ($this->pf->str('0001-01-01'))),
                    $space->table('usrcrs')->field('booking_date'),
                    $space->table('crs')->field('begin_date')
                )],
                true
            )
            ->defineFieldColumn(
                $this->txt('end_date'),
                'end_date',
                ['end_date' => $this->tf->ifThenElse(
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
                $this->txt('booking_status'),
                'booking_status',
                ['booking_status' => $space->table('usrcrs')->field('booking_status')],
                false,
                true,
                false,
                true
            )
            ->defineFieldColumn(
                $this->txt('booking_date'),
                'booking_date',
                ['booking_date' => $this->tf->ifThenElse(
                    'booking_date',
                    $space->table('usrcrs')->field('booking_status')->EQ($this->pf->str('participant')),
                    $space->table('usrcrs')->field('booking_date'),
                    $this->tf->ifThenElse(
                        '',
                        $this->pf->_ALL(
                            $space->table('usrcrs')->field('booking_status')->EQ($this->pf->str('waiting')),
                            $space->table('usrcrs')->field('waiting_date')->IS_NULL()->_NOT()
                        ),
                        $space->table('usrcrs')->field('waiting_date'),
                        $this->tf->constString('', '0001-01-01')
                    )
                )]
            )
        ;
        if ($this->isCourseMembersActive()) {
            $table->defineFieldColumn(
                $this->txt('participation_status'),
                'participation_status',
                ['participation_status' => $space->table('usrcrs')->field('participation_status'),
                'custom_p_status' => $space->table('usrcrs')->field('custom_p_status')
                ],
                false,
                true,
                false,
                true
            );
        } else {
            $table->defineFieldColumn(
                $this->txt('participation_status'),
                'participation_status',
                ['participation_status' => $space->table('usrcrs')->field('participation_status')],
                false,
                true,
                false,
                true
            );
        }
        if ($this->isEduTrackingActive()) {
            $table = $table->defineFieldColumn(
                $this->txt('sum_idd_achieved'),
                'idd_learning_time',
                ['idd_learning_time' => $this->tf->IfThenElse('idd_learning_time', $space
                        ->table('usrcrs')
                        ->field('participation_status')
                        ->EQ($this->pf->str('successful')), $this->tf->IfThenElse('', $space->table('usrcrs')->field('idd_learning_time')->IS_NULL(), $space->table('crs')->field('idd_learning_time'), $space->table('usrcrs')->field('idd_learning_time')), $this->tf->ConstInt('', 0))]
            )
            ->defineFieldColumn(
                $this->txt('sum_idd_forecast'),
                'idd_learning_time_booked',
                ['idd_learning_time_booked' => $this->tf->IfThenElse(
                    'idd_learning_time_booked',
                    $space->table('usrcrs')->field('participation_status')->EQ($this->pf->str('successful')),
                    $this->tf->IfThenElse(
                        '',
                        $space->table('usrcrs')->field('idd_learning_time')->IS_NULL(),
                        $space->table('crs')->field('idd_learning_time'),
                        $space->table('usrcrs')->field('idd_learning_time')
                    ),
                    $this->tf->IfThenElse(
                        '',
                        $this->space->table('usrcrs')->field('booking_status')
                             ->IN($this->pf->list_string_by_array(['participant','waiting']))
                             ->_AND($space->table('usrcrs')->field('participation_status')->NEQ($this->pf->str('absent'))
                             ->_OR($space->table('usrcrs')->field('participation_status')->IS_NULL())),
                        $this->space->table('crs')->field('idd_learning_time'),
                        $this->tf->ConstInt('', 0)
                    )
                )]
            );
        }
        $table->defineFieldColumn(
            $this->txt('venue'),
            'venue',
            ['venue' => $space->table('crs')->field('venue')],
            true
        );

        if ($this->isAccomodationActive()) {
            $table = $table
                ->defineFieldColumn(
                    $this->txt('accomodation'),
                    'accomodation',
                    ['accomodation' => $space->table('crs')->field('accomodation')],
                    true
                )
                ->defineFieldColumn(
                    $this->txt('nights'),
                    'nights',
                    ['nights' => $space->table('nights')->field('nights')],
                    true
                );
        }

        $table = $table
            ->defineFieldColumn(
                $this->txt('provider'),
                'provider',
                ['provider' => $space->table('crs')->field('provider')],
                true
            )
            ->defineFieldColumn(
                $this->txt('trainer'),
                'tutors',
                ['tutors' => $space->table('tut')->field('tutors')],
                true
            )
            ->defineFieldColumn(
                $this->txt('fee'),
                'fee',
                ['fee' => $space->table('crs')->field('fee')],
                true
            );
        $table->setDefaultOrderColumn($this->usrDataFieldId('lastname'), \SelectableReportTableGUI::ORDER_ASC);

        $this->space = $table->prepareTableAndSetRelevantFields($space);
        return $table;
    }

    /**
     * Checks the edu tracking plugin is active
     *
     * @return bool
     */
    public function isEduTrackingActive()
    {
        require_once("Services/Component/classes/class.ilPluginAdmin.php");
        if ($this->edu_tracking_active === null) {
            $this->edu_tracking_active = \ilPluginAdmin::isPluginActive("xetr");
        }
        return $this->edu_tracking_active;
    }

    /**
     * Checks if the accomodation-plugin is active
     *
     * @return bool
     */
    public function isAccomodationActive()
    {
        require_once("Services/Component/classes/class.ilPluginAdmin.php");
        if ($this->accomodation_active === null) {
            $this->accomodation_active = \ilPluginAdmin::isPluginActive("xoac");
        }
        return $this->accomodation_active;
    }


    /**
     * Checks the course members plugin is active
     *
     * @return bool
     */
    public function isCourseMembersActive()
    {
        require_once("Services/Component/classes/class.ilPluginAdmin.php");
        if ($this->course_members_active === null) {
            $this->course_members_active = \ilPluginAdmin::isPluginActive("xcmb");
        }
        return $this->course_members_active;
    }

    /**
     * Transforms minutes to showable time string
     *
     * @param int 	$minutes
     *
     * @return string
     */
    protected function minutesToTimeString($minutes)
    {
        assert('is_int($minutes)');
        $hours = floor($minutes / 60);
        $minutes = $minutes - $hours * 60;

        return str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT);
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
     * Fetch user-ids visible to report viewer.
     *
     * @return	int[]
     */
    public function visibleUsers()
    {
        if (!$this->visible_users) {
            $this->visible_users = $this->uol->getVisibleUserIds($this->usr);
        }
        return $this->visible_users;
    }

    /**
     * Get all standard user fields visible in Courses
     *
     * @return string[]
     */
    protected function getAllCourseVisibleStandardUserFields()
    {
        include_once './Services/PrivacySecurity/classes/class.ilExportFieldsInfo.php';
        $ef = \ilExportFieldsInfo::_getInstanceByType("crs");

        return $ef->getExportableFields();
    }


    /**
     * Get distinct course setting filter options
     *
     * @return string[string]
     */
    protected function crsTypeOptions()
    {
        return $this->getDistinct(self::HEAD_COURSE_TABLE, 'crs_type');
    }
    protected function crsTopicOptions()
    {
        $topics = $this->getDistinct(self::HEAD_COURSE_TOPICS_TABLE, 'list_data');
        $invisible_crs_topics = $this->getSettings()->getInvisibleCourseTopics();
        return array_diff($topics, $invisible_crs_topics);
    }
    protected function crsCategoriesOptions()
    {
        return $this->getDistinct(self::HEAD_COURSE_CATEGORIES_TABLE, 'list_data');
    }
    protected function eduProgrammeOptions()
    {
        return $this->getDistinct(self::HEAD_COURSE_TABLE, 'edu_programme');
    }

    /**
     * Get distinct participation and booking status filter options
     *
     * @return string[string]
     */
    protected function bookingStatusOptions()
    {
        $return = [];
        foreach (['participant'
                ,'waiting'
                ,'waiting_cancelled'
                ,'approval_pending'
                ,'cancelled_after_deadline'] as $val) {
            $return[$val] = $this->txt($val);
        }
        asort($return);
        return $return;
    }

    protected static $default_p_status = ['none', 'successful', 'absent'];

    protected function participationStatusOptions()
    {
        $return = [];
        foreach (self::$default_p_status as $id) {
            $return[$id] = $this->txt($id);
        }
        if ($this->isCourseMembersActive()) {
            $return = array_merge($return, $this->getDistinct('hhd_usrcrs', 'custom_p_status'));
        }
        asort($return);
        return $return;
    }

    /**
     * Get distinct entries in the column of a table
     *
     * @param	string	$table
     * @param	string	$column
     * @return string[]
     */
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
     * Posprocess data after retreival from db.
     *
     * @param	string[string]	$row
     * @return	string[string]
     */
    protected function postprocessRowHTML(array $row)
    {
        $row = $this->postprocessRowCommon($row);
        return $row;
    }
    protected function postprocessRowCommon(array $row)
    {
        if (array_key_exists("begin_date", $row)) {
            $row["begin_date"] = $this->transformDate($row["begin_date"]);
        }

        if (array_key_exists("booking_date", $row)) {
            $row["booking_date"] = $this->transformDate($row["booking_date"]);
        }

        if (array_key_exists("end_date", $row)) {
            $row["end_date"] = $this->transformDate($row["end_date"]);
        }

        foreach ($row as $key => &$value) {
            if ($value === null || $value == "") {
                $value = '-';
            }
        }
        $booked = $row['booking_status'] === 'participant';

        if (in_array($row['booking_status'], array("cancelled_after_deadline"))) {
            $row['participation_status'] = "-";
        }
        $row['booking_status'] = $this->txt($row['booking_status']);
        $row['participation_status'] = trim((string) $row['participation_status']);
        if (in_array((string) $row['participation_status'], ['none',''])) {
            $row['participation_status'] = '-';
        }
        if ($row['participation_status'] != "-") {
            $row['participation_status'] = $this->txt($row['participation_status']);
        }
        if ($booked && $row['participation_status'] === '-') {
            $row['participation_status'] = $this->txt('none');
        }
        if ($this->isCourseMembersActive()) {
            if ($row['participation_status'] !== '-') {
                if ($this->properCustomPStatus($row['custom_p_status'])) {
                    $row['participation_status'] = $row['participation_status'] . ' (' . $row['custom_p_status'] . ')';
                }
            }
        }


        if ($this->isEduTrackingActive()) {
            $row['idd_learning_time'] = $this->minutesToTimeString((int) $row['idd_learning_time']) . ' ' . $this->txt('hours');
            $row['idd_learning_time_booked'] = $this->minutesToTimeString((int) $row['idd_learning_time_booked']) . ' ' . $this->txt('hours');
        } else {
            unset($row['idd_learning_time']);
            unset($row['idd_learning_time_booked']);
        }

        $fee = "-";
        if (!is_null($row["fee"]) && $row["fee"] !== "") {
            $fee = number_format((float) $row["fee"], 2, ",", "");
        }
        $row["fee"] = $fee;

        return $row;
    }

    protected function transformDate($value) : string
    {
        if (is_null($value) || $value == "0001-01-01") {
            return  "-";
        }
        return date('d.m.Y', strtotime($value));
    }

    protected function properCustomPStatus($p_status)
    {
        $p_status = trim((string) $p_status);
        return $p_status !== '' && $p_status !== '-';
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }

    protected function getSettings() : Settings
    {
        return $this->settings;
    }
}
