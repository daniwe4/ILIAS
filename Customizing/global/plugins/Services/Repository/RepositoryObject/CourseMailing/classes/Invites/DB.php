<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseMailing\Invites;

interface DB
{
    /**
     * @return Invite[]
     */
    public function getInvitedUserFor(
        int $obj_id,
        string $order_field,
        string $order_direction,
        $offset,
        $limit,
        array $udf_columns = []
    ) : array;
    public function countInvitedUserFor(int $obj_id) : int;
    public function addUser(int $usr_id, int $obj_id, int $added_by);
    public function deleteUser(int $id);
    public function setRejectedByUser(int $id, int $rejected_by);
    public function setInvitedByUser(int $id, int $invite_by);
    public function setInvitedBy(int $usr_id, int $obj_id, int $invite_by);
    public function createRejectHashFor(int $usr_id, int $obj_id) : string;
    public function rejectByHash(string $hash);
    public function isAdded(int $usr_id, int $obj_id, bool $check_deleted = false);
    public function getLoginById(int $id) : string;
    public function reactivateUser(int $usr_id, int $obj_id, int $added_by);
}
