<?php

namespace CaT\Plugins\UserBookings\UserBooking;

require_once("Services/Component/classes/class.ilPluginAdmin.php");

use CaT\Plugins\UserBookings as Root;
use ILIAS\TMS\ReportUtilities\TreeObjectDiscovery;

/**
 * Interface to get all booked trainings by user
 */
class ilDB implements DB
{
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
     * @var \ilAccessHandler
     */
    protected $access;

    /**
     * @var Root\Settings\UserBookingSettings
     */
    protected $settings;

    /**
     * @var int
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
        \ilAccessHandler $access,
        Root\Settings\UserBookingsSettings $settings,
        \ilObjUserBookings $object,
        TreeObjectDiscovery $tree_obj_discovery
    ) {
        $this->db = $db;
        $this->tree = $tree;
        $this->rbacreview = $rbacreview;
        $this->helper = $helper;
        $this->access = $access;
        $this->settings = $settings;
        $this->object = $object;
        $this->tree_obj_discovery = $tree_obj_discovery;
    }
    /**
     * Get information about trainings user has booked
     *
     * @param int 	$user_id
     *
     * @return UserBooking[]
     */
    public function getBookedTrainingsFor($user_id)
    {
        assert('is_int($user_id)');
        $ret = array();

        if (\ilPluginAdmin::isPluginActive('xbkm') && \ilPluginAdmin::isPluginActive('xccl')) {
            require_once("Services/Membership/classes/class.ilParticipants.php");
            $crs_ids = \ilParticipants::_getMembershipByType($user_id, "crs", true);
            $waiting_crs_ids = $this->getCoursesWhereUserIsOnList($user_id);

            $crs_ids = $this->maybeConstrainToLocalCourses(
                array_unique(array_merge($crs_ids, $waiting_crs_ids))
            );

            require_once("Services/Object/classes/class.ilObjectFactory.php");
            foreach ($crs_ids as $crs_id) {
                $ref_id = $this->getRefId($crs_id);

                $crs = \ilObjectFactory::getInstanceByRefId($ref_id);

                if (!$this->userHasAccess($user_id, $ref_id, $crs_id)) {
                    continue;
                }

                if ($this->userHasLPState($user_id, $crs_id)) {
                    continue;
                }

                $xccl = $this->getCourseClassification($ref_id);

                $ret[] = $this->createBookedTrainings($crs, $xccl);
            }
        }

        return $ret;
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

        $query = "SELECT obj_id" . PHP_EOL
                . " FROM crs_waiting_list" . PHP_EOL
                . " WHERE usr_id = " . $this->db->quote($user_id, "integer");

        $res = $this->db->query($query);
        $ret = array();
        while ($row = $this->db->fetchAssoc($res)) {
            $ret[] = (int) $row["obj_id"];
        }

        return $ret;
    }

    /**
     * Check user has needed permissions to view the course
     *
     * @param int 	$user_id
     * @param int 	$crs_ref_id
     * @param int 	$crs_id
     *
     * @return bool
     */
    protected function userHasAccess($user_id, $crs_ref_id, $crs_id)
    {
        require_once("Modules/Course/classes/class.ilObjCourseAccess.php");
        $visible = $this->access->checkAccessOfUser($user_id, "visible", "", $crs_ref_id);
        $read = $this->access->checkAccessOfUser($user_id, "read", "", $crs_ref_id);

        $always_visible = false;
        $active = \ilObjCourseAccess::_isActivated($crs_id, $always_visible);

        if ($visible && ($read && $active || (bool) $always_visible)) {
            return true;
        }

        return false;
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
        return array_shift($ref_ids);
    }

    /**
     * Get all booking modalities below crs
     *
     * @param int 	$ref_id
     *
     * @return ilObjBookingModalities[]
     */
    protected function getBookingModalities($ref_id)
    {
        $ret = array();

        global $DIC;
        $objDefinition = $DIC["objDefinition"];

        $childs = $this->tree->getChilds($ref_id);
        foreach ($childs as $child) {
            $type = $child["type"];
            if ($type == "xbkm") {
                $ret[] = \ilObjectFactory::getInstanceByRefId($child["child"]);
            }

            if ($objDefinition->isContainer($type)) {
                $ret2 = $this->getBookingModalities($child["child"]);
                $ret = array_merge($ret, $ret2);
            }
        }

        return $ret;
    }

    /**
     * Get first course classification of course
     *
     * @param int 	$ref_id
     *
     * @return ilObjCourseClassification
     */
    protected function getCourseClassification($ref_id)
    {
        global $DIC;
        $objDefinition = $DIC["objDefinition"];

        $childs = $this->tree->getChilds($ref_id);

        foreach ($childs as $child) {
            $type = $child["type"];
            if ($type == "xccl") {
                return \ilObjectFactory::getInstanceByRefId($child["child"]);
            }

            if ($objDefinition->isContainer($type)) {
                $cc = $this->getCourseClassification($child["child"]);
                if (!is_null($cc)) {
                    return $cc;
                }
            }
        }

        return null;
    }


    /**
     * Creates object of booked trainings
     *
     * @param ilObjCourse 	$crs
     * @param ilObjBookingModalities[] 	$xbkms
     * @param ilObjCourseClassification 	$xccl
     *
     * @return UserBooking
     */
    protected function createBookedTrainings($crs, $xccl)
    {
        $start_date = $crs->getCourseStart();
        $end_date = $crs->getCourseEnd();
        $title = $crs->getTitle();

        list($venue_id, $city, $address) = $this->helper->getVenueInfos($crs->getId());
        list($type_id, $type, $target_group_ids, $target_group, $goals, $topic_ids, $topics) = $this->helper->getCourseClassificationValues($xccl);
        list($provider_id) = $this->helper->getProviderInfos($crs->getId());

        return new UserBooking((int) $crs->getRefId(), $title, $type, $start_date, $target_group, $goals, $topics, $end_date, $city, $address, "KOSTEN");
    }

    /**
     * Checks user as any lp status on course
     *
     * @param int 	$usr_id
     * @param int 	$crs_id
     *
     * @return bool
     */
    protected function userHasLPState($usr_id, $crs_id)
    {
        require_once("Services/Tracking/classes/class.ilLPStatus.php");
        return \ilLPStatus::_lookupStatus($crs_id, $usr_id) > \ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
    }

    protected function getLocalTrainingsFilterStatement(string $crs_id_column_title) : string
    {
        if (!$this->settings->getLocalEvaluation()) {
            return '	TRUE	';
        }
        $relevant_crs_ids = $this->relevantCrsIds();
        return count($relevant_crs_ids) > 0 ?
            $this->db->in($column_title, $relevant_crs_ids, false, 'integer') :
            '	FALSE	';
    }

    protected function maybeConstrainToLocalCourses(array $crs_ids) : array
    {
        if (!$this->settings->getLocalEvaluation()) {
            return $crs_ids;
        }
        return array_intersect($crs_ids, $this->relevantCrsIds());
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
