<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types = 1);

namespace CaT\Plugins\WBDManagement\Reports\ErrorReport;

use ILIAS\TMS\Filter;
use ILIAS\TMS\TableRelations;

class Report
{
    const T_REQUEST_ERROR = "wbd_request_errors";
    const T_USR_DATA = "usr_data";

    const F_REQUEST_DATE = "request_date";
    const F_LOGIN = "login";
    const F_GUTBERATEN_ID = "gutberaten_id";
    const F_STATUS = "status";

    const C_ID = "id";
    const C_USR_ID = "usr_id";
    const C_LOGIN = "login";
    const C_FIRSTNAME = "firstname";
    const C_LASTNAME = "lastname";
    const C_GUTBERATEN_ID = "gutberaten_id";
    const C_CRS_ID = "crs_id";
    const C_CRS_TITLE = "crs_title";
    const C_LEARNING_TIME = "learning_time";
    const C_MESSAGE = "message";
    const C_REQUEST_DATE = "request_date";
    const C_STATUS = "status";
    const C_ACTIONS = "actions";
    const C_REF_ID = 'ref_id';

    /**
     * @var Filter\Filters\Filter|null
     */
    protected $filter = null;

    /**
     * @var TableRelations\Tables\TableSpace | null
     */
    protected $space = null;

    /**
     * @var \ilEduBiographyPlugin
     */
    protected $plugin;

    /**
     * @var string
     */
    protected $plugin_dir;

    /**
     * @var TableRelations\GraphFactory
     */
    protected $gf;

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
     * @var CrsLinkHelper
     */
    protected $clh;

    /**
     * @var TableRelations\SqlQueryInterpreter
     */
    protected $interpreter;

    /**
     * @var ActionLinksHelper
     */
    protected $action_links;

    /**
     * @var \ilDBInterface
     */
    protected $ilias_db;

