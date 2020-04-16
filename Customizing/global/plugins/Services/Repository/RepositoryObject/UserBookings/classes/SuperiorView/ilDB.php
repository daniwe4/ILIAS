<?php

declare(strict_types=1);

namespace CaT\Plugins\UserBookings\SuperiorView;

use CaT\Plugins\UserBookings as Root;
use ILIAS\TMS\ReportUtilities\TreeObjectDiscovery;

/**
 * Interface to get all booked trainings by user
 */
class ilDB implements DB
{
    protected static $sortations = [
        self::SORT_BY_NAME_DESC,
        self::SORT_BY_NAME_ASC,
        self::SORT_BY_TITLE_DESC,
        self::SORT_BY_TITLE_ASC,
        self::SORT_BY_PERIOD_DESC,
        self::SORT_BY_PERIOD_ASC
    ];

    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var \ilTree
     */
    protected $tree;

    /**
     * @var \ilRbacReview
     */
    protected $rbacreview;

    /**
     * @var Root\Helper
     */
    protected $helper;

    /**
     * @var Root\Settings\UserBookingSettings
     */
    protected $settings;

    /**
     * @var \ilObjUserBookings
     */
    protected $object;

    /**
     * @var TreeObjectDiscovery
     */
    protected $tree_obj_discovery;

    public function __construct(
        \ilDBInterface $db,
        \ilRbacReview $rbacreview,
        \ilTree $tree,
        Root\Helper $helper,
        Root\Settings\UserBookingsSettings $settings,
        \ilObjUserBookings $object,
        TreeObjectDiscovery $tree_obj_discovery
    ) {
        $this->db = $db;
        $this->tree = $tree;
        $this->rbacreview = $rbacreview;
        $this->helper = $helper;
        $this->settings = $settings;
        $this->object = $object;
        $this->tree_obj_discovery = $tree_obj_discovery;
    }

    /**
     * @inheritdoc
     */
    public function getBookedTrainingsFor(
        array $user_ids,
        string $sortation,
        int $limit = null,
        int $offset = null
    ) : array {
        assert(in_array($sortation, self::$sortations));
        $ret = [];

        if (!\ilPluginAdmin::isPluginActive('xbkm') && \ilPluginAdmin::isPluginActive('xccl')) {
            return $ret;
        }

        $query = "SELECT oref.ref_id, hstc.title, hstc.begin_date, hstc.end_date, hstu.usr_id AS uid,".PHP_EOL
            ." CONCAT(IF(ud.title IS NULL,'',CONCAT(ud.title,' ')),ud.firstname,' ', ud.lastname) AS fullname".PHP_EOL
            .$this->getCommonFromAndWherePart($user_ids).PHP_EOL
            .$this->getSortPart($sortation).PHP_EOL;

        if (!is_null($limit) && !is_null($offset)) {
            $query .= " LIMIT ".$limit." OFFSET ".$offset;
        }

        $res = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($res)) {
            $ret[] = $this->createBookedTrainings(
                (int)$row["ref_id"],
                (int)$row["uid"],
                $row["fullname"],
                (string)$row["title"],
                new \ilDate($row["begin_date"], IL_CAL_DATE)
            );
        }

        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function getBookedTrainingsCountFor(array $user_ids): int
    {
        if (!\ilPluginAdmin::isPluginActive('xbkm') && \ilPluginAdmin::isPluginActive('xccl')) {
            return 0;
        }

        $query = "SELECT count(oref.ref_id) AS cnt".PHP_EOL
            .$this->getCommonFromAndWherePart($user_ids);
        $res = $this->db->query($query);
        $row = $this->db->fetchAssoc($res);
        return (int)$row["cnt"];
    }

