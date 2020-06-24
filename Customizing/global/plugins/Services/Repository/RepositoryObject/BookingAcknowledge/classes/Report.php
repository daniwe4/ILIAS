<?php

declare(strict_types=1);

namespace CaT\Plugins\BookingAcknowledge;

use ILIAS\TMS\Filter;
use ILIAS\TMS\TableRelations;
use ILIAS\TMS\ReportUtilities\TreeObjectDiscovery;
use CaT\Plugins\BookingAcknowledge\Utils\RequestDigester;
use CaT\Plugins\BookingAcknowledge\Utils\AccessHelper;

require_once 'Services/TMS/ReportUtilities/classes/class.ilUDFWrapper.php';

class Report
{
    use \ilUDFWrapper;

    const HEAD_COURSE_TABLE = "hhd_crs";
    const HEAD_COURSE_TOPICS_TABLE = "hhd_crs_topics";
    const HEAD_COURSE_CATEGORIES_TABLE = "hhd_crs_categories";
    const HEAD_USERCOURSE_TABLE = "hhd_usrcrs";
    const HEAD_COURSE_TUT_TABLE = "hhd_crs_tut";
    const USR_DATA_TABLE = 'usr_data';
    const HEAD_USERCOURSE_NIGHTS_TABLE = "hhd_usrcrs_nights";
    const HEAD_SESSIONCOURSE_TABLE = 'hhd_sesscrs';
    const ACKNOWLEDGEMENT_TABLE = 'xack_requests';

    const AGGREGATE_ID_CRS_TYPE = 'crs_type';
    const AGGREGATE_ID_EDU_PROGRAMME = 'edu_programme';
    const AGGREGATE_ID_CRS_TOPICS = 'crs_topics';
    const AGGREGATE_ID_CATEGORIES = 'categories';

    const TYPE_TO_ACKNOWLEDGE = 'report_type_to_acknowledge';
    const TYPE_ACKNOWLEDGED = 'report_type_acknowledged';

    const OBJECT_REFERENCE_TABLE = 'object_reference';


    protected $edu_tracking_active = null;
    protected $accomodation_active = null;
    protected $course_members_active = null;

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

    /**
     * @var	AccessHelper
     */
    protected $access_helper;

    public function __construct(
        \ilDBInterface $db,
        \Closure $txt,
        ActionLinksHelper $action_links,
        TreeObjectDiscovery $o_d,
        UserOrguLocator $uol,
        \ilObjUser $usr,
        AccessHelper $access_helper
    ) {
        $this->db = $db;
        $this->txt_closure = $txt;
        $this->action_links = $action_links;
        $this->o_d = $o_d;
        $this->uol = $uol;
        $this->usr = $usr;
        $this->access_helper = $access_helper;

        $this->gf = new TableRelations\GraphFactory();
        $this->pf = new Filter\PredicateFactory();
        $this->tf = new TableRelations\TableFactory($this->pf, $this->gf);
        $this->tyf = new Filter\TypeFactory();
        $this->ff = new Filter\FilterFactory($this->pf, $this->tyf);
    }

    protected $type;

    public function withType(string $type) : Report
    {
        $other = clone $this;
        $other->type = $type;
        $other->space = null;
        return $other;
    }

    public function txt(string $var)
    {
        $closure = $this->txt_closure;
        $txt = function ($code) use ($closure) {
            return $closure($code);
        };
        return $txt($var);
    }

