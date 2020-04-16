<?php declare(strict_types = 1);

namespace CaT\Plugins\CancellationFeeReport;

use ILIAS\TMS\Filter as Filter;
use ILIAS\TMS\TableRelations as TableRelations;
use ILIAS\TMS\ReportUtilities\TreeObjectDiscovery;

require_once 'Services/TMS/ReportUtilities/classes/class.ilUDFWrapper.php';

class Report
{
    use \ilUDFWrapper;

    const CRS_TABLE = 'hhd_crs';
    const CRS_TOPICS_TABLE = 'hhd_crs_topics';
    const CRS_CATEGORIES_TABLE = 'hhd_crs_categories';
    const USERCOURSE_TABLE = 'hhd_usrcrs';
    const USR_TABLE = 'usr_data';

    protected $uol;
    protected $o_d;
    protected $tf;
    protected $pf;
    protected $tyf;
    protected $ff;
    protected $interpreter;

    protected $space;
    protected $filter;

    protected $object;
    protected $usr;
    protected $db;

    public function __construct(
        UserOrguLocator $uol,
        TreeObjectDiscovery $o_d,
        \ilCancellationFeeReportPlugin $plugin,
        Filter\PredicateFactory $pf,
        TableRelations\TableFactory $tf,
        Filter\TypeFactory $tyf,
        Filter\FilterFactory $ff,
        TableRelations\SqlQueryInterpreter $interpreter,
        \ilObjUser $usr,
        \ilDBInterface $db
    ) {
        $this->uol = $uol;
        $this->o_d = $o_d;
        $this->plugin = $plugin;
        $this->tf = $tf;
        $this->pf = $pf;
        $this->tyf = $tyf;
        $this->ff = $ff;
        $this->interpreter = $interpreter;
        $this->usr = $usr;
        $this->db = $db;
    }

    public function withObject(\ilObjCancellationFeeReport $object) : Report
    {
        $this->object = $object;
        return $this;
    }

    public function filter() : Filter\Filters\Sequence
    {
        if (!$this->filter) {
            $this->filter = $this->buildFilter();
        }
        return $this->filter;
    }

    protected function buildFilter() : Filter\Filters\Sequence
    {
        $ff = $this->ff;
        $plugin = $this->plugin;
        $tyf = $this->tyf;
        return $ff->sequence(
            $ff->dateperiod(
                $plugin->txt('crs_date'),
                ''
            ),
            $ff->multiselectsearch(
                $plugin->txt('filter_orgus'),
                '',
                $this->orguOptions()
            ),
            $ff->option(
                $plugin->txt('orgus_filter_recursive'),
                ''
            ),
            $ff->sequence(
                $ff->text(
                    $plugin->txt('filter_lastname'),
                    ''
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
                )
            )
        )->map(function ($begin, $end, $orgus, $orgus_recursive, $lastname, $types, $categories, $topics) {
            return [
                                'begin' => $begin
                                ,'end' => $end
                                ,'orgus' => $orgus
                                ,'orgus_recursive' => $orgus_recursive
                                ,'lastname' => $lastname
                                ,'types' => $types
                                ,'categories' => $categories
                                ,'topics' => $topics
                            ];
        }, $tyf->dict(
            [
                        'begin' => $tyf->cls(\DateTime::class)
                        ,'end' => $tyf->cls(\DateTime::class)
                        ,'orgus' => $tyf->lst($tyf->int())
                        ,'orgus_recursive' => $tyf->bool()
                        ,'lastname' => $tyf->string()
                        ,'types' => $tyf->lst($tyf->string())
                        ,'categories' => $tyf->lst($tyf->string())
                        ,'topics' => $tyf->lst($tyf->string())
                ]
        ));
    }

    protected function orguOptions() : array
    {
        return $this->sortedOptions($this->uol->orgusVisibleToUser($this->usr));
    }

    protected function crsTypeOptions() : array
    {
        return $this->sortedOptions($this->getDistinct(self::CRS_TABLE, 'crs_type'));
    }

    protected function crsCategoriesOptions() : array
    {
        return $this->sortedOptions($this->getDistinct(self::CRS_CATEGORIES_TABLE, 'list_data'));
    }

    protected function crsTopicOptions() : array
    {
        return $this->sortedOptions($this->getDistinct(self::CRS_TOPICS_TABLE, 'list_data'));
    }

    protected function sortedOptions(array $options) : array
    {
        uasort(
            $options,
            function ($one, $two) {
                return strcasecmp($one, $two);
            }
        );
        return $options;
    }