    protected function getCommonFromAndWherePart(array $user_ids) : string
    {
        return " FROM hhd_usrcrs hstu".PHP_EOL
            ." JOIN object_reference oref ON hstu.crs_id = oref.obj_id".PHP_EOL
            ." JOIN usr_data ud ON hstu.usr_id = ud.usr_id".PHP_EOL
            ." JOIN hhd_crs hstc ON hstc.crs_id = hstu.crs_id".PHP_EOL
            ." WHERE ".$this->db->in("hstu.booking_status", array('participant' , 'waiting'), false, "text").PHP_EOL
            ."     AND ".$this->db->in("hstu.usr_id", $user_ids, false, "integer").PHP_EOL
            ."     AND (hstu.participation_status IS NULL OR hstu.participation_status IN ('none', 'in_progress'))".PHP_EOL
            ."	AND ".$this->getLocalTrainingsFilterStatement('hstc.crs_id');
    }



    /**
     * Get all course where user is on waiting list
     *
     * @param int 	$user_id
     *
     * @return int[]
     */
    protected function getCoursesWhereUserIsOnList($user_id)
    {
        assert('is_int($user_id)');

        $query = "SELECT obj_id".PHP_EOL
            ." FROM crs_waiting_list".PHP_EOL
            ." WHERE usr_id = ".$this->db->quote($user_id, "integer")
            ."	AND ".$this->getLocalTrainingsFilterStatement('obj_id');
        $res = $this->db->query($query);
        $ret = array();
        while ($row = $this->db->fetchAssoc($res)) {
            $ret[] = (int)$row["obj_id"];
        }

        return $ret;
    }

    /**
     * Get initial ref_id of course
     *
     * @param int 	$crs_id
     *
     * @return int
     */
    protected function getRefId($crs_id)
    {
        $ref_ids = \ilObject::_getAllReferences($crs_id);
        sort($ref_ids);
        return (int)array_shift($ref_ids);
    }

    /**
     * Creates object of booked trainings
     */
    protected function createBookedTrainings(int $ref_id, int $user_id, string $fullname, string $title, \ilDate $begin_date): UserBooking
    {
        return new UserBooking($ref_id, $user_id, $fullname, $title, $begin_date);
    }

    /**
     * Sorts filtered bookable training according to user input
     *
     * @param 	BookableCourse[] &$bookable_trainings
     * @param	mixed	$sortation	one of the SORT_BY-consts
     *
     * @return BookableCourse[]
     */
    protected function sortBookableTrainings(&$bookable_trainings, $sortation)
    {
        uasort($bookable_trainings, $this->getSortingClosure($sortation));
    }

    /**
     * @param	mixed	$sortation	one of the SORT_BY-consts
     * @return Closure
     */
    protected function getSortPart($sortation)
    {
        switch ($sortation) {
            case self::SORT_BY_NAME_DESC:
                return " ORDER BY ud.firstname DESC";
            case self::SORT_BY_NAME_ASC:
                return " ORDER BY ud.firstname ASC";
            case self::SORT_BY_TITLE_DESC:
                return " ORDER BY title DESC";
            case self::SORT_BY_TITLE_ASC:
                return " ORDER BY title ASC";
            case self::SORT_BY_PERIOD_DESC:
                return " ORDER BY begin_date DESC";
            case self::SORT_BY_PERIOD_ASC:
                return " ORDER BY begin_date ASC";
            default:
                throw new \LogicException("Unknown sortation mode: $sortation");
        }
    }

    protected function getLocalTrainingsFilterStatement(string $crs_id_column_title) : string
    {
        if(!$this->settings->getLocalEvaluation()) {
            return '	TRUE	';
        }
        $relevant_crs_ids = $this->relevantCrsIds();
        return count($relevant_crs_ids) > 0 ?
            $this->db->in($crs_id_column_title,$relevant_crs_ids,false,'integer') :
            '	FALSE	';
    }

    protected function relevantCrsIds() : array
    {
        $parent = $this->tree_obj_discovery->getParentOfObjectOfType($this->object, 'cat');
        if ($parent === null) {
            $parent = $this->tree_obj_discovery->getParentOfObjectOfType($this->object, 'root');
        }
        return $this->tree_obj_discovery->getAllChildrenIdsByTypeOfObject($parent, 'crs');
    }
}
