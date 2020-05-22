<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainingStatisticsByOrgUnits\Report\SplittedView;

use ILIAS\TMS\Filter;
use ILIAS\TMS\TableRelations;
use ILIAS\TMS\ReportUtilities\TreeObjectDiscovery;
use CaT\Plugins\TrainingStatisticsByOrgUnits\Settings\Settings;

class Report
{
    const HEAD_COURSE_TABLE = "hhd_crs";
    const HEAD_COURSE_TOPICS_TABLE = "hhd_crs_topics";
    const HEAD_COURSE_CATEGORIES_TABLE = "hhd_crs_categories";
    const HEAD_USERCOURSE_TABLE = "hhd_usrcrs";
    const OBJECT_DATA = "object_data";
    const OBJECT_ORGU = "object_data";
    const ORGU_UA = "il_orgu_ua";
    const USR_DATA = "usr_data";
    const OBJECT_REFERENCE = "object_reference";
    const TREE = "tree";

    /**
     * @var Filter\Filters\Sequence | null
     */
    protected $filter = null;

    /**
     * @var TableRelations\Tables\TableSpace | null
     */
    protected $space = null;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var TableRelations\TableFactory
     */
    protected $tf;

    /**
     * @var Filter\PredicateFactory
     */
    protected $pf;

    /**
     * @var Filter\TypeFactory
     */
    protected $tyf;

    /**
     * @var Filter\FilterFactory
     */
    protected $ff;

    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var TreeObjectDiscovery
     */
    protected $o_d;

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var \ilObjTrainingStatisticsByOrgUnits
     */
    protected $object;

    /**
     * @var string
     */
    protected $plugin_dir;

    /**
     * @var int | null
     */
    protected $parent_orgu_ref_id;

    /**
     * @var int | null
     */
    protected $parent_orgu_obj_id;

    /**
     * @var int[] | null
     */
    protected $relevant_training_ids;

    /**
     * @var string | null
     */
    protected $top_depth;

    public function __construct(
        \ilObjTrainingStatisticsByOrgUnits $object,
        \ilDBInterface $db,
        \Closure $txt,
        Filter\PredicateFactory $pf,
        TableRelations\TableFactory $tf,
        Filter\TypeFactory $tyf,
        Filter\FilterFactory $ff,
        TreeObjectDiscovery $o_d,
        Settings $settings,
        string $plugin_dir,
        int $parent_orgu_ref_id = null
    ) {
        $this->txt = $txt;
        $this->pf = $pf;
        $this->tf = $tf;
        $this->tyf = $tyf;
        $this->ff = $ff;

        $this->db = $db;

        $this->o_d = $o_d;
        $this->settings = $settings;
        $this->object = $object;
        $this->plugin_dir = $plugin_dir;
        $this->parent_orgu_ref_id = $parent_orgu_ref_id;

        if (!is_null($parent_orgu_ref_id)) {
            $this->parent_orgu_obj_id = \ilObject::_lookupObjId($parent_orgu_ref_id);
        }
    }

    public function fetchDataForOrgu(bool $html = true) : array
    {
        return $this->fetchDataBySpace($this->space(), $html);
    }

    protected function fetchDataBySpace(TableRelations\Tables\TableSpace $space, bool $html = true) : array
    {
        $sql = $this->interpreter()->getSql($space->query());
        $res = $this->db->query($sql);
        $return = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $return[] = $html ? $this->postprocessRowHTML($row) : $this->postprocessRowCommon($row);
        }

        $return = $this->sortOrgunits($return);

