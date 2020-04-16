<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseCreation;

/**
 * Communication class between front- and backend.
 * E.g. GUI only use this class to get information from ILIAS DB.
 */
class ilActions
{
    /**
     * @var RequestDB
     */
    protected $request_db;

    /**
     * @var CreationSettings\DB
     */
    protected $settings_db;

    public function __construct(
        RequestDB $request_db,
        CreationSettings\DB $settings_db
    ) {
        $this->request_db = $request_db;
        $this->settings_db = $settings_db;
    }

    /**
     * Get requests for a user that are not yet processed.
     * @return ILIAS\TMS\CourseCreation\Request[]
     */
    public function getDueRequestsOf(\ilObjUser $user) : array
    {
        return $this->request_db->getDueRequestsOf((int) $user->getId());
    }

    /**
     * Get all open requests
     * @return ILIAS\TMS\CourseCreation\Request[]
     */
    public function getOpenRequests(int $offset = null, int $limit = null) : array
    {
        return $this->request_db->getOpenRequests($offset, $limit);
    }

    /**
     * Counts all open requests
     */
    public function getCountOpenRequests() : int
    {
        return $this->request_db->getCountOpenRequests();
    }

    /**
     * Get all not successful finished requests
     * @return ILIAS\TMS\CourseCreation\Request[]
     */
    public function getNotSuccessfulRequests(int $offset = null, int $limit = null) : array
    {
        return $this->request_db->getNotSuccessfulRequests($offset, $limit);
    }

    /**
     * Count all not usccessful finished requests
     *
     * @return int
     */
    public function getCountNotSuccessfulRequests() : int
    {
        return $this->request_db->getCountNotSuccessfulRequests($offset, $limit);
    }

    /**
     * Get all successfull finished requests
     * @return ILIAS\TMS\CourseCreation\Request[]
     */
    public function getFinishedRequests(int $offset = null, int $limit = null) : array
    {
        return $this->request_db->getFinishedRequests($offset, $limit);
    }

    /**
     * Count all successfull finished requests sorted bei finished ts
     */
    public function getCountFinishedRequests() : int
    {
        return $this->request_db->getCountFinishedRequests($offset, $limit);
    }

    /**
     * Set a request as finished
     */
    public function setRequestFinished(int $request_id, \DateTime $finished_ts)
    {
        $this->request_db->setRequestFinished($request_id, $finished_ts);
    }

    /**
     * Get all role ids who are allowed to create multiply requests
     * @return int[]
     */
    public function getRoleIdsForMultiplyRequestCreation() : array
    {
        return $this->settings_db->select();
    }

    /**
     * Save roles who are allowed to create multiply requests
     * @param int[]
     */
    public function saveRoleIdsForMultiplyRequestCreation(array $role_ids)
    {
        return $this->settings_db->save($role_ids);
    }
}
