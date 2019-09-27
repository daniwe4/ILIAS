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
        $members = $this->getMembersByPositionAndOrgunits(
            $orgus,
            array_map(
                function (\ilOrgUnitPosition $c) {
                    return (int) $c->getId();
                },
                $positions
            ),
            $superior_user_id
        );
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

    protected function getMembersByPositionAndOrgunits(
        array $orgus,
        array $positions_ids,
        int $superior_user_id
    ) : array {
        return $this->getTMSPositionHelper()->getUserIdUnderAuthorityOfUserByPositionsAndOrgus(
            $superior_user_id,
            $orgus,
            $positions_ids,
            true
        );
    }

    protected function getPositionWithPermissionToBook() : array
    {
        $search = $this->findSearchObject();
        if (is_null($search)) {
            return [];
        }

        return $search->getPositionWithBookPermission();
    }

    protected function findSearchObject()
    {
        $ref_id = $this->getTMSSession()->getCurrentSearch();
        if (is_null($ref_id)) {
            return null;
        }

        return \ilObjectFactory::getInstanceByRefId($ref_id);
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

    abstract protected function getAccess() : \ilAccess;

    protected function getTMSSession() : \TMSSession
    {
        return new \TMSSession();
    }
}