    protected function getDistinct($table, $column) : array
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

    public function applyFilterToSpace(array $settings)
    {
        $settings = $this->getFilterSettings($settings);
        $this->maybeApplyCrsDatesFilter($settings['begin'], $settings['end']);
        $this->applyOrgusFilter($settings['orgus'], $settings['orgus_recursive']);
        $this->maybeApplyLastnameFilter($settings['lastname']);
        $this->maybeApplyTypesFilter($settings['types']);
        $this->maybeApplyCategoriesFilter($settings['categories']);
        $this->maybeApplyTopicsFilter($settings['topics']);
    }

    protected function maybeApplyCrsDatesFilter($begin_date, $end_date)
    {
        $space = $this->space();
        $begin_date_s = $begin_date->format('Y-m-d');
        $end_date_s = $end_date->format('Y-m-d');
        $begin_date_f = $space->table('crs')->field('begin_date');
        $end_date_f = $space->table('crs')->field('end_date');
        $space->addFilter(
            $this->pf->_ANY(
                $end_date_f->IS_NULL()->_OR($end_date_f->EQ($this->pf->str('0000-00-00')))->_AND(
                    $this->pf->_ALL(
                        $begin_date_f->GE($this->pf->str($begin_date_s)),
                        $begin_date_f->LE($this->pf->str($end_date_s))
                    )
                ),
                $end_date_f->IS_NULL()->_NOT()->_AND($end_date_f->NEQ($this->pf->str('0000-00-00')))->_AND(
                    $this->pf->_ALL(
                        $end_date_f->GE($this->pf->str($begin_date_s)),
                        $begin_date_f->LE($this->pf->str($end_date_s))
                    )
                )
            )
        );
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
                        ), $this->uol->getVisibleUserIds($this->usr));
        } else {
            $relevant_users = $this->uol->getVisibleUserIds($this->usr);
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

    protected function maybeApplyLastnameFilter($lastname)
    {
        $lastname = trim((string) $lastname);
        if ($lastname !== '') {
            $this->space()->addFilter(
                $this->space()->table('usr')->field('lastname')->LIKE(
                    $this->pf->str($lastname . '%')
                )
            );
        }
    }

    protected function maybeApplyTypesFilter(array $types)
    {
        if (count($types) > 0) {
            $types_f = $this->space()->table('crs')->field('crs_type');
            $this->space()->addFilter($types_f->IN($this->pf->list_string_by_array($types)));
        }
    }

    protected function maybeApplyCategoriesFilter(array $categories)
    {
        if (count($categories) > 0) {
            $this->space->forceRelevant($this->space->table('categories'));
            $categories_f = $this->space->table('categories')->space()->table('categories');
            $this->space->table('categories')->addConstraintSub(
                $categories_f->field('list_data')->IN(
                    $this->pf->list_string_by_array($categories)
                )
            );
        }
    }

    protected function maybeApplyTopicsFilter(array $topics)
    {
        if (count($topics) > 0) {
            $this->space->forceRelevant($this->space->table('topics'));
            $topics_f = $this->space->table('topics')->space()->table('topics');
            $this->space->table('topics')->addConstraintSub(
                $topics_f->field('list_data')->IN(
                    $this->pf->list_string_by_array($topics)
                )
            );
        }
    }

    private function getFilterSettings(array $settings)
    {
        $filter = $this->filter();
        $settings = call_user_func_array(array($filter, "content"), $settings);
        return $settings;
    }

    public function space() : TableRelations\Tables\TableSpace
    {
        if (!$this->space) {
            $this->space = $this->getSpace();
        }
        return $this->space;
    }

    protected function getSpace() : TableRelations\Tables\TableSpace
    {
        $crs = $this->tf->Table(self::CRS_TABLE, 'crs')
            ->addField($this->tf->field('crs_id'))
            ->addField($this->tf->field('title'))
            ->addField($this->tf->field('crs_type'))
            ->addField($this->tf->field('accomodation'))
            ->addField($this->tf->field('venue'))
            ->addField($this->tf->field('venue_freetext'))
            ->addField($this->tf->field('provider'))
            ->addField($this->tf->field('provider_freetext'))
            ->addField($this->tf->field('begin_date'))
            ->addField($this->tf->field('end_date'))
            ->addField($this->tf->field('max_cancellation_fee'))
            ;
        $crs = $crs->addConstraint(
            $crs->field('max_cancellation_fee')->IS_NULL()->_NOT()->_AND(
                $crs->field('max_cancellation_fee')->GT($this->pf->float(0.0))
            )
        );

        $cancellations = $this->tf->Table(self::USERCOURSE_TABLE, 'usrcrs')
                        ->addField($this->tf->field('usr_id'))
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('booking_status'))
                        ->addField($this->tf->field('participation_status'))
                        ->addField($this->tf->field('cancel_booking_date'))
                        ->addField($this->tf->field('cancellation_fee'))
                        ;
        $cancellations = $cancellations
                        ->addConstraint(
                            $cancellations->field('booking_status')->IN(
                                $this->pf->list_string_by_array(
                                    [
                                        "cancelled",
                                        "cancelled_after_deadline"
                                    ]
                                )
                            )->_OR(
                                $cancellations->field('booking_status')->IN(
                                    $this->pf->list_string_by_array(
                                        [
                                            "participant"
                                        ]
                                    )
                                )->_AND(
                                    $cancellations->field('participation_status')->IN(
                                        $this->pf->list_string_by_array(
                                            [
                                                "absent"
                                            ]
                                        )
                                    )
                                )
                            )
                        )
                        ->addConstraint(
                            $cancellations->field('cancellation_fee')->IS_NULL()->_NOT()
                                ->_AND(
                                    $cancellations->field('cancellation_fee')->GT($this->pf->float(0.0))
                                )
                        );

        $categories_src = $this->tf->Table(self::CRS_CATEGORIES_TABLE, 'categories')
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('list_data'));
        $categories = $this->tf->DerivedTable(
            $this->tf->TableSpace()
                ->addTablePrimary($categories_src)
                ->setRootTable($categories_src)
                ->request($categories_src->field('crs_id'))
                ->groupBy($categories_src->field('crs_id')),
            'categories'
        );

        $topics_src = $this->tf->Table(self::CRS_TOPICS_TABLE, 'topics')
                        ->addField($this->tf->field('crs_id'))
                        ->addField($this->tf->field('list_data'));
        $topics = $this->tf->DerivedTable(
            $this->tf->TableSpace()
                ->addTablePrimary($topics_src)
                ->setRootTable($topics_src)
                ->request($topics_src->field('crs_id'))
                ->groupBy($topics_src->field('crs_id')),
            'topics'
        );


        $usr_data = $this->tf->Table(self::USR_TABLE, 'usr')
                        ->addField($this->tf->field('usr_id'))
                        ->addField($this->tf->field('firstname'))
                        ->addField($this->tf->field('lastname'));

        $usr_data_presentation = $this->configuredUserDataTable($this->tf, 'usr_data_presentation');

        $space = $this->tf->TableSpace()
            ->addTablePrimary($cancellations)
            ->addTableSecondary($crs)
            ->addTableSecondary($usr_data)
            ->addTableSecondary($categories)
            ->addTableSecondary($topics)
            ->addTableSecondary($usr_data_presentation)
            ->setRootTable($cancellations)
            ->addDependency(
                $this->tf->TableJoin(
                    $cancellations,
                    $crs,
                    $cancellations->field('crs_id')->EQ($crs->field('crs_id'))
                )
            )
            ->addDependency(
                $this->tf->TableJoin(
                    $cancellations,
                    $usr_data,
                    $cancellations->field('usr_id')->EQ($usr_data->field('usr_id'))
                )
            )
            ->addDependency(
                $this->tf->TableJoin(
                    $cancellations,
                    $usr_data_presentation,
                    $cancellations->field('usr_id')->EQ($usr_data_presentation->field('usr_id'))
                )
            )
            ->addDependency(
                $this->tf->TableJoin(
                    $cancellations,
                    $categories,
                    $cancellations->field('crs_id')->EQ($categories->field('crs_id'))
                )
            )
            ->addDependency(
                $this->tf->TableJoin(
                    $cancellations,
                    $topics,
                    $cancellations->field('crs_id')->EQ($topics->field('crs_id'))
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

    public function fetchData() : array
    {
        $res = $this->db->query($this->interpreter->getSql($this->space()->query()));
        $return = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $return[] = $this->postprocessRowHTML($row);
        }
        return $return;
    }

    protected function postprocessRowHTML(array $row) : array
    {
        if (array_key_exists('begin_date', $row)) {
            if ($row['begin_date'] === $row['end_date']) {
                $row['crs_period'] = \DateTime::createFromFormat('Y-m-d', $row['begin_date'])->format('d.m.Y');
            } else {
                $row['crs_period'] =
                    \DateTime::createFromFormat('Y-m-d', $row['begin_date'])->format('d.m.Y')
                    . ' - ' . \DateTime::createFromFormat('Y-m-d', $row['end_date'])->format('d.m.Y');
            }
        }

        if (array_key_exists('storno_date', $row) && !is_null($row['storno_date'])) {
            $row['storno_date'] = \DateTime::createFromFormat('Y-m-d', $row['storno_date'])->format('d.m.Y');
        }

        if (array_key_exists('cancellation_fee', $row)) {
            $fee = (float) $row['cancellation_fee'];
            $row['cancellation_fee'] = number_format($fee, 2, ",", ".");
        }

        if (array_key_exists('max_cancellation_fee', $row)) {
            $fee = (float) $row['max_cancellation_fee'];
            $row['max_cancellation_fee'] = number_format($fee, 2, ",", ".");
        }

        foreach ($row as $key => $value) {
            if (is_null($value) || $value == "") {
                $row[$key] = "-";
            }
        }

        return $row;
    }

    public function configureTable(\SelectableReportTableGUI $table) : \SelectableReportTableGUI
    {
        $space = $this->space();
        $plugin = $this->plugin;
        $table->setRowTemplate('tpl.report_row.html', $plugin->getDirectory());
        $table = $this->addUserDataToTable(
            $space,
            $table,
            'usr_data_presentation'
        );
        $table = $this->addUDFColumnsToTable($space, $table);
        $table = $table
            ->defineFieldColumn(
                $plugin->txt('crs_title'),
                'crs_title',
                ['crs_title' => $space->table('crs')->field('title')],
                true,
                true
            )
            ->defineFieldColumn(
                $plugin->txt('crs_type'),
                'crs_type',
                ['crs_type' => $space->table('crs')->field('crs_type')],
                true,
                true
            )
            ->defineFieldColumn(
                $plugin->txt('crs_period'),
                'crs_period',
                ['begin_date' => $space->table('crs')->field('begin_date')
                ,'end_date' => $this->tf->ifThenElse(
                    '',
                    $space->table('crs')->field('end_date')->IS_NULL()
                            ->_OR($space->table('crs')->field('end_date')->EQ($this->pf->str('0000-00-00'))),
                    $space->table('crs')->field('begin_date'),
                    $space->table('crs')->field('end_date')
                )
                ],
                true,
                true
            )
            ->defineFieldColumn(
                $plugin->txt('venue'),
                'venue',
                ['venue' => $space->table('crs')->field('venue')],
                true,
                true
            )
            ->defineFieldColumn(
                $plugin->txt('accomodation'),
                'accomodation',
                ['accomodation' => $space->table('crs')->field('accomodation')],
                true,
                true
            )
            ->defineFieldColumn(
                $plugin->txt('provider'),
                'provider',
                ['provider' => $space->table('crs')->field('provider')],
                true,
                true
            )
            ->defineFieldColumn(
                $plugin->txt('storno_date'),
                'storno_date',
                ['storno_date' => $space->table('usrcrs')->field('cancel_booking_date')],
                true,
                true
            )
            ->defineFieldColumn(
                $plugin->txt('max_cancellation_fee'),
                'max_cancellation_fee',
                ['max_cancellation_fee' => $space->table('crs')->field('max_cancellation_fee')],
                true,
                true
            )
            ->defineFieldColumn(
                $plugin->txt('cancellation_fee'),
                'cancellation_fee',
                ['cancellation_fee' => $space->table('usrcrs')->field('cancellation_fee')],
                true,
                true
            )
            ;
        $table->setDefaultSelectedColumns(
            [$this->usrDataFieldId('firstname'),$this->usrDataFieldId('lastname'),'crs_title','crs_type','crs_period','storno_date','cancellation_fee']
        );
        $table->setDefaultOrderColumn('crs_period', \SelectableReportTableGUI::ORDER_ASC);
        $table->prepareTableAndSetRelevantFields($space);
        $this->space = $this->possiblyConstrainCourses($space);
        return $table;
    }

    protected function possiblyConstrainCourses(
        TableRelations\Tables\TableSpace $space
    ) : TableRelations\Tables\TableSpace {
        if ($this->object->getSettings()->isGlobal()) {
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

    protected function relevantTrainingIds() : array
    {
        $parent = $this->o_d->getParentOfObjectOfType($this->object, 'cat');
        if ($parent === null) {
            $parent = $this->o_d->getParentOfObjectOfType($this->object, 'root');
        }
        return $this->o_d->getAllChildrenIdsByTypeOfObject($parent, 'crs');
    }
}
