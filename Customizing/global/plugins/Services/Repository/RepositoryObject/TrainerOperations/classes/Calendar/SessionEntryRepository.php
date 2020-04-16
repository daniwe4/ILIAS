<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\Calendar;

use CaT\Plugins\TrainerOperations\Aggregations\IliasRepository;

/**
 * Get SessionEntries for Calendar
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class SessionEntryRepository
{
    /**
     * @var IliasRepository
     */
    protected $repo;
    /**
     * @var int
     */
    protected $base_ref_id;
    /**
     * @var int[]
     */
    protected $session_ref_ids = array();
    /**
     * @var array <int, ilObjSession>
     */
    protected $cache_session_objs = array();
    /**
     * @var array <int, array>	session_ref_id => [crs_ref_id, crs_title]
     */
    protected $crs_info_lookup = [];
    /**
     * @var array <int, array>	session_ref_id => [bool tutor_from_course, int[] tutor_ids, string tutor_name]
     */
    protected $session_lookup = [];


    public function __construct(IliasRepository $repo_accessor)
    {
        $this->repo = $repo_accessor;
    }

    /**
     * Configure the repository to get objects below the given ref_id.
     */
    public function withBaseRefId(
        int $ref_id,
        \DateTime $start,
        \DateTime $end
    ) : SessionEntryRepository {
        $clone = clone $this;
        $clone->base_ref_id = $ref_id;
        $clone->session_ref_ids = $clone->getRelevantSessionsIds($ref_id, $start, $end);

        $clone->presortSessions();

        return $clone;
    }

    protected $no_tutor = [];
    protected $other_tutor = [];
    protected $assigned_tutor = [];
    protected function presortSessions()
    {
        foreach ($this->getSessionRefIds() as $sess_ref) {
            list(
                $tutor_source_from_course,
                $assigned_tutors,
                $tutor_name
            ) = $this->getSourceAndTutorsOfSession($sess_ref);

            if ((count($assigned_tutors) === 0 && $tutor_source_from_course)
                ||
                ($tutor_name === '' && $tutor_source_from_course === false)
            ) {
                $this->no_tutor[] = $this->buildSessionEntry($this->getSessionObj($sess_ref));
            }

            if ($tutor_source_from_course === false
                && $tutor_name !== ''
            ) {
                $this->other_tutor[] = $this->buildSessionEntry($this->getSessionObj($sess_ref));
            }

            if ($tutor_source_from_course
                && count($assigned_tutors) > 0
            ) {
                $entry = $this->buildSessionEntry($this->getSessionObj($sess_ref));
                foreach ($assigned_tutors as $tutor_id) {
                    if (!array_key_exists($tutor_id, $this->assigned_tutor)) {
                        $this->assigned_tutor[$tutor_id] = [];
                    }
                    $this->assigned_tutor[$tutor_id][] = $entry;
                }
            }
        }
    }


    /**
     * @return SessionEntry[]
     */
    public function getAllSessionEntriesWithoutTutor() : array
    {
        return $this->no_tutor;

        $ret = [];
        foreach ($this->getSessionRefIds() as $sess_ref) {
            list(
                $tutor_source_from_course,
                $assigned_tutors,
                $tutor_name
            ) = $this->getSourceAndTutorsOfSession($sess_ref);

            if (
                (count($assigned_tutors) === 0 && $tutor_source_from_course)
                ||
                ($tutor_name === '' && $tutor_source_from_course === false)
            ) {
                $ret[] = $this->buildSessionEntry($this->getSessionObj($sess_ref));
            }
        }
        return $ret;
    }

    /**
     * @return SessionEntry[]
     */
    public function getAllSessionEntriesWithoutIdentifiedTutor() : array
    {
        return $this->other_tutor;

        $ret = [];
        foreach ($this->getSessionRefIds() as $sess_ref) {
            list(
                $tutor_source_from_course,
                $assigned_tutors,
                $tutor_name
            ) = $this->getSourceAndTutorsOfSession($sess_ref);

            if ($tutor_source_from_course === false
                && $tutor_name !== ''
            ) {
                $ret[] = $this->buildSessionEntry($this->getSessionObj($sess_ref));
            }
        }
        return $ret;
    }

    /**
     * @return SessionEntry[]
     */
    public function getAllSessionEntriesWithTutor(int $usr_id) : array
    {
        $ret = [];
        if (array_key_exists($usr_id, $this->assigned_tutor)) {
            $ret = $this->assigned_tutor[$usr_id];
        }
        return $ret;

        foreach ($this->getSessionRefIds() as $sess_ref) {
            list(
                $tutor_source_from_course,
                $assigned_tutors,
                $tutor_name
            ) = $this->getSourceAndTutorsOfSession($sess_ref);

            if ($tutor_source_from_course
                && in_array($usr_id, $assigned_tutors)
            ) {
                $ret[] = $this->buildSessionEntry($this->getSessionObj($sess_ref));
            }
        }
        return $ret;
    }

    protected function buildSessionEntry(\ilObjSession $session) : SessionEntry
    {
        $session_ref_id = (int) $session->getRefId();
        $appointment = $session->getFirstAppointment();
        $start = (string) $appointment->getStart()->getUnixTime();
        $end = (string) $appointment->getEnd()->getUnixTime();
        $fullday = (bool) $appointment->isFullday();

        $start = \DateTime::createFromFormat('U', $start);
        $end = \DateTime::createFromFormat('U', $end);

        list($parent_crs_ref_id, $title) = $this->getParentCrsInfo($session_ref_id);

        return new SessionEntry(
            $session_ref_id,
            $parent_crs_ref_id,
            $title,
            $session->getDescription(),
            $fullday,
            $start,
            $end
        );
    }

    /**
     * @return int[]
     */
    protected function getSessionRefIds() : array
    {
        return $this->session_ref_ids;
    }


    /**
     * @return int[]
     */
    protected function getRelevantSessionsIds(
        int $base_ref_id,
        \DateTime $start,
        \DateTime $end
    ) : array {
        $rel_courses = $this->getInfoOfRelevantCourses($base_ref_id, $start, $end);
        $session_refs = [];
        foreach ($rel_courses as $crs_info) {
            $sessions_under_course = $this->repo->getAllChildrenOfByType((int) $crs_info['ref_id'], 'sess');
            if (count($sessions_under_course) > 0) {
                foreach ($sessions_under_course as $session_info) {
                    $session_ref = (int) $session_info['ref_id'];
                    $session_refs[] = $session_ref;
                    $this->crs_info_lookup[$session_ref] = [
                        (int) $crs_info['ref_id'],
                        $crs_info['title']
                    ];
                }
            }
        }

        return $session_refs;
    }


    protected function getInfoOfRelevantCourses(
        int $base_ref_id,
        \DateTime $start,
        \DateTime $end
    ) : array {
        $course_infos = $this->repo->getAllChildrenOfByType($base_ref_id, 'crs');

        $course_obj_ids = array_map(
            function ($info) {
                return (int) $info['obj_id'];
            },
            $course_infos
        );

        $course_ids_in_range = $this->repo->filterCourseIdsByTimeRange($course_obj_ids, $start, $end);

        $course_infos = array_filter(
            $course_infos,
            function ($info) use ($course_ids_in_range) {
                return in_array(
                    (int) $info['obj_id'],
                    $course_ids_in_range
                );
            }
        );

        $rel_courses = array_filter(
            $course_infos,
            [$this, 'isRelevantCourse']
        );

        return $rel_courses;
    }

    protected function getParentCrsInfo(int $session_ref_id) : array
    {
        return $this->crs_info_lookup[$session_ref_id];
    }

    protected function isRelevantCourse(array $crs_info) : bool
    {
        $is_online = $this->repo->isCourseOnline((int) $crs_info['obj_id']);
        $has_copy_settings = count(
            $this->repo->getAllChildrenOfByType((int) $crs_info['ref_id'], 'xcps')
        ) > 0;

        return (
            $is_online === true
            && $has_copy_settings === false
        );
    }

    protected function getSessionObj(int $session_ref_id) : \ilObjSession
    {
        if (!array_key_exists($session_ref_id, $this->cache_session_objs)) {
            $this->cache_session_objs[$session_ref_id] = $this->repo->getInstanceByRefId($session_ref_id);
        }
        return $this->cache_session_objs[$session_ref_id];
    }

    protected function getSourceAndTutorsOfSession(int $session_ref_id) : array
    {
        if (!array_key_exists($session_ref_id, $this->session_lookup)) {
            $session_object = $this->getSessionObj($session_ref_id);

            $tutor_source_from_course = $session_object->getTutorSource() === $session_object::TUTOR_CFG_FROMCOURSE;
            $assigned_tutors = [];
            if ($tutor_source_from_course) {
                $assigned_tutors = $session_object->getAssignedTutorsIds();
            }
            $this->session_lookup[$session_ref_id] = [
                $tutor_source_from_course,
                $assigned_tutors,
                trim($session_object->getName())
            ];
        }

        return $this->session_lookup[$session_ref_id];
    }
}
