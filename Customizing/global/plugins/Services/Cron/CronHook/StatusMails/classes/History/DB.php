<?php

declare(strict_types=1);

namespace CaT\Plugins\StatusMails\History;

/**
 * The DB to factor UserActivities.
 * @author Nils Haagen    <nils.haagen@concepts-and-training.de>
 */
interface DB
{

    /**
     * Get activites of users that occured between $start and $end.
     * If there is more than one activity for one user/course-relation,
     * only the latest activity will be returned.
     * @param int[] $usr_ids
     * @return    UserActivity[]
     */
    public function getActivitiesByTime(\DateTime $start, \DateTime $end, array $usr_ids) : array;

    /**
     * Get activites of users at courses that will start between $start and $end.
     * This will only return activities with type UserActivity::ACT_TYPE_BOOKED.
     * @param int[] $usr_ids
     * @return    UserActivity[]
     */
    public function getBookedByCourseTime(\DateTime $start, \DateTime $end, array $usr_ids) : array;
}
