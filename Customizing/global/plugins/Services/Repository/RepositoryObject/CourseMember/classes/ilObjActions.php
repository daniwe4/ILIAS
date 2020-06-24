<?php

namespace CaT\Plugins\CourseMember;

/**
 * Communication class between front- and backend.
 * E.g. GUI only use this class to get information from ILIAS DB.
 */
class ilObjActions
{
    /**
     * @var \ilObjCourseMember
     */
    protected $object;

    /**
     * @var Members\DB
     */
    protected $members_db;

    /**
     * @var LPSettings\LPManager
     */
    protected $lp_manager;

    public function __construct(
        \ilObjCourseMember $object,
        Members\DB $members_db,
        LPSettings\LPManager $lp_manager,
        \ilAppEventHandler $app_event_handler
    ) {
        $this->object = $object;
        $this->members_db = $members_db;
        $this->lp_manager = $lp_manager;
        $this->app_event_handler = $app_event_handler;
    }

    /**
     * Get the current object
     *
     * @return \ilObjCourseMember
     */
    public function getObject()
    {
        if ($this->object === null) {
            throw new \Exception("No object was set");
        }

        return $this->object;
    }

    /**
     * Creates a new member object and entry or updates existing
     *
     * @param Member
     *
     * @return void
     */
    public function upsert(Members\Member $member)
    {
        return $this->members_db->upsert($member);
    }

    /**
     * Get all members with plugin lp state saved
     *
     * @return Member[]
     */
    public function getMemberWithSavedLPSatus()
    {
        $this->synchronizeMembersWithCourse();
        $parent = $this->getObject()->getParentCourse();
        return $this->members_db->select((int) $parent->getId());
    }

    /**
     * Get members from course; if list is not finalized,
     * add users to list and delete those that are no longer
     * a member of the course.
     *
     * @return void
     */
    public function synchronizeMembersWithCourse()
    {
        if ($this->getObject()->getSettings()->getClosed()) {
            return;
        }
        $course_members = $this->getParentCourseMemberIds();
        $existing = $this->getExistingMemberIds();
        $crs_obj = $this->getObject()->getParentCourse();
        $crs_id = (int) $crs_obj->getId();

        foreach ($course_members as $cm_id) {
            if (!in_array($cm_id, $existing)) {
                //add to list
                $member = $this->getMemberWith(
                    (int) $cm_id,
                    $crs_id,
                    null,
                    null,
                    \ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM,
                    $this->getObject()->getSettings()->getCredits(),
                    null
                );
                $this->members_db->upsert($member);
            }
        }
        foreach ($existing as $e_id) {
            if (!in_array($e_id, $course_members)) {
                //delete from list
                $this->members_db->deleteForUserAndCourse((int) $e_id, $crs_id);
            }
        }
    }

    /**
     * Get user-ids of parent course members.
     * @return int[]
     */
    protected function getParentCourseMemberIds()
    {
        $crs_obj = $this->getObject()->getParentCourse();
        $crs_member_ids = $crs_obj->getMembersObject()->getMembers();
        return $crs_member_ids;
    }

    /**
     * Get user-ids of current list
     * @return int[]
     */
    protected function getExistingMemberIds()
    {
        $crs_obj = $this->getObject()->getParentCourse();
        $crs_id = (int) $crs_obj->getId();
        $existing = array_map(
            function ($member) {
                return $member->getUserId();
            },
            $this->members_db->select($crs_id)
        );
        return $existing;
    }

    /**
     * Get an member object with
     *
     * @param int 	$user_id
     * @param int 	$crs_id
     * @param string | null 	$lp_value
     * @param int | null 	$ilias_lp
     * @param int | null 	$credits
     * @param int | null 	$idd_learning_time
     * @return Member[]
     */
    public function getMemberWith(
        int $user_id,
        int $crs_id,
        ?int $lp_id,
        ?string $lp_value,
        ?int $ilias_lp,
        ?float $credits,
        ?int $idd_learning_time
    ) {
        return $this->members_db->getMember($user_id, $crs_id, $lp_id, $lp_value, $ilias_lp, $credits, $idd_learning_time);
    }

    /**
     * Refresh LP sate for all known members
     *
     * @return null
     */
    public function refreshLP()
    {
        $this->lp_manager->refresh((int) $this->getObject()->getId());
    }

    /**
     * Throws event if course member list is closed
     *
     * @param int 	$crs_id
     * @param int 	$user_id
     * @param int 	$minutes
     *
     * @return void
     */
    public function throwEvent(
        int $crs_id,
        int $user_id,
        int $minutes,
        ?string $lp_value
    ) {
        $payload = [
            "crs_id" => $crs_id,
            "usr_id" => $user_id,
            "minutes" => $minutes,
            "lp_value" => $lp_value
        ];

        $this->app_event_handler->raise("Plugin/CourseMember", "closeList", $payload);
    }


    /**
     * Throws (general) event if course member list is closed
     *
     * @return void
     */
    public function throwFinalizedEvent()
    {
        $parent_course = $this->getObject()->getParentCourse();
        $payload = [
            "crs_ref_id" => (int) $parent_course->getRefId(),
            "crs_obj_id" => (int) $parent_course->getId()
        ];
        $event = Mailing\LPStatusOccasion::EVENT_MEMBERLIST_FINALIZED;
        $this->app_event_handler->raise("Modules/Course", $event, $payload);
    }

    /**
     * Export the list and return path to file.
     *
     * @return string
     */
    public function exportSignatureList()
    {
        $exporter = $this->getObject()->getSinatureListExporter();
        $exporter->writeOutput();
        return $exporter->getFilePath();
    }

    /**
     * Get the idd learning time for single user
     *
     * @param int 	$user_id
     *
     * @return int
     */
    public function getMinutesFor(int $user_id)
    {
        return $this->members_db->getMinutesFor((int) $this->getObject()->getParentCourse()->getId(), $user_id);
    }
}
