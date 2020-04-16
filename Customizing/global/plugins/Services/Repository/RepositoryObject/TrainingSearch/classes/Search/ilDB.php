<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingSearch\Search;

require_once("Services/Component/classes/class.ilPluginAdmin.php");
require_once __DIR__ . "/class.ilCoursesGUI.php";

class ilDB implements DB
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var \ilAccess
     */
    protected $access;

    /**
     * @var \ilTree
     */
    protected $tree;

    /**
     * @var	ilObjectFactory
     */
    protected $factory;

    /**
     * @var bool
     */
    protected $venue_plugin_active;

    /**
     * @var \ilPlugin|null
     */
    protected $venue_plugin_object = null;

    public function __construct(
        \ilDBInterface $db,
        \ilAccess $access,
        \ilTree $tree,
        ilObjectFactory $factory
    ) {
        $this->db = $db;
        $this->access = $access;
        $this->tree = $tree;
        $this->factory = $factory;

        $this->venue_plugin_active = \ilPluginAdmin::isPluginActive('venues');
        if ($this->venue_plugin_active) {
            $this->venue_plugin_object = \ilPluginAdmin::getPluginObjectById('venues');
        }
    }

    /**
     * @inheritdoc
     */
    public function getCoursesFor(Options $options) : array
    {
        if (!\ilPluginAdmin::isPluginActive('xccl')) {
            return [];
        }

        // The rest has been filtered on the database
        $crss = iterator_to_array(
            $this->toCourses(
                $this->filterIddRelevant(
                    $options->getIddRelevantFilter(),
                    $this->filterOnlyBookable(
                        $options->getOnlyBookableFilter(),
                        $this->filterRBAC(
                            $options->getUserId(),
                            $this->filterVenueSearchTags(
                                $options->getVenueSearchTagFilter(),
                                /*$this->filterGoals(
                                    $options->getTextFilter(),
                                    $this->filterContents(
                                        $options->getTextFilter(),*/
                                        $this->getPrefilteredAndSortedCourses($options)
                                //)
                                //)
                            )
                        )
                    )
                )
            )
        );

        // The rest has been sorted on the database.
        $crss = $this->maybeSortByCity($options->getSortation(), $crss);

        return $crss;
    }

    /**
     * Get a set of courses that a prefiltered and sorted as much as possible
     * without invoking ente- or rbac-logic on courses.
     *
     * This determines a base set of courses by starting to walk down recursively
     * from the upper node (repository or category) of the search. Only readable
     * containers are considered for recursing down. Courses in these containers
     * are included in the search without further consideration of rbac.
     *
     * Courses where the user already is a member are excluded, as well as courses
     * they do not start today or in the future.
     *
     * The following filters are executed on the database (object_data, crs_settings
     * and history-tables):
     *  - title
     *  - period
     *  - type
     *  - topic
     *  - category
     *
     * The following sortations are executed on the database (object_data, crs_settings):
     *  - title
     *  - period
     *
     * The method returns an array of $ref_id => $obj_id as result.
     *
     * @return array<int,int>
     */
    protected function getPrefilteredAndSortedCourses(Options $options) : \Generator
    {
        return $this->getPrefilteredAndSortedCoursesFromDB(
            $options,
            array_diff_key(
                $this->getBaseSetOfCoursesForUser(
                    $options->getUserId(),
                    $options->getCategoryRefId()
                ),
                $this->getUsersCourses(
                    $options->getUserId()
                )
            )
        );
    }

    protected function toCourses(\Traversable $crss) : \Generator
    {
        foreach ($crss as $ref_id => $obj_id) {
            $crs = $this->factory->getCourseFor($ref_id, $obj_id);
            if (is_null($crs)) {
                continue;
            }
            yield $crs;
        }
    }

    protected function filterRBAC(int $user_id, \Traversable $crss) : \Generator
    {
        return $this->yieldIf($crss, function ($ref_id, $obj_id) use ($user_id) {
            return $this->access->checkAccessOfUser(
                $user_id,
                "visible",
                "",
                $ref_id,
                "crs",
                $obj_id
            );
        });
    }

    protected function filterGoals(string $goals_filter = null, \Traversable $crss) : \Traversable
    {
        if (is_null($goals_filter)) {
            return $crss;
        }

        return $this->yieldIf($crss, function ($ref_id, $obj_id) use ($goals_filter) {
            $xccl = $this->factory->getCourseClassificationObjFor($ref_id);
            if (is_null($xccl)) {
                return false;
            }
            $goals = $xccl
                ->getCourseClassification()
                ->getGoals();
            return $this->containsText($goals_filter, $goals);
        });
    }

    protected function filterContents(string $content_filter = null, \Traversable $crss) : \Traversable
    {
        if (is_null($content_filter)) {
            return $crss;
        }
        return $this->yieldIf($crss, function ($ref_id, $obj_id) use ($content_filter) {
            $xccl = $this->factory->getCourseClassificationObjFor($ref_id);
            if (is_null($xccl)) {
                return false;
            }
            $content = $xccl
                ->getCourseClassification()
                ->getContent();
            return $this->containsText($content_filter, $content);
        });
    }

    protected function filterOnlyBookable(bool $only_bookable_filter, \Traversable $crss) : \Traversable
    {
        if (!$only_bookable_filter) {
            return $crss;
        }

        return $this->yieldIf($crss, function ($ref_id, $obj_id) {
            $crs = $this->factory->getCourseFor($ref_id, $obj_id);
            return !is_null($crs) && $crs->isBookable();
        });
    }

    protected function filterIddRelevant(bool $idd_relevant_filter, \Traversable $crss) : \Generator
    {
        if (!$idd_relevant_filter) {
            return $crss;
        }

        return $this->yieldIf($crss, function ($ref_id, $obj_id) {
            $crs = $this->factory->getCourseFor($ref_id, $obj_id);
            return !is_null($crs) && $crs->isIDDRelevant();
        });
    }

    protected function filterVenueSearchTags($search_tag_filter, \Traversable $crss) : \Generator
    {
        if (!$this->venue_plugin_active) {
            return $crss;
        }

        if (is_null($search_tag_filter)) {
            return $crss;
        }

        return $this->yieldIf(
            $crss,
            function ($ref_id, $obj_id) use ($search_tag_filter) {
                list($venue_id, $city, $address, $name, $postcode, $custom_assignment, $addtitional_info, $tags) = $this->venue_plugin_object->getVenueInfos($obj_id);
                if ($custom_assignment === true) {
                    return false;
                }

                return in_array(
                    $search_tag_filter,
                    array_map(
                        function ($tag) {
                            return $tag->getId();
                        },
                        $tags
                    )
                );
            }
        );
    }

    protected function yieldIf(\Traversable $orig, \Closure $condition) : \Generator
    {
        foreach ($orig as $key => $value) {
            if ($condition($key, $value)) {
                yield $key => $value;
            }
        }
    }

    protected function containsText(string $needle, string $haystack)
    {
        return strpos(strtolower($haystack), strtolower($needle)) !== false;
    }

    /**
     * This is the work horse of getPrefilteredAndSortedCourses.
     *
     * @param	array<int,int>	$ids
     * @return	array<int,int>
     */
    protected function getPrefilteredAndSortedCoursesFromDB(Options $options, array $ids) : \Generator
    {
        $crs_id_in_object_data = $this->db->in("object_data.obj_id", $ids, false, "integer");

        $today = $this->db->quote(date("Y-m-d"), "string");

        $title = $options->getTextFilter();
        if (!is_null($title)) {
            $title_filter =
                "AND object_data.title LIKE " . $this->db->quote("%$title%", "string");
        } else {
            $title_filter = "-- no title filter";
        }

        $start = $options->getDurationStartFilter();
        $end = $options->getDurationEndFilter();
        if (!is_null($start) && !is_null($end)) {
            $period_filter =
                "AND ((
				    (
				        hhd_crs.begin_date >= $today AND
				        hhd_crs.begin_date <= " . $this->db->quote($end->format("Y-m-d"), "string") .
                    ")" .
                " AND hhd_crs.end_date >= " . $this->db->quote($start->format("Y-m-d"), "string") . ") OR " .
                " hhd_crs.begin_date = '0001-01-01')";
        } else {
            $period_filter = "AND (hhd_crs.begin_date >= $today OR hhd_crs.begin_date = '0001-01-01')";
        }

        $type = $options->getTypeFilter();
        if (!is_null($type)) {
            $type_filter =
                "AND hhd_crs.crs_type = " . $this->db->quote($type, "string");
        } else {
            $type_filter = "-- no type filter";
        }

        $topic = $options->getTopicFilter();
        if (!is_null($topic)) {
            $topic_filter =
                "AND hhd_crs_topics.list_data = " . $this->db->quote($topic, "string");
        } else {
            $topic_filter = "-- no topic filter";
        }

        $category = $options->getCategoryFilter();
        if (!is_null($category)) {
            $category_filter =
                "AND hhd_crs_categories.list_data = " . $this->db->quote($category, "string");
        } else {
            $category_filter = "-- no category filter";
        }

        $target_group = $options->getTargetGroupsFilter();
        if (!is_null($target_group)) {
            $target_groups_filter =
                "AND hhd_crs_target_groups.list_data = " . $this->db->quote($target_group, "text");
        } else {
            $target_groups_filter = "-- no category filter";
        }

        $sortation = $options->getSortation();
        $date_sort = "";
        switch ($sortation) {
            case Options::SORTATION_TITLE_ASC:
                $order = "object_data.title ASC";
                break;
            case Options::SORTATION_TITLE_DESC:
                $order = "object_data.title DESC";
                break;
            case Options::SORTATION_PERIOD_ASC:
                $date_sort = ", IF(hhd_crs.begin_date = '0001-01-01', '9999-01-01', hhd_crs.begin_date) AS date_sort";
                $order = "date_sort ASC";
                break;
            case Options::SORTATION_PERIOD_DESC:
                $date_sort = ", hhd_crs.begin_date AS date_sort";
                $order = "date_sort DESC";
                break;
            case Options::SORTATION_CITY_ASC:
            case Options::SORTATION_CITY_DESC:
                $order = "-- no database-executable order, using default" . PHP_EOL
                         . "   hhd_crs.begin_date ASC, hhd_crs.end_date ASC";

                break;
            default:
                throw new \LogicException("Unknown sortation: $sortation");
        }

        $query = <<<SQL
SELECT DISTINCT
  object_data.obj_id, object_reference.ref_id $date_sort
FROM
  object_data
JOIN
  object_reference ON object_data.obj_id = object_reference.obj_id
JOIN
  hhd_crs ON hhd_crs.crs_id = object_data.obj_id
LEFT JOIN
  hhd_crs_topics ON hhd_crs_topics.crs_id = object_data.obj_id
LEFT JOIN
  hhd_crs_categories ON hhd_crs_categories.crs_id = object_data.obj_id
LEFT JOIN
  hhd_crs_target_groups ON hhd_crs_target_groups.crs_id = object_data.obj_id
JOIN
  crs_settings ON crs_settings.obj_id = object_data.obj_id
WHERE
  $crs_id_in_object_data
  AND object_data.type = "crs"
  AND (hhd_crs.is_template = 0 OR hhd_crs.is_template IS NULL)
  AND object_reference.deleted IS NULL
  AND crs_settings.activation_type = 1
  $title_filter
  $period_filter
  $type_filter
  $topic_filter
  $category_filter
  $target_groups_filter
ORDER BY
  $order
SQL;

        $res = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($res)) {
            yield (int) $row["ref_id"] => (int) $row["obj_id"];
        }
    }

    /**
     * Returns an array of ref_id => obj_ids of some lists of courses that might
     * be bookable by a user.
     *
     * @return array<int,int>
     */
    protected function getBaseSetOfCoursesForUser(int $user_id, int $root_ref_id) : array
    {
        $categories = [$root_ref_id];
        $courses = [];

        while (count($categories) > 0) {
            $category = array_shift($categories);
            $children = $this->tree->getChildsByTypeFilter($category, ["crs", "cat", "grp"]);
            foreach ($children as $child) {
                $obj_id = (int) $child["obj_id"];
                $ref_id = (int) $child["child"];
                $type = $child["type"];
                if ($type === "crs") {
                    $courses[$ref_id] = $obj_id;
                } elseif ($this->access->checkAccessOfUser($user_id, "read", "", $ref_id, $type, $obj_id)
                    && $this->access->checkAccessOfUser($user_id, "visible", "", $ref_id, $type, $obj_id)
                ) {
                    $categories[] = $ref_id;
                }
            }
        }

        return $courses;
    }

    /**
     * Returns an array of ref_id => obj_id of all courses the user has a role in.
     *
     * @return array<int,int>
     */
    protected function getUsersCourses(int $user_id) : array
    {
        $obj_ids = array_merge(
            \ilParticipants::_getMembershipByType($user_id, "crs"),
            \ilWaitingList::getIdsWhereUserIsOnList($user_id)
        );

        $res = [];
        // TODO: This could be faster if only ilParticipants would tell the ref_ids
        // directly...
        foreach ($obj_ids as $obj_id) {
            $ref_ids = \ilObject::_getAllReferences($obj_id);
            if (count($ref_ids) === 0) {
                continue;
            }
            $res[array_shift($ref_ids)] = $obj_id;
        }
        return $res;
    }
    /**
     * @param string[] 	$values
     * @param Course[]
     *
     * @return Course[]
     */
    protected function maybeSortByCity(string $sortation, array $courses) : array
    {
        switch ($sortation) {
            case Options::SORTATION_TITLE_ASC:
            case Options::SORTATION_TITLE_DESC:
            case Options::SORTATION_PERIOD_ASC:
            case Options::SORTATION_PERIOD_DESC:
                break;
            case Options::SORTATION_CITY_ASC:
                uasort($courses, function ($a, $b) {
                    return strcmp($a->getLocation(), $b->getLocation());
                });
                break;
            case Options::SORTATION_CITY_DESC:
                uasort($courses, function ($a, $b) {
                    return strcmp($b->getLocation(), $a->getLocation());
                });
                break;
            default:
                throw new \InvalidArgumentException("Unknown sortation: $sortation");
        }

        return $courses;
    }
}