    public function __construct(
        \Closure $txt,
        string $plugin_dir,
        TableRelations\TableFactory $tf,
        Filter\FilterFactory $ff,
        Filter\TypeFactory $tyf,
        Filter\PredicateFactory $pf,
        TableRelations\SqlQueryInterpreter $interpreter,
        ActionLinksHelper $action_links,
        CrsLinkHelper $clh,
        \ilDBInterface $ilias_db
    ) {
        $this->txt = $txt;
        $this->plugin_dir = $plugin_dir;
        $this->tf = $tf;
        $this->ff = $ff;
        $this->tyf = $tyf;
        $this->pf = $pf;
        $this->interpreter = $interpreter;
        $this->action_links = $action_links;
        $this->clh = $clh;
        $this->ilias_db = $ilias_db;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function fetchData()
    {
        $res = $this->ilias_db->query($this->interpreter->getSql($this->space()->query()));
        $return = [];
        while ($row = $this->ilias_db->fetchAssoc($res)) {
            $return[] = $this->postprocessRowHTML($row);
        }
        return $return;
    }

    /**
     * @param array $row
     * @return array
     * @throws \Exception
     */
    protected function postprocessRowHTML(array $row)
    {
        if (array_key_exists(self::C_ID, $row) && $row[self::C_ID] !== null) {
            $row['actions'] = $this->actionMenuFor((int) $row[self::C_ID]);
        }
        return $this->postprocessRowCommon($row);
    }


    protected function actionMenuFor(int $id) : string
    {
        $l = new \ilAdvancedSelectionListGUI();
        $l->setListTitle($this->txt("please_choose"));

        $l->addItem(
            $this->txt("mark_resolved"),
            "",
            $this->action_links->getResolveLinkFor($id)
        );

        $l->addItem(
            $this->txt("mark_not_resolvable"),
            "",
            $this->action_links->getNotResolvableLinkFor($id)
        );

        $l->setId("selection_list_" . $id);
        return $l->getHTML();
    }


    /**
     * @param array $row
     * @return array
     * @throws \Exception
     */
    protected function postprocessRowCommon(array $row)
    {
        if (array_key_exists(self::C_LEARNING_TIME, $row)) {
            $row[self::C_LEARNING_TIME] = $this->minutesToTimeString((int) $row[self::C_LEARNING_TIME]);
        }

        if (array_key_exists(self::C_REQUEST_DATE, $row)) {
            $request_date = new \DateTime($row[self::C_REQUEST_DATE]);
            $row[self::C_REQUEST_DATE] = $request_date->format("d.m.Y H:i:s");
        }

        if (array_key_exists(self::C_STATUS, $row)) {
            $row[self::C_STATUS] = $this->txt($row[self::C_STATUS]);
        }

        if (array_key_exists("multi", $row)) {
            require_once __DIR__ . "/class.ilWBDReportGUI.php";
            $row["multi"] = '<input type="checkbox" name="' . \ilWBDReportGUI::P_MULTI_IDS . '[]" value="' . $row["multi"] . '" />';
        }

        $row[self::C_REF_ID] = '';
        if (array_key_exists(self::C_CRS_ID, $row)) {
            $crs_id = $row[self::C_CRS_ID];
            $ref_id = (int) array_shift(\ilObject::_getAllReferences($crs_id));
            if ($ref_id) {
                $row[self::C_REF_ID] = $this->clh->renderLink($ref_id);
            }
        }

        foreach ($row as $key => $value) {
            if (is_null($value)) {
                $row[$key] = "-";
            }
        }

        return $row;
    }

    /**
     * Get the filter for the report.
     *
     * @return \ILIAS\TMS\Filter\Filters\Filter
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
        // dirty hack, set default values at first load
        if (!isset($_POST["filter"]) && !isset($_GET["filter_params"])) {
            $settings[self::F_STATUS] = [Entry::STATUS_OPEN];
        }

        return $settings;
    }


    /**
     * Build filter configuration.
     *
     * @return \ILIAS\TMS\Filter\Filters\Filter
     */
    protected function buildFilter()
    {
        $ff = $this->ff;
        $tyf = $this->tyf;
        return $ff->sequence(
            $ff->dateperiod(
                $this->txt(self::F_REQUEST_DATE),
                ''
            ),
            $ff->sequence(
                $ff->text(
                    $this->txt(self::F_LOGIN),
                    ''
                ),
                $ff->text(
                    $this->txt(self::F_GUTBERATEN_ID),
                    ''
                ),
                $ff->multiselectsearch(
                    $this->txt(self::F_STATUS),
                    '',
                    [
                        Entry::STATUS_OPEN => $this->txt(Entry::STATUS_OPEN),
                        Entry::STATUS_RESOLVED => $this->txt(Entry::STATUS_RESOLVED),
                        Entry::STATUS_NOT_RESOLVABLE => $this->txt(Entry::STATUS_NOT_RESOLVABLE)
                    ]
                )->default_choice(array(Entry::STATUS_OPEN))
            )
        )->map(function ($request_date_start, $request_date_end, $login, $gutberaten_id, $status) {
            return [
                self::F_REQUEST_DATE . '_start' => $request_date_start,
                self::F_REQUEST_DATE . '_end' => $request_date_end,
                self::F_LOGIN => $login,
                self::F_GUTBERATEN_ID => $gutberaten_id,
                self::F_STATUS => $status
            ];
        }, $tyf->dict(
            [
                self::F_REQUEST_DATE . '_start' => $tyf->either($tyf->cls("\\DateTime"), $tyf->string()),
                self::F_REQUEST_DATE . '_end' => $tyf->either($tyf->cls("\\DateTime"), $tyf->string()),
                self::F_LOGIN => $tyf->string(),
                self::F_GUTBERATEN_ID => $tyf->string(),
                self::F_STATUS => $tyf->lst($tyf->string())
            ]
        ));
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
        $this->maybeApplyRequestDateFilter($settings[self::F_REQUEST_DATE . '_start'], $settings[self::F_REQUEST_DATE . '_end']);
        $this->maybeApplyLoginFilter($settings[self::F_LOGIN]);
        $this->maybeApplyGutberatenIdFilter($settings[self::F_GUTBERATEN_ID]);
        $this->maybeApplyStatusFilter($settings[self::F_STATUS]);
    }

    protected function maybeApplyRequestDateFilter($request_date_start, $request_date_end)
    {
        $request_date = $this->space()->table(self::T_REQUEST_ERROR)->field(self::C_REQUEST_DATE);
        $predicate_request_date = $this->pf->_ALL(
            $request_date->LE()->str($request_date_end->format("Y-m-d")),
            $request_date->GE()->str($request_date_start->format("Y-m-d"))
        );
        $this->space()->addFilter($predicate_request_date);
    }

    protected function maybeApplyLoginFilter(string $login)
    {
        if ($login != '') {
            $c_login = $this->space()->table(self::T_USR_DATA)->field(self::C_LOGIN);
            $predicate_login = $c_login->EQ($this->pf->str($login));
            $this->space()->addFilter($predicate_login);
        }
    }

    protected function maybeApplyGutberatenIdFilter(string $gutberaten_id)
    {
        if ($gutberaten_id != "") {
            $c_gutberaten_id = $this->space()->table(self::T_REQUEST_ERROR)->field(self::C_GUTBERATEN_ID);
            $predicate_gutberaten_id = $c_gutberaten_id->EQ($this->pf->str($gutberaten_id));
            $this->space()->addFilter($predicate_gutberaten_id);
        }
    }

    protected function maybeApplyStatusFilter(array $status)
    {
        if (count($status) > 0) {
            $c_status = $this->space()->table(self::T_REQUEST_ERROR)->field(self::C_STATUS);
            $predicate_status = $c_status->IN($this->pf->list_string_by_array($status));
            $this->space()->addFilter($predicate_status);
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
     * @return TableRelations\Tables\TableSpace
     */
    protected function buildSpace()
    {
        $request_data = $this->tf->Table(
            self::T_REQUEST_ERROR,
            self::T_REQUEST_ERROR
        )
            ->addField($this->tf->field(self::C_ID))
            ->addField($this->tf->field(self::C_USR_ID))
            ->addField($this->tf->field(self::C_GUTBERATEN_ID))
            ->addField($this->tf->field(self::C_CRS_ID))
            ->addField($this->tf->field(self::C_CRS_TITLE))
            ->addField($this->tf->field(self::C_LEARNING_TIME))
            ->addField($this->tf->field(self::C_MESSAGE))
            ->addField($this->tf->field(self::C_REQUEST_DATE))
            ->addField($this->tf->field(self::C_STATUS))
        ;

        $usr_data = $this->tf->Table(
            self::T_USR_DATA,
            self::T_USR_DATA
        )
            ->addField($this->tf->field(self::C_USR_ID))
            ->addField($this->tf->field(self::C_LOGIN))
            ->addField($this->tf->field(self::C_FIRSTNAME))
            ->addField($this->tf->field(self::C_LASTNAME))
        ;

        return $this->tf->TableSpace()
            ->addTablePrimary($request_data)
            ->addTableSecondary($usr_data)
            ->setRootTable($request_data)
            ->addDependency(
                $this->tf->TableLeftJoin($request_data, $usr_data, $request_data->field(self::C_USR_ID)->EQ($usr_data->field(self::C_USR_ID)))
            )
        ;
    }

    public function configureTable(\SelectableReportTableGUI $table)
    {
        $space = $this->space();
        $table->setRowTemplate('tpl.report_row.html', $this->plugin_dir);

        $table
            ->defineFieldColumn(
                "",
                "multi",
                ["multi" => $space->table(self::T_REQUEST_ERROR)->field(self::C_ID)]
            )
            ->defineFieldColumn(
                $this->txt(self::C_ID),
                self::C_ID,
                [self::C_ID => $space->table(self::T_REQUEST_ERROR)->field(self::C_ID)],
                false,
                false
            )
            ->defineFieldColumn(
                $this->txt(self::C_USR_ID),
                self::C_USR_ID,
                [self::C_USR_ID => $space->table(self::T_REQUEST_ERROR)->field(self::C_USR_ID)],
                false,
                false
            )
            ->defineFieldColumn(
                $this->txt(self::C_LOGIN),
                self::C_LOGIN,
                [self::C_LOGIN => $space->table(self::T_USR_DATA)->field(self::C_LOGIN)],
                false,
                false
            )
            ->defineFieldColumn(
                $this->txt(self::C_FIRSTNAME),
                self::C_FIRSTNAME,
                [self::C_FIRSTNAME => $space->table(self::T_USR_DATA)->field(self::C_FIRSTNAME)],
                false,
                false
            )
            ->defineFieldColumn(
                $this->txt(self::C_LASTNAME),
                self::C_LASTNAME,
                [self::C_LASTNAME => $space->table(self::T_USR_DATA)->field(self::C_LASTNAME)],
                false,
                false
            )
            ->defineFieldColumn(
                $this->txt(self::C_GUTBERATEN_ID),
                self::C_GUTBERATEN_ID,
                [self::C_GUTBERATEN_ID => $space->table(self::T_REQUEST_ERROR)->field(self::C_GUTBERATEN_ID)],
                true,
                false
            )
            ->defineFieldColumn(
                $this->txt(self::C_REF_ID),
                self::C_REF_ID,
                [self::C_CRS_ID => $space->table(self::T_REQUEST_ERROR)->field(self::C_CRS_ID)],
                false,
                false
            )
            ->defineFieldColumn(
                $this->txt(self::C_CRS_TITLE),
                self::C_CRS_TITLE,
                [self::C_CRS_TITLE => $space->table(self::T_REQUEST_ERROR)->field(self::C_CRS_TITLE)],
                true,
                false
            )
            ->defineFieldColumn(
                $this->txt(self::C_LEARNING_TIME),
                self::C_LEARNING_TIME,
                [self::C_LEARNING_TIME => $space->table(self::T_REQUEST_ERROR)->field(self::C_LEARNING_TIME)],
                true,
                false
            )
            ->defineFieldColumn(
                $this->txt(self::C_MESSAGE),
                self::C_MESSAGE,
                [self::C_MESSAGE => $space->table(self::T_REQUEST_ERROR)->field(self::C_MESSAGE)],
                true,
                false
            )
            ->defineFieldColumn(
                $this->txt(self::C_REQUEST_DATE),
                self::C_REQUEST_DATE,
                [self::C_REQUEST_DATE => $space->table(self::T_REQUEST_ERROR)->field(self::C_REQUEST_DATE)],
                false,
                false
            )
            ->defineFieldColumn(
                $this->txt(self::C_STATUS),
                self::C_STATUS,
                [self::C_STATUS => $space->table(self::T_REQUEST_ERROR)->field(self::C_STATUS)],
                true,
                false
            )
            ->defineFieldColumn(
                $this->txt(self::C_ACTIONS),
                self::C_ACTIONS,
                [self::C_ID => $space->table(self::T_REQUEST_ERROR)->field(self::C_ID)],
                false,
                false,
                true
            );

        $table->setDefaultOrderColumn(self::C_LOGIN, \SelectableReportTableGUI::ORDER_DESC);
        $table->prepareTableAndSetRelevantFields($space);
        $this->space = $space;
        return $table;
    }

    protected function minutesToTimeString(int $minutes) : string
    {
        $hours = floor($minutes / 60);
        $minutes = $minutes - $hours * 60;

        return str_pad((string) $hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad((string) $minutes, 2, '0', STR_PAD_LEFT);
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}
