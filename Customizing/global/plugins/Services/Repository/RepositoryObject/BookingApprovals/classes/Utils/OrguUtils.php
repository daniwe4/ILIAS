<?php

declare(strict_types=1);

namespace CaT\Plugins\BookingApprovals\Utils;

require_once "Modules/OrgUnit/classes/Positions/UserAssignment/class.ilOrgUnitUserAssignmentQueries.php";

/**
 * Class that handles operation on org units.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class OrguUtils
{
    /**
     * @var \TMSPositionHelper
     */
    protected $postion_helper;

    public function __construct(\TMSPositionHelper $position_helper)
    {
        $this->position_helper = $position_helper;
    }

    /**
     * Get all orgus the user is assigned to.
     * Get all users with $position from these orgus.
     * If none is found, aquire next higher OrgU and try again.
     */
    public function getNextHigherUsersWithPositionForUser(int $position, int $usr_id) : array
    {
        return $this->position_helper->getNextHigherUsersWithPositionForUser($position, $usr_id);
    }

    public function getPositionTitleById(int $id) : string
    {
        return $this->position_helper->getPositionTitleById($id);
    }
}
