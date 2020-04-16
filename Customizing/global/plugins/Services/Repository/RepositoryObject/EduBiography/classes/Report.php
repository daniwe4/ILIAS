<?php

namespace CaT\Plugins\EduBiography;

use ILIAS\TMS\Filter;
use ILIAS\TMS\TableRelations;

abstract class Report
{
    const HEAD_COURSE_TABLE = "hhd_crs";
    const HEAD_COURSE_TUT_TABLE = "hhd_crs_tut";
    const HEAD_COURSE_TOPICS_TABLE = "hhd_crs_topics";
    const HEAD_COURSE_CATEGORIES_TABLE = "hhd_crs_categories";
    const HEAD_USERCOURSE_TABLE = "hhd_usrcrs";
    const HEAD_USERCOURSE_NIGHTS_TABLE = "hhd_usrcrs_nights";
    const KEY_DEFAULT_YEAR_PRESET = "default_year";

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
    protected $ilDB;

    public function __construct(\ilEduBiographyPlugin $plugin, \ilDBInterface $ilDB)
    {
        $this->plugin = $plugin;

        $this->gf = new TableRelations\GraphFactory();
        $this->pf = new Filter\PredicateFactory();
        $this->tf = new TableRelations\TableFactory($this->pf, $this->gf);
        $this->tyf = new Filter\TypeFactory();
        $this->ff = new Filter\FilterFactory($this->pf, $this->tyf);

        $this->ilDB = $ilDB;
    }

    /**
     * Get the data for the report.
     *
     * @return	array
     */
    public function fetchData()
    {
        $res = $this->ilDB->query($this->interpreter()->getSql($this->space()->query()));
        $return = [];
        while ($row = $this->ilDB->fetchAssoc($res)) {
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
    abstract protected function postprocessRowHTML(array $row);

    /**
     * Postprocess a row from the database to display it as anything.
     *
     * @param	array	$row
     * @return	array
     */
    abstract protected function postprocessRowCommon(array $row);

    /**
     * Get an unique identifier for the report
     *
     * @return string
     */
    abstract public function getReportIdentifier();

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
     * Build filter configuration.
     *
     * @return	ILIAS\TMS\Filter\Filters\Filter
     */
    abstract protected function buildFilter();

    protected function crsTypeOptions()
    {
        return $this->getDistinct(self::HEAD_COURSE_TABLE, 'crs_type');
    }

    protected function crsTopicOptions()
    {
        $topics = $this->getDistinct(self::HEAD_COURSE_TOPICS_TABLE, 'list_data');
        $invisible_crs_topics = [];
        if ($this->getSettings()->hasSuperiorOverview()) {
            $invisible_crs_topics = $this->getSettings()->getInvisibleCourseTopics();
        }
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

    protected function bookingStatusOptions()
    {
        $plugin = $this->plugin;
        $return = $this->getDistinct(self::HEAD_USERCOURSE_TABLE, "booking_status");
        $dont_show = array("cancelled", "waiting_self_cancelled");
        $return = array_filter(
            $return,
            function ($r) use ($dont_show) {
                return !in_array($r, $dont_show);
            }
        );
        $return = array_map(function ($r) use ($plugin) {
            return $plugin->txt($r);
        }, $return);
        asort($return);
        return $return;
    }
    protected function participationStatusOptions()
    {
        $plugin = $this->plugin;
        $return = ['none' => $plugin->txt('none')
                ,'successful' => $plugin->txt('successful')
                ,'absent' => $plugin->txt('absent')];
        asort($return);
        return $return;
    }

    protected function getDistinct($table, $column)
    {
        $res = $this->ilDB->query(
            'SELECT DISTINCT ' . $column
                . '	FROM ' . $table
                . '	WHERE ' . $column . ' IS NOT NULL '
                . '		AND ' . $column . ' != \'\''
                . '		AND ' . $column . '	!= \'-\''
                . '	ORDER BY ' . $column
        );
        $return = [];
        while ($rec = $this->ilDB->fetchAssoc($res)) {
            $return[$rec[$column]] = $rec[$column];
        }
        return $return;
    }

    protected function yearOptions()
    {
        $current = (int) date('Y');
        $return = [];
        for ($i = $current - 11; $i < $current + 3; $i++) {
            $return[$i] = (string) $i;
        }
        return $return;
    }


    protected $default_year;

    protected function getDefaultYear()
    {
        return $this->default_year === null ? (int) date('Y') : $this->default_year;
    }

    public function setDefaultYear($year)
    {
        assert('is_int($year)');
        $this->default_year = (int) $year;
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
    abstract protected function buildSpace();

    /**
     * @return	TableRelations\SqlQueryInterpreter
     */
    protected function interpreter()
    {
        if (!$this->interpreter) {
            $this->interpreter = new TableRelations\SqlQueryInterpreter(new Filter\SqlPredicateInterpreter($this->ilDB), $this->pf, $this->ilDB);
        }
        return $this->interpreter;
    }

    /**
     * Applies the filter settings to the data.
     *
     * @param	array	$settings
     * @return	void
     */
    abstract public function applyFilterToSpace(array $settings);

    /**
     * Configures the table that displays the reports data.
     *
     * @param	SelectableReportTableGUI	$table
     * @return	void
     */
    abstract public function configureTable(\SelectableReportTableGUI $table);

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

    /**
     * Checks the edu tracking plugin is active
     *
     * @return bool
     */
    public function isEduTrackingActive()
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

    /**
     * Get a formatted array of filter selections.
     *
     * @param	mixed[]	$settings
     * @return	mixed[]
     */
    protected function createFilterSettings(array $settings)
    {
        $filter = $this->filter();
        $settings = call_user_func_array(array($filter, "content"), $settings);
        return $settings;
    }

    abstract protected function getSettings() : Settings\Settings;
}
