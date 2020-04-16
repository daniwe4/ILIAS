<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingApprovals\Utils;

/**
 * Some utils to get to course-infos / booking modalities
 */
class CourseUtils
{
    /**
     * @var	\ilTree
     */
    protected $g_tree;

    /**
     * @var	\ilObjectDefinition
     */
    protected $g_objDefinition;

    /**
     * @var	IliasWrapper
     */
    protected $ilias_wrapper;

    /**
     * @var	array <int, CourseInfoImpl>
     */
    protected $crs_info_cache = [];


    public function __construct(
        \ilTree $tree,
        \ilObjectDefinition $obj_definition,
        IliasWrapper $ilias_wrapper
    ) {
        $this->g_tree = $tree;
        $this->g_objDefinition = $obj_definition;
        $this->ilias_wrapper = $ilias_wrapper;
    }

    public function getSelfBookingModalities(int $crs_ref_id)
    {
        $used_booking = null;
        $booking_mods = $this->getAllChildsOfByType($crs_ref_id, 'xbkm');
        foreach ($booking_mods as $key => $booking_mod) {
            if ($booking_mod->isSelfBooking()) {
                $used_booking = $booking_mod;
                break;
            }
        }

        return $used_booking;
    }

    /**
     * Get the approval roles for the course from booking modalities.
     * Mind the order!
     * @return int[]
     */
    public function getApprovalRolesForSelfBooking(int $crs_ref_id) : array
    {
        $used_booking = $this->getSelfBookingModalities($crs_ref_id);
        if (is_null($used_booking)) {
            return array();
        }

        return $used_booking->getApproversPositions();
    }

    public function getSuperiorBookingModalities(int $crs_ref_id)
    {
        $used_booking = null;
        $booking_mods = $this->getAllChildsOfByType($crs_ref_id, 'xbkm');
        foreach ($booking_mods as $key => $booking_mod) {
            if ($booking_mod->isSuperiorBooking()) {
                $used_booking = $booking_mod;
                break;
            }
        }

        return $used_booking;
    }

    /**
     * Get the approval roles for the course from booking modalities.
     * Mind the order!
     * @return int[]
     */
    public function getApprovalRolesForSuperiorBooking(int $crs_ref_id) : array
    {
        $used_booking = $this->getSuperiorBookingModalities($crs_ref_id);
        if (is_null($used_booking)) {
            return array();
        }

        return $used_booking->getApproversPositions();
    }

    /**
     * Get CourseInfos via Ente.
     * @return CourseInfoImpl[]
     */
    public function getCourseInformation(int $crs_ref_id) : array
    {
        if (!array_key_exists($crs_ref_id, $this->crs_info_cache)) {
            $info = new CourseInformation($crs_ref_id);
            $this->crs_info_cache[$crs_ref_id] = $info->get();
        }
        return $this->crs_info_cache[$crs_ref_id];
    }

    /**
     * Get first child by type recursive
     *
     * @return Object[]|null 	of search type
     */
    protected function getAllChildsOfByType(int $ref_id, string $search_type)
    {
        $ret = [];
        $children = $this->g_tree->getSubTree(
            $this->g_tree->getNodeData($ref_id),
            false,
            $search_type
        );

        foreach ($children as $child) {
            $ret[] = $this->ilias_wrapper->getInstanceByRefId((int) $child);
        }
        return $ret;
    }

    public function getCourseAdmins(int $crs_ref_id) : array
    {
        $crs = $this->ilias_wrapper->getInstanceByRefId($crs_ref_id);
        return $crs->getMembersObject()->getAdmins();
    }

    public function getTitleForRefId(int $crs_ref_id) : string
    {
        return $this->ilias_wrapper->lookupTitleByRef($crs_ref_id);
    }

    public function lookupFullname(int $usr_id) : string
    {
        return $this->ilias_wrapper->lookupFullname($usr_id);
    }
}
