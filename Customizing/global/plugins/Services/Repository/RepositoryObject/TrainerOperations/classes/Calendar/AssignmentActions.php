<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\Calendar;

use CaT\Plugins\TrainerOperations\Aggregations\IliasRepository;

/**
 * Assign tutors to courses and sessions
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class AssignmentActions
{
    /**
     * @var IliasRepository
     */
    protected $il_repo;

    public function __construct(
        IliasRepository $il_repo
    ) {
        $this->il_repo = $il_repo;
    }

    public function assignTutorsToSession(int $session_ref_id, array $tutor_ids)
    {
        $session = $this->il_repo->getInstanceByRefId($session_ref_id);
        $session->setTutorSource(\ilObjSession::TUTOR_CFG_FROMCOURSE);
        $session->setAssignedTutors($tutor_ids);
        $session->update();
    }

    public function assignManualTutorsToSession(
        int $session_ref_id,
        string $tutor_name,
        string $tutor_mail,
        string $tutor_phone
    ) {
        $session = $this->il_repo->getInstanceByRefId($session_ref_id);
        $session->setTutorSource(\ilObjSession::TUTOR_CFG_MANUALLY);
        $session->setName($tutor_name);
        $session->setEmail($tutor_mail);
        $session->setPhone($tutor_phone);
        $session->update();
    }

    public function assignTutorToCourse(int $crs_ref_id, int $tutor_id)
    {
        \ilSessionAppEventListener::preventExecution(true);

        $crs = $this->il_repo->getInstanceByRefId($crs_ref_id);
        if (!$crs instanceof \ilObjCourse) {
            throw new Exception("Ref-id is not for a course", 1);
        }

        if ($tutor_id === 0) {
            return;
        }
        $mob = $crs->getMembersObject();
        if ($mob->isAssigned($tutor_id)) {
            $roles = $mob->getAssignedRoles($tutor_id);
            $roles[] = $crs->getDefaultTutorRole();
            $mob->updateRoleAssignments($tutor_id, $roles);
        } else {
            $mob->add($tutor_id, IL_CRS_TUTOR);
        }
    }
}
