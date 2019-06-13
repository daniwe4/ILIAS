<?php

declare(strict_types=1);

namespace ILIAS\TMS;

trait MyUsersHelper
{
    /**
     * Get user ids and fullname of user, where current ilUser is allowed to book for
     *
     * @param int 	$superior_user_id
     *
     * @return string[]
     */
    public function getUserWhereCurrentCanBookFor($superior_user_id) : array
    {
        // ATTENTION: This is not the proper way to do caching in general. If you are
        // tempted to do the same: don't. Talk to Richard instead.
        static $cache = [];

        if (isset($cache[$superior_user_id])) {
            return $cache[$superior_user_id];
        }

        $positions = $this->getPositionWithPermissionToBook();
        $orgus = $this->getOrgusByPositionAndUser($positions, $superior_user_id);

        $ret = array();
        $members = $this->getMembersByOrgus($orgus, $superior_user_id);
        $current = array((string) $superior_user_id);
        $members = array_unique(array_merge($current, $members));

        foreach ($members as $user_id) {
            $name_infos = \ilObjUser::_lookupName($user_id);
            $ret[$user_id] = $name_infos["lastname"] . ", " . $name_infos["firstname"];
        }

        uasort($ret, function ($a, $b) {
            return strcmp($a, $b);
        });

        $cache[$superior_user_id] = $ret;

        return $ret;
    }

    /**
     * Get user ids of of user, where current ilUser is allowed to see bookings
     *
     * @param int 	$superior_user_id
     *
     * @return int[]
     */
    public function getUsersWhereCurrentCanViewBookings($superior_user_id)
    {
        require_once("Services/User/classes/class.ilObjUser.php");
        $ret = array();
        $members = $this->getMembersUserHasAuthorities($superior_user_id);
        $members = array_filter(
            $members,
            function ($user_id) use ($superior_user_id) {
                return $user_id != $superior_user_id;
            }
        );

        foreach ($members as $user_id) {
            $name_infos = \ilObjUser::_lookupName($user_id);
            $ret[$user_id] = $name_infos["lastname"] . ", " . $name_infos["firstname"];
        }

        uasort($ret, function ($a, $b) {
            return strcmp($a, $b);
        });

        return $ret;
    }

    /**
     * Get all user ids where user has authorities
     *
     * @param int 	§user_id
     *
     * @return int[]
     */
    protected function getMembersByOrgus(array $orgus, int $superior_user_id) : array
    {
        return $this->getTMSPositionHelper()->getUserIdUnderAuthorityOfUserByPositionsAndOrgus($superior_user_id, $orgus, true);
    }

    protected function getPositionWithPermissionToBook() : array
    {
        $search = $this->findSearchObject();
        if (is_null($search)) {
            return [];
        }

        return $search->getPositionWithBookPermission();
    }

    protected function getPositionWithPermissionToViewBookings() : array
    {
        $search = $this->findSearchObject();

        if (is_null($search)) {
            return [];
        }

        return $search->getPositionWithViewBookingPermission();
    }

    protected function findSearchObject()
    {
        if (!\ilPluginAdmin::isPluginActive("xtrs")) {
            return null;
        }

        $xtrs_objects = \ilObject::_getObjectsDataForType("xtrs", true);

        if (count($xtrs_objects) == 0) {
            return null;
        }

        uasort($xtrs_objects, function ($a, $b) {
            return strcmp($a["id"], $b["id"]);
        });

        $access = $this->getAccess();
        foreach ($xtrs_objects as $value) {
            foreach (\ilObject::_getAllReferences($value["id"]) as $ref_id) {
                if (
                    $access->checkAccess("visible", "", $ref_id) &&
                    $access->checkAccess("read", "", $ref_id) &&
                    $access->checkAccess("use_search", "", $ref_id)
                ) {
                    return \ilObjectFactory::getInstanceByRefId($ref_id);
                }
            }
        }

        return null;
    }

    /**
     * Get all user ids where user has authorities
     *
     * @param int 	§user_id
     *
     * @return int[]
     */
    protected function getMembersUserHasAuthorities($user_id)
    {
        require_once("Services/TMS/Positions/TMSPositionHelper.php");
        require_once("Modules/OrgUnit/classes/Positions/UserAssignment/class.ilOrgUnitUserAssignmentQueries.php");
        $tms_pos_helper = new \TMSPositionHelper(\ilOrgUnitUserAssignmentQueries::getInstance());
        return $tms_pos_helper->getUserIdWhereUserHasAuhtority($user_id);
    }

    protected function getOrgusByPositionAndUser(array $positions, int $superior_user_id) : array
    {
        return array_map("intval", $this->getTMSPositionHelper()->getOrgUnitByPositions($positions, $superior_user_id));
    }

    protected function getTMSPositionHelper() : \TMSPositionHelper
    {
        if (is_null($this->pos_helper)) {
            $this->pos_helper = new \TMSPositionHelper(\ilOrgUnitUserAssignmentQueries::getInstance());
        }

        return $this->pos_helper;
    }

    abstract protected function getAccess() : ilAccess;
}
