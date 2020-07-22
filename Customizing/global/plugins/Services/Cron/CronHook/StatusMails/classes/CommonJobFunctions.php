<?php

declare(strict_types=1);

namespace CaT\Plugins\StatusMails;

use CaT\Plugins\StatusMails\Course\CourseFlags;
use ILIAS\TMS\Mailing\TMSMailClerk;

/**
 * some functions are shared by both jobs.
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
trait CommonJobFunctions
{
    /**
     * @var Orgu\DB
     */
    protected $orgu;

    /**
     * @var History\DB
     */
    protected $history;

    /**
     * @var Mailing\MailFactory
     */
    protected $factory;

    /**
     * @var TMSMailClerk
     */
    protected $clerk;

    /**
     * @var int[]
     */
    protected $cached_refs;

    /**
     * @var \Closure
     */
    protected $txt;

    protected function calculateOffsetBySettings() : \DateInterval
    {
        //get the REAL schedule-type.
        //getScheduleType will not return for flexible schedules.
        $cron_data = \ilCronManager::getCronJobData(static::ID);
        $schedule_type = (int) $cron_data[0]['schedule_type'];
        $schedule_value = (int) $cron_data[0]['schedule_value'];

        switch ($schedule_type) {
            case static::SCHEDULE_TYPE_DAILY:
                $offset = new \DateInterval('P1D');
                break;
            case static::SCHEDULE_TYPE_IN_MINUTES:
                $offset = new \DateInterval('PT' . $schedule_value . 'M');
                break;
            case static::SCHEDULE_TYPE_IN_HOURS:
                $offset = new \DateInterval('PT' . $schedule_value . 'H');
                break;
            case static::SCHEDULE_TYPE_IN_DAYS:
                $offset = new \DateInterval('P' . $schedule_value . 'D');
                break;
            case static::SCHEDULE_TYPE_WEEKLY:
                $offset = new \DateInterval('P7D');
                break;
            case static::SCHEDULE_TYPE_MONTHLY:
                $offset = new \DateInterval('P1M');
                break;
            case static::SCHEDULE_TYPE_QUARTERLY:
                $offset = new \DateInterval('P3M');
                break;
            case static::SCHEDULE_TYPE_YEARLY:
                $offset = new \DateInterval('P1Y');
                break;
            default:
                $offset = new \DateInterval('P0D');
        }
        return $offset;
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }

    /**
     * @inheritDoc
     */
    public function hasAutoActivation()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function hasFlexibleSchedule()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultScheduleType()
    {
        return static::SCHEDULE_TYPE;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultScheduleValue()
    {
        return static::DEFAULT_SCHEDULE_VALUE;
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        foreach ($this->orgu->getAllSuperiorsAndMinions() as $superior) {
            $employees = $superior->getEmployees();
            if (count($employees) === 0) {
                continue;
            }

            $data = $this->getActivityData($employees);
            if (is_null($data)) {
                continue;
            }

            //get relevant course-properties for every mentioned course.
            $flags = [];
            $filtered_data = [];
            foreach ($data as $activity) {
                $crs_obj_id = (int) $activity->getCourseObjId();
                $flags = $this->getCourseFlags($flags, $crs_obj_id);

                if ($flags[$crs_obj_id]->preventMailEntirely() === false) {
                    $filtered_data[] = $activity;
                }
            }

            if (count($filtered_data) === 0) {
                continue;
            }

            $mail = $this->factory->getMail($superior->getUserId(), $filtered_data, $flags);
            $this->clerk->process([$mail], $this->getId()); //2nd parameter: event
            $this->ping();
        }
        return $this->getOkResult();
    }

    /**
     * Contents of the mail may vary according to the course's settings.
     * This retieves flags relevant for the mail.
     * @param int[] $flags
     * @return int[]
     */
    protected function getCourseFlags(array $flags, int $crs_obj_id) : array
    {
        if (array_key_exists($crs_obj_id, $flags)) {
            return $flags;
        }

        $ref = $this->getCourseRefById($crs_obj_id);
        if (is_null($ref)) {
            $cf = new CourseFlags($crs_obj_id);
        } else {
            $cf = new CourseFlags(
                $crs_obj_id,
                $this->preventMailForCourse($ref),
                $this->showAccomodation($ref)
            );
        }

        $flags[$crs_obj_id] = $cf;
        return $flags;
    }

    /**
     * Get a ref-id for a course's object-id.
     * @param int $crs_obj_id
     * @return    int|null
     */
    protected function getCourseRefById(int $crs_obj_id) : ?int
    {
        if (!array_key_exists($crs_obj_id, $this->cached_refs)) {
            $ref = $this->getRefForObjId($crs_obj_id);
            $this->cached_refs[$crs_obj_id] = $ref;
        }
        return $this->cached_refs[$crs_obj_id];
    }

    protected function getRefForObjId(int $crs_obj_id) : ?int
    {
        try {
            $course = new \ilObjCourse($crs_obj_id, false);
            $refs = \ilObject::_getAllReferences($course->getId());
            $ref = array_shift(array_keys($refs));
        } catch (\Exception $e) {
            return null;
        }
        return (int) $ref;
    }

    protected function preventMailForCourse(int $crs_ref_id) : bool
    {
        $sub_items = $this->getAllChildrenOfByType($crs_ref_id, 'xcml');//get ilObjCourseMailing(s)
        foreach ($sub_items as $item) {
            if ($item->getSettings()->getPreventMailing()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get all children by type recursively.
     * @param int    $ref_id
     * @param string $search_type
     * @return \ilObject[]
     */
    protected function getAllChildrenOfByType(int $ref_id, string $search_type) : array
    {
        $children = $this->getTree()->getSubTree(
            $this->getTree()->getNodeData($ref_id),
            true,
            $search_type
        );

        return array_map(
            function ($node) {
                return \ilObjectFactory::getInstanceByRefId($node["child"]);
            },
            $children
        );
    }

    abstract protected function getTree();

    protected function showAccomodation(int $crs_ref_id) : bool
    {
        $sub_items = $this->getAllChildrenOfByType($crs_ref_id, 'xoac');//get ilObjAccomodation
        if (count($sub_items) > 0) {
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return static::ID;
    }

    protected function ping() : void
    {
        \ilCronManager::ping($this->getId());
    }

    protected function getOkResult() : \ilCronJobResult
    {
        $cron_result = new \ilCronJobResult();
        $cron_result->setStatus(\ilCronJobResult::STATUS_OK);
        return $cron_result;
    }
}