        return $return;
    }

    protected function postprocessRowHTML(array $row) : array
    {
        if (is_null($this->top_depth)) {
            $this->top_depth = $row["depth"];
        }

        $depth = $row["depth"] - $this->top_depth;
        $margin = "";
        for ($i = 0; $i < $depth; $i++) {
            $margin .= "&nbsp;&nbsp;";
        }

        $row["orgu_title"] = $margin . $row["orgu_title"];
        return $this->postprocessRowCommon($row);
    }

    protected function postprocessRowCommon(array $row) : array
    {
        if ($row["obj_id"] == \ilObjOrgUnit::getRootOrgId()) {
            $row["orgu_title"] = $this->txt("orgu_base");
        }
        foreach ($row as $key => $value) {
            if (is_null($value)) {
                $row[$key] = "-";
            }
        }
        return $row;
    }

    protected function sortOrgunits(array $result) : array
    {
        $rows = [];
        foreach ($result as $row) {
            $rows[$row["depth"]][] = $row;
        }

        $depths = array_keys($rows);
        sort($depths);
        foreach ($depths as $depth) {
            usort(
                $rows[$depth],
                function ($a_row, $b_row) {
                    $res = strcasecmp($a_row["orgu_title"], $b_row["orgu_title"]);
                    if ($res == 0) {
                        $a_ref_id = (int) $a_row["child"];
                        $b_ref_id = (int) $a_row["child"];

                        if ($a_ref_id > $b_ref_id) {
                            $res = -1;
                        }

                        if ($a_ref_id = $b_ref_id) {
                            $res = 1;
                        }
                    }
                    return $res;
                }
            );
        }

        $ret = [];
        $first_depth = (int) array_shift($depths);
        if ($first_depth) {
            foreach ($rows[$first_depth] as $row) {
                $ret[] = $row;
                $ret = array_merge(
                        $ret,
                        $this->findMyChildren(
                            $first_depth,
                            (int) $row["child"],
                            $rows
                        )
                    );
            }
        }
        return $ret;
    }

    protected function findMyChildren(int $depth, int $parent_ref_id, array $rows) : array
    {
        $ret = [];
        $depth++;
        if (array_key_exists($depth, $rows)) {
            foreach ($rows[$depth] as $row) {
                if ((int) $row["parent"] == $parent_ref_id) {
                    $ret[] = $row;

                    $ret = array_merge(
                        $ret,
                        $this->findMyChildren(
                            $depth,
                            (int) $row["child"],
                            $rows
                        )
                    );
                }
            }
        }

        return $ret;
    }

    public function filter() : Filter\Filters\Sequence
    {
        if ($this->filter === null) {
            $this->filter = $this->buildFilter();
        }
        return $this->filter;
    }

    private function getFilterSettings(array $settings) : array
    {
        $filter = $this->filter();
        $settings = call_user_func_array(array($filter, "content"), $settings);
        return $settings;
    }

    protected function buildFilter() : Filter\Filters\Sequence
    {
        $ff = $this->ff;
        $tyf = $this->tyf;
        return $ff->sequence(
            $ff->dateperiod(
                $this->txt('crs_date'),
                ''
            ),
            $ff->sequence(
                $ff->multiselectsearch(
                    $this->txt('usr_active'),
                    '',
                    [
                        1 => $this->txt('active'),
                        0 => $this->txt('inactive')
                    ]
                ),
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
                    $this->txt('filter_templates'),
                    '',
                    $this->templatesOptions()
                ),
                $ff->multiselectsearch(
                    $this->txt('edu_programme'),
                    '',
                    $this->eduProgrammeOptions()
                ),
                $ff->multiselectsearch(
                    $this->txt('org_unit'),
                    '',
                    $this->orgUnitFilterOptions()
                )
            )
        )->map(
            function (
                $period_start,
                $period_end,
                $usr_active,
                $types,
                $categories,
                $topics,
                $templates,
                $edu_programmes,
                $org_units
            ) {
                return [
                    'period_start' => $period_start,
                    'period_end' => $period_end,
                    'usr_active' => $usr_active,
                    'types' => $types,
                    'categories' => $categories,
                    'topics' => $topics,
                    'templates' => $templates,
                    'edu_programmes' => $edu_programmes,
                    'org_units' => $org_units
                ];
            },
            $tyf->dict(
                [
                    'period_start' => $tyf->cls("\\DateTime"),
                    'period_end' => $tyf->cls("\\DateTime"),
                    'usr_active' => $tyf->lst($tyf->int()),
                    'types' => $tyf->lst($tyf->string()),
                    'categories' => $tyf->lst($tyf->string()),
                    'topics' => $tyf->lst($tyf->string()),
                    'templates' => $tyf->lst($tyf->int()),
                    'edu_programmes' => $tyf->lst($tyf->string()),
                    'org_units' => $tyf->lst($tyf->int())
                ]
            )
        );
    }

    public function applyFilterToSpace(array $settings)
    {
        $settings = $this->getFilterSettings($settings);
        $this->maybeApplyCrsDatesFilter($settings['period_start'], $settings['period_end']);
        $this->maybeApplyTypesFilter($settings['types']);
        $this->maybeApplyCategoriesFilter($settings['categories']);
        $this->maybeApplyTopicsFilter($settings['topics']);
        $this->maybeApplyTemplatesFilter($settings['templates']);
        $this->maybeApplyEduProgrammeFilter($settings['edu_programmes']);
        $this->maybeApplyActiveFilter($settings['usr_active']);
        $this->maybeApplyOrguUnitFilter($settings['org_units']);
    }

    protected function maybeApplyCrsDatesFilter(\DateTime $period_start, \DateTime $period_end)
    {
        $space = $this->space()->table("bookings")->space();
        $usr_crs = $space->table("bookings");
        $crs = $space->table("crs");

        $begin_date = $period_start->format('Y-m-d');
        $end_date = $period_end->format('Y-m-d');

        $begin_date_f = $crs->field('begin_date');
        $end_date_f = $crs->field('end_date');

        $booking_date_f = $usr_crs->field('booking_date');
        $participation_date_f = $usr_crs->field('ps_acquired_date');

        $begin_date_not_null = $begin_date_f->IS_NULL()->_NOT();
        $end_date_not_null = $end_date_f->IS_NULL()->_NOT();

        $booking_date_not_null = $booking_date_f->IS_NULL()->_NOT();
        $participation_date_not_null = $participation_date_f->IS_NULL()->_NOT();

        $is_self_learn = $begin_date_f->IS_NULL()->_OR($begin_date_f->EQ($this->pf->str('0001-01-01')));
        $is_not_self_learn = $is_self_learn->_NOT();

        $begin_date_null = $begin_date_f->IS_NULL();
        $booking_date_null = $booking_date_f->IS_NULL();

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
                )->_OR(
                    $this->pf->_ALL(
                            $booking_date_not_null,
                            $participation_date_f->IS_NULL(),
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
            $space = $this->space();
            $bookings = $space->table("bookings")->space();
            $bookings->addFilter(
                $bookings->table("crs")->field('crs_type')->IN($this->pf->list_string_by_array($types))
            );
        }
    }

    protected function maybeApplyCategoriesFilter(array $categories)
    {
        if (count($categories) > 0) {
            $space = $this->space();
            $cats = $space->table('bookings')->space()->table("categories_filter");
            $cats->space()->addFilter(
                $cats->space()->table("categories")->field('list_data')->IN($this->pf->list_string_by_array($categories))
            );
            $space->table('bookings')->space()->forceRelevant($cats);
        }
    }

    protected function maybeApplyTopicsFilter(array $topics)
    {
        if (count($topics) > 0) {
            $space = $this->space();
            $tops = $space->table('bookings')->space()->table('topics_filter');
            $tops->space()->addFilter(
                $tops->space()->table("topics")->field('list_data')->IN($this->pf->list_string_by_array($topics))
            );
            $space->table('bookings')->space()->forceRelevant($tops);
        }
    }

    protected function maybeApplyTemplatesFilter(array $templates)
    {
        if (count($templates) > 0) {
            $space = $this->space();
            $bookings = $space->table("bookings")->space();
            $bookings->addFilter(
                $bookings->table('copy_mappings')->field('source_id')->IN($this->pf->list_int_by_array($templates))
            );
        }
    }

    protected function maybeApplyEduProgrammeFilter(array $edu_programmes)
    {
        if (count($edu_programmes) > 0) {
            $space = $this->space();
            $bookings = $space->table("bookings")->space();
            $bookings->addFilter(
                $bookings->table("crs")->field('edu_programme')->IN($this->pf->list_string_by_array($edu_programmes))
            );
        }
    }

    protected function maybeApplyActiveFilter(array $active)
    {
        if (count($active) > 0) {
            $space = $this->space();
            $usr = $space->table("users")->space();
            $usr->addFilter(
                $usr->table("usr")->field('active')->IN($this->pf->list_int_by_array($active))
            );
        }
    }

    protected function maybeApplyOrguUnitFilter(array $org_units)
    {
        if (count($org_units) > 0) {
            $this->space()->addFilter(
                $this->space()->table("tr")->field('child')->IN(
                    $this->pf->list_int_by_array($org_units)
                )
            );
        }
    }

    protected function crsTypeOptions() : array
    {
        return $this->getDistinct(self::HEAD_COURSE_TABLE, 'crs_type');
    }

    protected function crsTopicOptions() : array
    {
        return $this->getDistinct(self::HEAD_COURSE_TOPICS_TABLE, 'list_data');
    }

    protected function crsCategoriesOptions() : array
    {
        return $this->getDistinct(self::HEAD_COURSE_CATEGORIES_TABLE, 'list_data');
    }

    protected function eduProgrammeOptions() : array
    {
        return $this->getDistinct(self::HEAD_COURSE_TABLE, 'edu_programme');
    }

    protected function orgUnitFilterOptions() : array
    {
        foreach ($this->getOrguNodeIds($this->parent_orgu_ref_id) as $orgu_ref_id => $orgu_obj_id) {
            $ret[(int) $orgu_ref_id] = \ilObject::_lookupTitle($orgu_obj_id);
        }
        return $ret;
    }

    protected function templatesOptions() : array
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

    protected function getDistinct($table, $column) : array
    {
        $res = $this->db->query(
            'SELECT DISTINCT ' . $column
            . '	FROM ' . $table
            . '	WHERE ' . $column . ' IS NOT NULL '
            . '		AND ' . $column . ' != \'\''
            . '		AND ' . $column . ' != \'-\''
            . '	ORDER BY ' . $column
        );
        $return = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $return[$rec[$column]] = $rec[$column];
        }
        return $return;
    }

    public function space() : TableRelations\Tables\TableSpace
    {
        if (!$this->space) {
            $this->space = $this->buildSpaceCourses();
        }
        return $this->space;
    }

    protected function buildSpaceCourses() : TableRelations\Tables\TableSpace
    {
        $root_path = $this->pathByRefId($this->parent_orgu_ref_id);
        $tree = $this->tf->Table(self::TREE, "tr")
            ->addField($this->tf->field("path"))
            ->addField($this->tf->field("child"))
            ->addField($this->tf->field("depth"))
            ->addField($this->tf->field("parent"));

        $orgu_ua = $this->tf->Table(self::ORGU_UA, "orgu_ua")
            ->addField($this->tf->field("user_id"))
            ->addField($this->tf->field("orgu_id"));

        $object_reference = $this->tf->Table(self::OBJECT_REFERENCE, "oref")
            ->addField($this->tf->field("ref_id"))
            ->addField($this->tf->field("obj_id"))
            ->addField($this->tf->field("deleted"));

        $object_data = $this->tf->Table(self::OBJECT_DATA, "od")
            ->addField($this->tf->field("type"))
            ->addField($this->tf->field("obj_id"))
            ->addField($this->tf->field("title"));


        $usr = $this->tf->Table(self::USR_DATA, 'usr')
            ->addField($this->tf->field('usr_id'))
            ->addField($this->tf->field('active'));

        $users_space = $this->tf->TableSpace()
            ->addTablePrimary($orgu_ua)
            ->addTableSecondary($usr)
            ->setRootTable($orgu_ua)
            ->addDependency(
                $this->tf->TableJoin(
                    $orgu_ua,
                    $usr,
                    $orgu_ua->field('user_id')->EQ($usr->field('usr_id'))
                        
                )
            )
            ->request($orgu_ua->field("user_id"), "usr_id")
            ->request($orgu_ua->field("orgu_id"))
            ->groupBy($orgu_ua->field("user_id"))
            ->groupBy($orgu_ua->field("orgu_id"));
        if (!$this->settings->isGlobal()) {
            $users_space = $users_space->addFilter(
                $orgu_ua->field("orgu_id")->IN(
                    $this->pf->list_int_by_array(
                        $this->childrenIdsByRefId($this->parent_orgu_ref_id)
                    )
                )
            );
        }

        $users = $this->tf->DerivedTable($users_space, "users");


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
            ->request($categories->field('list_data'), 'list_data')
            ->groupBy($categories->field('crs_id'));
        $categories_filter = $this->tf->DerivedTable($categories_filter_space, 'categories_filter');

        $crs = $this->tf->Table(self::HEAD_COURSE_TABLE, "crs")
            ->addField($this->tf->field("is_template"))
            ->addField($this->tf->field("crs_id"))
            ->addField($this->tf->field("begin_date"))
            ->addField($this->tf->field("end_date"))
            ->addField($this->tf->field("crs_type"))
            ->addField($this->tf->field("edu_programme"))
        ;

        $copy_mappings = $this->tf->Table('copy_mappings', 'copy_mappings')
            ->addField($this->tf->field('obj_id'))
            ->addField($this->tf->field('source_id'));

        $bookings_table = $this->tf->Table(self::HEAD_USERCOURSE_TABLE, "bookings")
            ->addField($this->tf->field("usr_id"))
            ->addField($this->tf->field("crs_id"))
            ->addField($this->tf->field("booking_status"))
            ->addField($this->tf->field("participation_status"))
            ->addField($this->tf->field("ps_acquired_date"))
            ->addField($this->tf->field("booking_date"))
        ;

        $bookings_table = $bookings_table->addConstraint(
            $bookings_table->field('booking_status')->IN(
                $this->pf->list_string_by_array(
                    [
                        'participant',
                        'waiting',
                        'approval_pending'
                    ]
                )
            )
        );

        $bookings_space = $this->tf->TableSpace()
            ->addTablePrimary($bookings_table)
            ->addTableSecondary($categories_filter)
            ->addTableSecondary($topics_filter)
            ->addTableSecondary($crs)
            ->addTableSecondary($copy_mappings)
            ->setRootTable($bookings_table)
            ->addDependency(
                $this->tf->TableJoin(
                    $bookings_table,
                    $topics_filter,
                    $bookings_table->field('crs_id')->EQ($topics_filter->field('crs_id'))
                )
            )
            ->addDependency(
                $this->tf->TableJoin(
                    $bookings_table,
                    $categories_filter,
                    $bookings_table->field('crs_id')->EQ($categories_filter->field('crs_id'))
                )
            )
            ->addDependency(
                $this->tf->TableJoin(
                    $bookings_table,
                    $crs,
                    $bookings_table->field("crs_id")->EQ($crs->field("crs_id"))
                )
            )
            ->addDependency(
                $this->tf->TableJoin(
                    $bookings_table,
                    $copy_mappings,
                    $bookings_table->field('crs_id')->EQ($copy_mappings->field('obj_id'))
                )
            )
            ->request($bookings_table->field('crs_id'))
            ->request($bookings_table->field('usr_id'))
            ->request($bookings_table->field('booking_date'))
            ->request($bookings_table->field('ps_acquired_date'))
            ->request($bookings_table->field('booking_status'))
            ->request($bookings_table->field('participation_status'))
            ->addFilter($this->pf->_ANY(
                $crs->field('is_template')->EQ($this->pf->int(0)),
                $crs->field('is_template')->IS_NULL()
            ));
        $bookings = $this->tf->DerivedTable($bookings_space, 'bookings');




        $space = $this->tf->TableSpace()
            ->addTablePrimary($tree)
            ->addTablePrimary($object_reference)
            ->addTablePrimary($object_data)
            ->addTablePrimary($users)
            ->addTablePrimary($bookings)
            ->setRootTable($tree)
            ->addDependency(
                $this->tf->TableJoin(
                    $tree,
                    $object_reference,
                    $tree->field("child")->EQ($object_reference->field("ref_id"))
                )
            )
            ->addDependency(
                $this->tf->TableJoin(
                    $object_reference,
                    $object_data,
                    $object_reference->field("obj_id")->EQ($object_data->field("obj_id"))
                )
            )
            ->addDependency(
                $this->tf->TableLeftJoin(
                    $tree,
                    $users,
                    $tree->field('child')->EQ($users->field('orgu_id'))
                )
            )
            ->addDependency(
                $this->tf->TableLeftJoin(
                    $users,
                    $bookings,
                    $users->field('usr_id')->EQ($bookings->field('usr_id'))
                )
            )
            ->addFilter($object_data->field('type')->EQ($this->pf->str('orgu')))
            ->addFilter($object_reference->field('deleted')->IS_NULL())
            ->request($tree->field("child"))
            ->request($tree->field("path"))
            ->request($object_data->field("title"), "orgu_title")
            ->request($object_data->field("obj_id"))
            ->request($tree->field("depth"))
            ->request($tree->field("parent"))
            ->groupBy($tree->field('child'));
        if (!$this->settings->isGlobal()) {
            $space = $space->addFilter(
                $tree->field("path")->EQ($this->pf->str($root_path))->_OR(
                    $tree->field("path")->LIKE($this->pf->str($root_path . '.%'))
                )
            );
        }
        $space->orderBy(["path", "orgu_title"], "asc");

        return $space;
    }

    protected function interpreter() : TableRelations\SqlQueryInterpreter
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

    public function configureTable(\SelectableReportTableGUI $table) : \SelectableReportTableGUI
    {
        $space = $this->space();
        $table->setRowTemplate('tpl.report_row.html', $this->plugin_dir);

        $title_column_participated = $this->txt('cnt_done');
        $title_column_absent = $this->txt('cnt_failed');
        $title_column_in_progress = $this->txt('cnt_pending');
        list($title_column_participated, $title_column_absent, $title_column_in_progress) =
            $this->maybeEnrichByPStatusTitle($title_column_participated, $title_column_absent, $title_column_in_progress);

        $table
            ->defineFieldColumn(
                $this->txt("orgu_title"),
                "orgu_title",
                [

                ],
                false,
                false
            )
            ->defineFieldColumn(
                $this->txt("cnt_usr"),
                "cnt_usr",
                [
                    "cnt_usr" => $this->tf->countField(
                        'cnt_usr',
                        $space->table("users")->field('usr_id'),
                        true
                    )
                ],
                false,
                false
            )
            ->defineFieldColumn(
                $this->txt("cnt_booked"),
                "cnt_booked",
                [
                    "cnt_booked" => $this->tf->sum(
                        'cnt_booked',
                        $this->tf->ifThenElse(
                            '',
                            $space->table("bookings")->field('booking_status')->EQ($this->pf->str('participant')),
                            $this->tf->constInt('', 1),
                            $this->tf->constInt('', 0)
                        )
                    )
                ],
                true,
                false
            )
            ->defineFieldColumn(
                $this->txt("cnt_waiting"),
                "cnt_waiting",
                [
                    "cnt_waiting" => $this->tf->sum(
                        'cnt_waiting',
                        $this->tf->ifThenElse(
                            '',
                            $space->table("bookings")->field('booking_status')->EQ($this->pf->str('waiting')),
                            $this->tf->constInt('', 1),
                            $this->tf->constInt('', 0)
                        )
                    )
                ],
                true,
                false
            )
            ->defineFieldColumn(
                $this->txt("cnt_approval_pending"),
                "cnt_approval_pending",
                [
                    "cnt_approval_pending" => $this->tf->sum(
                        'cnt_approval_pending',
                        $this->tf->ifThenElse(
                            '',
                            $space->table("bookings")->field('booking_status')->EQ($this->pf->str('approval_pending')),
                            $this->tf->constInt('', 1),
                            $this->tf->constInt('', 0)
                        )
                    )
                ],
                true,
                false
            )
            ->defineFieldColumn(
                $title_column_participated,
                "cnt_done",
                [
                    "cnt_done" => $this->tf->sum(
                        'cnt_done',
                        $this->tf->ifThenElse(
                            '',
                            $space->table("bookings")->field('participation_status')->EQ($this->pf->str('successful')),
                            $this->tf->constInt('', 1),
                            $this->tf->constInt('', 0)
                        )
                    )
                ],
                true,
                false
            )
            ->defineFieldColumn(
                $title_column_in_progress,
                "cnt_pending",
                [
                    "cnt_pending" => $this->tf->sum(
                        'cnt_pending',
                        $this->tf->ifThenElse(
                            '',
                            $space->table("bookings")->field('participation_status')->IN($this->pf->list_string_by_array(['','none', 'in_progress']))
                                ->_OR($space->table("bookings")->field('participation_status')->IS_NULL())
                                ->_AND($space->table("bookings")->field('booking_status')->IS_NULL()->_NOT()),
                            $this->tf->constInt('', 1),
                            $this->tf->constInt('', 0)
                        )
                    )
                ],
                true,
                false
            )
            ->defineFieldColumn(
                $title_column_absent,
                "cnt_failed",
                [
                    "cnt_failed" => $this->tf->sum(
                        'cnt_failed',
                        $this->tf->ifThenElse(
                            '',
                            $space->table("bookings")->field('participation_status')->EQ($this->pf->str('absent')),
                            $this->tf->constInt('', 1),
                            $this->tf->constInt('', 0)
                        )
                    )
                ],
                true,
                false
            )
        ;

        $table->setDefaultSelectedColumns(
            [
                "cnt_booked",
                "cnt_done"
            ]
        );
        $table->prepareTableAndSetRelevantFields($space);

        return $table;
    }

    protected function maybeEnrichByPStatusTitle(
        string $title_column_participated,
        string $title_column_absent,
        string $title_column_in_progress
    ) {
        if (\ilPluginAdmin::isPluginActive('xcmb')) {
            $map = ['participated' => [],'absent' => [],'in_progress' => []];
            $plugin = \ilPluginAdmin::getPluginObjectById('xcmb');
            $options = $plugin->getLPOptionsDB()->select(true);
            foreach ($options as $option) {
                $o_title = $option->getTitle();
                switch ($option->getILIASLP()) {
                    case \ilLPStatus::LP_STATUS_COMPLETED_NUM:
                        $map['participated'][] = $o_title;
                        break;
                    case \ilLPStatus::LP_STATUS_FAILED_NUM:
                        $map['absent'][] = $o_title;
                        break;
                    case \ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM:
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

    protected function relevantTrainingIds() : array
    {
        if ($this->relevant_training_ids === null) {
            $parent = $this->o_d->getParentOfObjectOfType($this->object, 'root');
            $this->relevant_training_ids = $this->o_d->getAllChildrenIdsByTypeOfObject($parent, 'crs');
        }
        return $this->relevant_training_ids;
    }

    protected function getOrguOpitions(int $org_unit) : array
    {
        $children = $this->getOrguNodeIds($org_unit);
        return array_unique(
            array_merge(
                [$org_unit],
                array_keys($children)
            )
        );
    }

    protected function getOrguNodeIds(int $org_unit) : array
    {
        return $this->o_d->getAllChildrenNodeIdsByTypeOfObject(
            $org_unit,
            "orgu"
        );
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }

    protected function pathByRefId(int $ref_id) : string
    {
        $q = 'SELECT path FROM tree'
            . '	WHERE child = ' . $this->db->quote($ref_id, 'integer');
        if ($rec = $this->db->fetchAssoc($this->db->query($q))) {
            return $rec['path'];
        }
        throw new \ilException('invalid ref_id ' . $ref_id);
    }

    protected function childrenIdsByRefId(int $ref_id) : array
    {
        $path = $this->pathByRefId($ref_id);
        $q = 'SELECT child FROM tree'
            . '	WHERE path = ' . $this->db->quote($path, 'text')
            . '		OR path LIKE ' . $this->db->quote($path . '%', 'text');
        $res = $this->db->query($q);
        $return = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $return[] = (int) $rec['child'];
        }
        return $return;
    }
}
