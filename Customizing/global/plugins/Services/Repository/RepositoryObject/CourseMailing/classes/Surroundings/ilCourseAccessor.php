<?php
namespace CaT\Plugins\CourseMailing\Surroundings;

/**
 * The plugin needs information about its environment.
 * This is to get to the course above the repository object.
 */
class ilCourseAccessor
{

    /**
     * @var int
     */
    protected $obj_ref_id;

    public function __construct($obj_ref_id)
    {
        assert('is_int($obj_ref_id)');
        $this->obj_ref_id = $obj_ref_id;
    }

    /**
     * get some information about the parent course
     *
     * @return array<string,mixed> | false
     */
    public function getParentCourseInfo()
    {
        global $tree;
        foreach ($tree->getPathFull($this->obj_ref_id) as $hop) {
            if ($hop['type'] === 'crs') {
                return $hop;
            }
        }
        return false;
    }

    /**
     * Get a list of local roles available at the course.
     *
     * @return array<string,string> | [] 	role_id->role_title
     */
    public function getLocalRoles()
    {
        global $rbacreview;
        $ret = array();

        $crs_info = $this->getParentCourseInfo();
        if ($crs_info === false) {
            return $ret;
        }
        foreach ($rbacreview->getLocalRoles($crs_info['ref_id']) as $role_id) {
            $ret[$role_id] = $this->getUnifiedRoleTitle(
                (string) $role_id,
                (string) $crs_info['ref_id']
            );
        }
        return $ret;
    }

    /**
     * Strip crsRefId from role title for std. ilias roles.
     *
     * @param string 	$role_id
     * @param string 	$crs_ref_id
     * @return string
     */
    private function getUnifiedRoleTitle($role_id, $crs_ref_id)
    {
        assert('is_string($role_id)');
        assert('is_string($crs_ref_id)');

        $role_title = \ilObject::_lookupTitle($role_id);

        $crs_ref_id = '_' . $crs_ref_id;
        if (substr($role_title, 0, 3) === 'il_') {
            $role_title = substr($role_title, 0, strrpos($role_title, $crs_ref_id));
        }
        return $role_title;
    }

    /**
     * Get all members of parent course
     *
     * @return ilObjUser[]
     */
    public function getMembersOfParentCourse()
    {
        $ret = array();
        $crs_info = $this->getParentCourseInfo();
        if ($crs_info === false) {
            return $ret;
        }

        $crs_obj_id = $crs_info['obj_id'];
        require_once("Services/Membership/classes/class.ilParticipants.php");
        $part = \ilParticipants::getInstanceByObjId($crs_obj_id);
        $user_ids = $part->getParticipants();

        foreach ($user_ids as $id) {
            $ret[] = new \ilObjUser($id);
        }
        return $ret;
    }

    /**
     * get all member-ids with a certain role
     *
     * @param int 	$role_id
     * @return int[]
     */
    public function getMembersWithRole($role_id)
    {
        $ret = array();
        $crs_info = $this->getParentCourseInfo();
        if ($crs_info === false) {
            return $ret;
        }

        global $rbacreview;
        $crs_obj_id = $crs_info['obj_id'];
        /*
        $part = \ilParticipants::getInstanceByObjId($crs_obj_id);
        the above line will run into caching-troubles when adding
        a course-member and subsequently triggering mail via
        addParticipant-event.
        Creating ilCourseParticipants anew will re-read from DB.
        */
        require_once("Modules/Course/classes/class.ilCourseParticipants.php");
        $part = new \ilCourseParticipants($crs_obj_id);
        $user_ids = $part->getParticipants();
        foreach ($user_ids as $usr_id) {
            if ($rbacreview->isAssigned($usr_id, $role_id)) {
                $ret[] = (int) $usr_id;
            }
        }
        return $ret;
    }

    /**
     * get roles for a certain member
     *
     * @param int 	$usr_id
     * @return int[]
     */
    public function getRolesForMember(int $usr_id)
    {
        $ret = array();
        $crs_info = $this->getParentCourseInfo();
        if ($crs_info === false) {
            return $ret;
        }

        $crs_obj_id = $crs_info['obj_id'];
        /*
        $part = \ilParticipants::getInstanceByObjId($crs_obj_id);
        the above line will run into caching-troubles when adding
        a course-member and subsequently triggering mail via
        addParticipant-event.
        Creating ilCourseParticipants anew will re-read from DB.
        */
        $roles = $this->getAssignedRolesFor($usr_id, $crs_obj_id);
        foreach ($roles as $role_id) {
            $ret[] = $this->getUnifiedRoleTitle(
                (string) $role_id,
                (string) $crs_info['ref_id']
            );
        }
        return $ret;
    }

    public function getRoleIdsForMember(int $usr_id) : array
    {
        $ret = array();
        $crs_info = $this->getParentCourseInfo();
        if ($crs_info === false) {
            return $ret;
        }

        $crs_obj_id = $crs_info['obj_id'];
        return $this->getAssignedRolesFor($usr_id, $crs_obj_id);
    }

    protected function getAssignedRolesFor(int $usr_id, int $crs_obj_id) : array
    {
        $part = $this->getParticipantsInstance($crs_obj_id);
        return $part->getAssignedRoles($usr_id);
    }

    protected function getParticipantsInstance(int $crs_obj_id) : \ilCourseParticipants
    {
        return new \ilCourseParticipants($crs_obj_id);
    }

    public function getRoleSortingFor($usr_id)
    {
        $crs_info = $this->getParentCourseInfo();
        if ($crs_info === false) {
            return "";
        }

        $crs_obj_id = $crs_info['obj_id'];
        require_once("Services/Membership/classes/class.ilParticipants.php");
        $part = \ilParticipants::getInstanceByObjId($crs_obj_id);
        return $part->setRoleOrderPosition($usr_id);
    }

    public function isOnWaitingList(int $usr_id)
    {
        $crs_info = $this->getParentCourseInfo();
        if ($crs_info === false) {
            return false;
        }
        $crs_obj_id = $crs_info['obj_id'];
        return \ilWaitingList::_isOnList($usr_id, $crs_obj_id);
    }
}
