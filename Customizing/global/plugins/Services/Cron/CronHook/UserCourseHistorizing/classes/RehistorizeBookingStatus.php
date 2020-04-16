<?php

namespace CaT\Plugins\UserCourseHistorizing;

/**
 * Set historized booking status for user with member role on historized course
 */
class RehistorizeBookingStatus
{
    public function __construct(\ilDBInterface $db, \ilRbacReview $rbacreview)
    {
        $this->db = $db;
        $this->rbacreview = $rbacreview;
    }

    public function run()
    {
        require_once("Services/Object/classes/class.ilObjectFactory.php");
        $crs_n_users = $this->getUserWithoutBookingStatus();

        foreach ($crs_n_users as $ref_id => $users) {
            $crs = \ilObjectFactory::getInstanceByRefId($ref_id);
            $member_role_id = $crs->getDefaultMemberRole();
            foreach ($users as $user_id) {
                if ($this->isMemberOfCourse($user_id, $member_role_id)) {
                    $this->updateBookingStatus($user_id, $crs->getId());
                }
            }
        }
    }

    /**
     * Get all user with no booking status
     *
     * @return int[]
     */
    protected function getUserWithoutBookingStatus()
    {
        $query = "SELECT hhd.crs_id, hhd.usr_id, oref.ref_id" . PHP_EOL
                . " FROM hhd_usrcrs hhd" . PHP_EOL
                . " JOIN object_reference oref ON oref.obj_id = hhd.crs_id" . PHP_EOL
                . " WHERE hhd.booking_status IS NULL";
        $res = $this->db->query($query);

        $ret = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $ret[$row["ref_id"]][] = (int) $row["usr_id"];
        }

        return $ret;
    }

    /**
     * Check user booking state must be updated
     *
     * @param int[]
     *
     * @return void
     */
    protected function isMemberOfCourse($user_id, $member_role_id)
    {
        return $this->rbacreview->isAssigned($user_id, $member_role_id);
    }

    /**
     * Set booking status to participant
     *
     * @param int 	$user_id
     * @param int 	$crs_id
     *
     * @return void
     */
    protected function updateBookingStatus($user_id, $crs_id)
    {
        $query = "UPDATE hhd_usrcrs SET booking_status = 'participant' WHERE crs_id = $crs_id AND usr_id = $user_id";
        $this->db->manipulate($query);
    }
}
