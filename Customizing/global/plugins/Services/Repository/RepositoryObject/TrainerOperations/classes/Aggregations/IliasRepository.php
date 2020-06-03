<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\Aggregations;

/**
 * Access the ILIAS Repository
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class IliasRepository
{
    /**
     * @var \ilTree
     */
    protected $tree;
    /**
     * @var \ilObjectDefinition
     */
    protected $object_defnition;

    public function __construct(
        \ilTree $tree,
        \ilObjectDefinition $object_defnition,
        \ilDBInterface $g_db

    ) {
        $this->tree = $tree;
        $this->object_defnition = $object_defnition;
        $this->g_db = $g_db;
    }

    public function getParentId(int $ref_id) : int
    {
        return (int) $this->tree->getParentId($ref_id);
    }


    protected $c_childrenof = [];
    public function getAllChildrenOfByType(int $ref_id, string $search_type) : array
    {
        $k = $ref_id . '-' . $search_type;
        if (array_key_exists($k, $this->c_childrenof)) {
            return $this->c_childrenof[$k];
        }
        $childs = $this->tree->getSubTree(
            $this->tree->getNodeData($ref_id),
            true,
            $search_type
        );

        $this->c_childrenof[$k] = $childs;
        return $childs;
    }


    public function getInstanceByRefId(int $ref_id) : \ilObject
    {
        return \ilObjectFactory::getInstanceByRefId($ref_id);
    }

    /**
     * @return ilObjUser[]
     */
    public function getTutorsAtCourse(int $crs_ref_id) : array
    {
        $crs = $this->getInstanceByRefId($crs_ref_id);
        if (!$crs instanceof \ilObjCourse) {
            throw new Exception("Ref-id is not for a course", 1);
        }

        $members = $crs->getMembersObject();
        $ret = array_map(
            function ($usr_id) {
                return \ilObjectFactory::getInstanceByObjId($usr_id, false);
            },
            array_filter(
                $members->getTutors(),
                function ($usr_id) {
                    return (int) $usr_id !== 0;
                }
            )
        );
        return $ret;
    }

    public function isCourseOnline(int $crs_obj_id) : bool
    {
        $dummy = null;
        return \ilObjCourseAccess::_isActivated($crs_obj_id, $dummy, false);
    }

    public function filterCourseIdsByTimeRange(
        array $course_obj_ids,
        \DateTime $start,
        \DateTime $end
    ) : array {
        $query = "SELECT obj_id FROM crs_settings WHERE" . PHP_EOL
            . $this->g_db->in("obj_id", $course_obj_ids, false, "integer") . PHP_EOL
            . "AND ((" . PHP_EOL
            . "	period_start <= " . $this->g_db->quote($end->format("Y-m-d 23:59:59"), "text")
            . " 	AND "
            . "	period_end >= " . $this->g_db->quote($start->format("Y-m-d 00:00:00"), "text")
            . "))";

        $res = $this->g_db->query($query);
        $course_ids_in_range = [];
        while ($row = $this->g_db->fetchAssoc($res)) {
            $course_ids_in_range[] = (int) $row['obj_id'];
        }

        return $course_ids_in_range;
    }
}