    public function fetchData() : array
    {
        $res = $this->db->query($this->interpreter()->getSql($this->space()->query()));
        $return = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $return[] = $this->postprocessRowHTML($row);
        }
        return $return;
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
                $this->txt('crs_date'),
                ''
            ),
            $ff->text($this->txt('filter_lastname'), ''),
            $ff->multiselectsearch(
                $this->txt('orgus_filter'),
                '',
                $this->orgusOptions()
            ),
            $ff->option($this->txt('orgus_filter_recursive'), ''),
            $ff->text($this->txt('filter_crs_title'), '')
        )->map(
            function (
                $period_start,
                $period_end,
                $lastname,
                $orgus,
                $recursive,
                $crs_title
            ) {
                return ['period_start' => $period_start,
                            'period_end' => $period_end,
                            'lastname' => $lastname,
                            'orgus' => $orgus,
                            'orgus_recursive' => $recursive,
                            'crs_title' => $crs_title
                            ];
            },
            $tyf->dict(
                [	'period_start' => $tyf->cls("\\DateTime"),
                    'period_end' => $tyf->cls("\\DateTime"),
                    'lastname' => $tyf->string(),
                    'orgus' => $tyf->lst($tyf->int()),
                    'orgus_recursive' => $tyf->bool(),
                    'crs_title' => $tyf->string()
                ]
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
        $this->possiblyAddLastnameFilter($settings['lastname']);
        $this->possiblyAddCrsTitleFilter($settings['crs_title']);
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
    private function possiblyAddLastnameFilter($lastname)
    {
        $lastname = trim((string) $lastname);
        if ($lastname !== '') {
            $ln_field = $this->space()->table('usr')->field('lastname');
            $this->space()->addFilter($ln_field->LIKE($this->pf->str($lastname . '%')));
        }
    }

    /**
     * Add a crs-title filter predicate to space, if crs-title does not
     * consist of whitespaces.
     *
     * @param	string	$lastname
     * @return	void
     */
    private function possiblyAddCrsTitleFilter($crs_title)
    {
        $crs_title = trim((string) $crs_title);
        if ($crs_title !== '') {
            $ln_field = $this->space()->table('crs')->field('title');
            $this->space()->addFilter($ln_field->LIKE($this->pf->str($crs_title . '%')));
        }
    }

    /**
     * Get the table space the report uses.
     */
    public function space() : \ILIAS\TMS\TableRelations\Tables\TableSpace
    {
        switch ($this->type) {
            case self::TYPE_TO_ACKNOWLEDGE:
                if ($this->space === null) {
                    $this->space = $this->toAcknowledgeSpace();
                }
                break;
            case self::TYPE_ACKNOWLEDGED:
                if ($this->space === null) {
                    $this->space = $this->acknowledgedSpace();
                }
                break;
        }
        return $this->space;
    }

    /**
     * Get the table space corresponding to to acknowledge report.
     */
    protected function baseSpace() : \ILIAS\TMS\TableRelations\Tables\TableSpace
    {
        $course_members_active = $this->isCourseMembersActive();

        $crs_data = $this->tf->Table(self::HEAD_COURSE_TABLE, 'crs')
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('title'))
                        ->addField($this->tf->field('crs_type'))
                        ->addField($this->tf->field('venue'))
                        ->addField($this->tf->field('provider'))
                        ->addField($this->tf->field('begin_date'))
                        ->addField($this->tf->field('end_date'))
                        ->addField($this->tf->field('to_be_acknowledged'));


        $participations = $this->tf->Table(self::HEAD_USERCOURSE_TABLE, 'usrcrs')
                        ->addField($this->tf->field('usr_id'))
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('booking_status'))
                        ->addField($this->tf->field('participation_status'))
                        ->addField($this->tf->field('booking_date'))
                        ->addField($this->tf->field('ps_acquired_date'))
                        ->addField($this->tf->field('prior_night'));

        if ($course_members_active) {
            $participations = $participations->addField($this->tf->field('custom_p_status'));
        }
        $participations = $participations
                        ->addConstraint($participations->field('booking_status')->IN(
                            $this->pf->list_string_by_array(
                                ['participant'
                                ,'waiting']
                            )
                        ));

        $usr_data = $this->tf->Table(self::USR_DATA_TABLE, 'usr')
                    ->addField($this->tf->field('usr_id'));
        //		->addField($this->tf->field('firstname'))
        //		->addField($this->tf->field('lastname'));

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


        $sessions_base = $this->tf->Table(self::HEAD_SESSIONCOURSE_TABLE, 'base')
                    ->addField($this->tf->field('crs_id'))
                    ->addField($this->tf->field('session_id'))
                    ->addField($this->tf->field('begin_date'))
                    ->addField($this->tf->field('start_time'))
                    ->addField($this->tf->field('end_date'))
                    ->addField($this->tf->field('end_time'))
                    ->addField($this->tf->field('fullday'))
                    ->addField($this->tf->field('removed'));
        $sessions_ref = $this->tf->Table(self::HEAD_SESSIONCOURSE_TABLE, 'ref')
                    ->addField($this->tf->field('crs_id'))
                    ->addField($this->tf->field('session_id'))
                    ->addField($this->tf->field('begin_date'))
                    ->addField($this->tf->field('end_date'))
                    ->addField($this->tf->field('removed'));
        $sessions_ref->addConstraint($sessions_ref->field('removed')->EQ($this->pf->int(0)));

        $session_space_start = $this->tf->TableSpace()
                        ->addTablePrimary($sessions_base)
                        ->addTablePrimary($sessions_ref)
                        ->setRootTable($sessions_base)
                        ->addFilter($sessions_base->field('removed')->EQ($this->pf->int(0)))
                        ->addDependency(
                            $this->tf->TableLeftJoin(
                                $sessions_base,
                                $sessions_ref,
                                $this->pf->_ALL(
                                    $sessions_base->field('begin_date')->GT($sessions_ref->field('begin_date')),
                                    $sessions_base->field('crs_id')->EQ($sessions_ref->field('crs_id'))
                                )
                            )
                        )
                        ->addFilter($sessions_ref->field('session_id')->IS_NULL())
                        ->request($sessions_base->field('crs_id'))
                        ->request($sessions_base->field('fullday'))
                        ->request($sessions_base->field('start_time'))
                        ->request($sessions_base->field('end_time'));
        $start_time = $this->tf->DerivedTable($session_space_start, 'start_time');

        $session_space_end = $this->tf->TableSpace()
            ->addTablePrimary($sessions_base)
            ->addTablePrimary($sessions_ref)
            ->setRootTable($sessions_base)
            ->addFilter($sessions_base->field('removed')->EQ($this->pf->int(0)))
            ->addDependency(
                $this->tf->TableLeftJoin(
                    $sessions_base,
                    $sessions_ref,
                    $this->pf->_ALL(
                        $sessions_base->field('end_date')->LT($sessions_ref->field('end_date')),
                        $sessions_base->field('crs_id')->EQ($sessions_ref->field('crs_id'))
                    )
                )
            )
            ->addFilter($sessions_ref->field('session_id')->IS_NULL())
            ->request($sessions_base->field('crs_id'))
            ->request($sessions_base->field('fullday'))
            ->request($sessions_base->field('start_time'))
            ->request($sessions_base->field('end_time'));
        $end_time = $this->tf->DerivedTable($session_space_end, 'end_time');

        $acknowledgements = $this->tf->Table(self::ACKNOWLEDGEMENT_TABLE, 'acknowledgements')
                        ->addField($this->tf->field('usr_id'))
                        ->addField($this->tf->field('acting_usr_id'))
                        ->addField($this->tf->field('crs_ref_id'))
                        ->addField($this->tf->field('dat'))
                        ->addField($this->tf->field('id'))
                        ->addField($this->tf->field('state'));

        $course_ref = $this->tf->Table(self::OBJECT_REFERENCE_TABLE, 'object_reference')
                        ->addField($this->tf->field('obj_id'))
                        ->addField($this->tf->field('ref_id'));

        $actor = $this->tf->Table(self::USR_DATA_TABLE, 'actor')
                        ->addField($this->tf->field('usr_id'))
                        ->addField($this->tf->field('firstname'))
                        ->addField($this->tf->field('lastname'));

        $space = $this->tf->TableSpace()
                        ->addTablePrimary($participations)
                        ->addTablePrimary($usr_data)
                        ->addTablePrimary($crs_data)
                        ->addTablePrimary($start_time)
                        ->addTablePrimary($end_time)
                        ->addTablePrimary($course_ref)
                        ->addTablePrimary($acknowledgements)
                        ->addTableSecondary($actor)
                        ->setRootTable($participations)
                        ->addDependency(
                            $this->tf->TableJoin(
                                $participations,
                                $crs_data,
                                $participations->field('crs_id')->EQ($crs_data->field('crs_id'))
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
                            $this->tf->TableLeftJoin(
                                $participations,
                                $start_time,
                                $participations->field('crs_id')->EQ($start_time->field('crs_id'))
                            )
                        )
                        ->addDependency(
                            $this->tf->TableLeftJoin(
                                $participations,
                                $end_time,
                                $participations->field('crs_id')->EQ($end_time->field('crs_id'))
                            )
                        )
                        ->addDependency(
                            $this->tf->TableJoin(
                                $participations,
                                $course_ref,
                                $participations->field('crs_id')->EQ($course_ref->field('obj_id'))
                            )
                        )
                        ->addDependency(
                            $this->tf->TableLeftJoin(
                                $course_ref,
                                $acknowledgements,
                                $course_ref->field('ref_id')->EQ($acknowledgements->field('crs_ref_id'))
                            )
                        )
                        ->addDependency(
                            $this->tf->TableLeftJoin(
                                $participations,
                                $acknowledgements,
                                $participations->field('usr_id')->EQ($acknowledgements->field('usr_id'))
                            )
                        )
                        ->addDependency(
                            $this->tf->TableLeftJoin(
                                $acknowledgements,
                                $actor,
                                $acknowledgements->field('acting_usr_id')->EQ($actor->field('usr_id'))
                            )
                        );

        $space = $this->appendUDFsToSpace(
            $this->tf,
            $this->pf,
            $space,
            $usr_data,
            'usr_id'
        );

        return $space;
    }

    protected function toAcknowledgeSpace() : \ILIAS\TMS\TableRelations\Tables\TableSpace
    {
        $space = $this->baseSpace();
        $space->addFilter($space->table('acknowledgements')->field('id')->IS_NULL());
        $space->addFilter($space->table('crs')->field('to_be_acknowledged')->EQ($this->pf->int(1)));
        return $space;
    }


    protected function acknowledgedSpace() : \ILIAS\TMS\TableRelations\Tables\TableSpace
    {
        $space = $this->baseSpace();
        $space->addFilter($space->table('acknowledgements')->field('id')->IS_NULL()->_NOT());
        $space->addFilter($space->table('crs')->field('to_be_acknowledged')->EQ($this->pf->int(1)));
        return $space;
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
    public function configureTable(\SelectableReportTableGUI $table) : \SelectableReportTableGUI
    {
        switch ($this->type) {
            case self::TYPE_TO_ACKNOWLEDGE:
                if ($this->access_helper->mayAcknowledge()) {
                    $table->defineFieldColumn(
                        '',
                        'checkbox',
                        [],
                        false,
                        false,
                        true
                    );

                    $table->setSelectAllCheckbox("f_usrcrs");
                    $table->addMultiItemSelectionButton(
                        "multi_action",
                        array(
                            RequestDigester::CMD_ACKNOWLEDGE_CONFIRM => $this->txt(RequestDigester::CMD_ACKNOWLEDGE_CONFIRM),
                            RequestDigester::CMD_DECLINE_CONFIRM => $this->txt(RequestDigester::CMD_DECLINE_CONFIRM)
                        ),
                        Utils\RequestDigester::CMD_MULTI_ACTION,
                        $this->txt("multi_action")
                    );
                }

                $table = $this->configureToAcknowledgeTable($table);
                break;

            case self::TYPE_ACKNOWLEDGED:
                $table = $this->configureAcknowledgedTable($table);
                break;
        }

        $this->space = $table->prepareTableAndSetRelevantFields($this->space);
        return $table;
    }


    protected function configureToAcknowledgeTable(\SelectableReportTableGUI $table) : \SelectableReportTableGUI
    {
        $space = $this->space;
        $table = $this->configureBaseTable($table);
        $table->defineFieldColumn(
            $this->txt('actions'),
            'actions',
            ['ref_id' => $space->table('object_reference')->field('ref_id')
                ,'a_usr_id' => $space->table('usr')->field('usr_id')],
            false,
            false,
            true
            );

        return $table;
    }

    protected function configureAcknowledgedTable(\SelectableReportTableGUI $table) : \SelectableReportTableGUI
    {
        $space = $this->space;
        $table = $this->configureBaseTable($table);
        $table->defineFieldColumn(
            $this->txt('br_status'),
            'br_status',
            ['br_status' => $space->table('acknowledgements')->field('state')],
            false,
            true,
            true
            )
            ->defineFieldColumn(
                $this->txt('ack_by'),
                'ack_by',
                ['ack_fn' => $space->table('actor')->field('firstname')
                ,'ack_ln' => $space->table('actor')->field('lastname')
                ,'ack_dat' => $space->table('acknowledgements')->field('dat')],
                false,
                false,
                true
            )
            ->defineFieldColumn(
                $this->txt('actions'),
                'actions',
                ['ref_id' => $space->table('object_reference')->field('ref_id')
                ,'a_usr_id' => $space->table('usr')->field('usr_id')],
                false,
                false,
                true
            );
        return $table;
    }

    protected function configureBaseTable(\SelectableReportTableGUI $table) : \SelectableReportTableGUI
    {
        $space = $this->space();

        $table
            ->defineFieldColumn(
                $this->txt('crs_title'),
                'crs_title',
                [ 'crs_title' => $space->table('crs')->field('title')]
            )
            ->defineFieldColumn(
                $this->txt('lastname'),
                'lastname',
                ['lastname' => $space->table('usr')->field('lastname')]
            )
            ->defineFieldColumn(
                $this->txt('firstname'),
                'firstname',
                ['firstname' => $space->table('usr')->field('firstname')]
            );


        /**
         * skip firstname and lastname because they have a fixed position in table
         * username isn't visible at all
         */
        foreach ($this->getAllCourseVisibleStandardUserFields() as $field) {
            if (in_array($field, ["firstname", "lastname", "username", "org_units"])) {
                continue;
            }

            $table = $table->defineFieldColumn(
                $this->txt($field),
                "UDF_" . $field,
                ["UDF_" . $field => $space->table('usr')->field($field)],
                true
            );
        }
        $table = $this->addUDFColumnsToTable($this->space, $table);

        $table = $table
            ->defineFieldColumn(
                $this->txt('orgu'),
                'orgu',
                ['o_usr_id' => $space->table('usr')->field('usr_id')]
            )
            ->defineFieldColumn(
                $this->txt('crs_type'),
                'crs_type',
                ['crs_type' => $space->table('crs')->field('crs_type')],
                true
            )
            ->defineFieldColumn(
                $this->txt('crs_date'),
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
                $this->txt('venue'),
                'venue',
                ['venue' => $space->table('crs')->field('venue')],
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
            );
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

        $table = $table
            ->defineFieldColumn(
                $this->txt('start_time'),
                'start_time',
                ['start_time' => $space->table('start_time')->field('start_time')
                ,'fullday' => $space->table('start_time')->field('fullday')],
                false,
                true,
                false,
                true,
                true
            )
            ->defineFieldColumn(
                $this->txt('end_time'),
                'end_time',
                ['end_time' => $space->table('end_time')->field('end_time')
                ,'fullday' => $space->table('end_time')->field('fullday')],
                false,
                true,
                false,
                true,
                true
            )
            ->defineFieldColumn(
                $this->txt('prior_night'),
                'prior_night',
                ['prior_night' => $space->table('usrcrs')->field('prior_night')]
            );

        $table->setDefaultOrderColumn('lastname', \SelectableReportTableGUI::ORDER_ASC);
        return $table;
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
    protected function minutesToTimeString(int $minutes)
    {
        $hours = (string) floor($minutes / 60);
        $minutes = (string) ($minutes - $hours * 60);

        return str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT);
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
    protected function visibleUsers()
    {
        if (!$this->visible_users) {
            $this->visible_users = $this->uol->getVisibleUserIds($this->usr);
        }
        return $this->visible_users;
    }


    protected static $default_p_status = ['none', 'successful', 'absent'];

    protected function participationStatusOptions()
    {
        $plugin = $this->plugin;
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
     * @return string[string]
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
        if (array_key_exists('ref_id', $row) && $row['ref_id'] !== null) {
            $row['actions'] = $this->actionMenuFor(
                (int) $row['a_usr_id'],
                (int) $row['ref_id']
            );
            $row['checkbox'] = implode('_', [ //TODO: this is digester::prepare
                $row['a_usr_id'],
                $row['ref_id']
            ]);
        }

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
            $begin = date('d.m.Y', strtotime($begin_date));
            $end = date('d.m.Y', strtotime($end_date));
            if ($begin === $end) {
                $row['crs_date'] = $begin;
            } else {
                $row['crs_date'] = $begin . '-' . $end;
            }
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


        if (array_key_exists('fulldate', $row) && (string) $row['fulldate'] === '1') {
            $row['start_time'] = self::FULLDAY_ENDTIME . ' ' . $this->txt('hrs');
        } elseif (array_key_exists('start_time', $row) && is_numeric($row['start_time'])) {
            $row['start_time'] = date('H:i', (int) $row['start_time']) . ' ' . $this->txt('hrs');
        }

        if (array_key_exists('fulldate', $row) && (string) $row['fulldate'] === '1') {
            $row['end_time'] = self::FULLDAY_ENDTIME . ' ' . $this->txt('hrs');
        } elseif (array_key_exists('end_time', $row) && is_numeric($row['end_time'])) {
            $row['end_time'] = date('H:i', (int) $row['end_time']) . ' ' . $this->txt('hrs');
        }

        if (array_key_exists('br_status', $row)) {
            switch ((int) $row['br_status']) {
                case Acknowledgments\Acknowledgment::APPROVED:
                    $row['br_status'] = $this->txt('approved');
                    break;
                case Acknowledgments\Acknowledgment::DECLINED:
                    $row['br_status'] = $this->txt('declined');
                    break;
            }
        }
        if (array_key_exists('ack_ln', $row)) {
            $row['ack_by'] = $row['ack_fn'] . ' ' . $row['ack_ln'] . '/' . $row['ack_dat'];
        }
        if (array_key_exists('o_usr_id', $row)) {
            $row['orgu'] = $this->uol->getFormattedOrgusOfUser((int) $row['o_usr_id']);
        }
        if (array_key_exists('prior_night', $row)) {
            if ((string) $row['prior_night'] === '0') {
                $row['prior_night'] = $this->txt('no');
            } elseif ((string) $row['prior_night'] === '1') {
                $row['prior_night'] = $this->txt('yes');
            } else {
                $row['prior_night'] = '-';
            }
        }
        return $row;
    }


    const FULLDAY_STARTTIME = '09:00';
    const FULLDAY_ENDTIME = '16:00';

    protected function properCustomPStatus($p_status)
    {
        $p_status = trim((string) $p_status);
        return $p_status !== '' && $p_status !== '-';
    }

    protected function actionMenuFor(int $usr_id, int $crs_ref_id) : string
    {
        $l = new \ilAdvancedSelectionListGUI();
        $l->setListTitle($this->txt("please_choose"));
        $l->setId("selection_list_" . $crs_ref_id);
        $l->setItemLinkClass("xsmall");
        $l->setLinksMode("il_ContainerItemCommand2");

        $action_links = $this->action_links
            ->withRefId($crs_ref_id)
            ->withUsrId($usr_id);

        $to_training = $action_links->getLinkToTraining();
        $title = $this->txt('to_course');
        $l->addItem(
            $title,
            $title,
            $to_training,
            '', //image
            '', //image_alt
            '_blank' //frame
        );

        $entry_actions = $action_links->getEntryActions();
        if ($this->type === self::TYPE_ACKNOWLEDGED) {
            $entry_actions = ['mail' => $entry_actions['mail']];
        }

        foreach ($entry_actions as $title => $link) {
            $l->addItem(
                $this->txt($title),
                $title,
                $link
            );
        }

        return $l->getHTML();
    }

    /**
     * Get all standard user fields visible in Courses
     *
     * @return string[]
     */
    protected function getAllCourseVisibleStandardUserFields() : array
    {
        include_once './Services/PrivacySecurity/classes/class.ilExportFieldsInfo.php';
        $ef = \ilExportFieldsInfo::_getInstanceByType("crs");

        return $ef->getExportableFields();
    }
}
