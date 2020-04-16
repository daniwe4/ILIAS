<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

use CaT\Plugins\ParticipationsImport as PI;

/**
* Implementation of the cron job
*/
class ilParticipationsImportJob extends ilCronJob
{
    const ID = 'participation_import';


    protected $courses_source;
    protected $participations_source;
    protected $course_translator;
    protected $participation_translator;
    protected $courses_target;
    protected $participations_target;

    public function __construct(
        PI\DataSources\CoursesSource $courses_source,
        PI\DataSources\ParticipationsSource $participations_source,
        PI\CourseTranslator $course_translator,
        PI\ParticipationTranslator $participation_translator,
        PI\DataTargets\CoursesTarget $courses_target,
        PI\DataTargets\ParticipationsTarget $participations_target,
        ilLogger $log
    ) {
        $this->courses_source = $courses_source;
        $this->participations_source = $participations_source;
        $this->course_translator = $course_translator;
        $this->participation_translator = $participation_translator;
        $this->courses_target = $courses_target;
        $this->participations_target = $participations_target;
        $this->log = $log;
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return self::ID;
    }

    /**
     * Is to be activated on "installation"
     *
     * @return boolean
     */
    public function hasAutoActivation()
    {
        return false;
    }

    /**
     * Can the schedule be configured?
     *
     * @return boolean
     */
    public function hasFlexibleSchedule()
    {
        return false;
    }

    /**
     * Get schedule type
     *
     * @return int
     */
    public function getDefaultScheduleType()
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    /**
     * Get schedule value
     *
     * @return int|array
     */
    public function getDefaultScheduleValue()
    {
        return 1;
    }

    /**
     * Get called if the cronjob is started
     * Executing the ToDo's of the cronjob
     */
    public function run()
    {
        $cron_result = new \ilCronJobResult();
        \ilCronManager::ping($this->getId());
        $this->importCourses();
        \ilCronManager::ping($this->getId());
        $this->importParticipations();
        $cron_result->setStatus(\ilCronJobResult::STATUS_OK);
        return $cron_result;
    }

    protected function importCourses()
    {
        $cnt = 0;
        foreach ($this->courses_source->getCourses() as $src_crs) {
            $this->logInitCrs($src_crs);
            try {
                $target_crs = $this->course_translator->translateCourse($src_crs);
                $this->courses_target->importCourse($target_crs);
                $this->logTransferCrs($src_crs, $target_crs);
            } catch (PI\Exception $e) {
                $this->log->error('error during import of course crs_id:' . $src_crs->crsId() . ':' . $e->getMessage());
                continue;
            }
            if ((++$cnt) % 100 === 0) {
                \ilCronManager::ping($this->getId());
            }
        }
    }

    protected function logInitCrs(PI\DataSources\Course $src_crs)
    {
        $this->log->notice(
            'Init import of course with properties '
            . 'crs_id:' . $src_crs->crsId() . ','
            . 'title:' . $src_crs->title() . ','
            . 'type:' . $src_crs->crsType() . ','
            . 'begin_date:' . ($src_crs->beginDate() ? $src_crs->beginDate()->format('Y-m-d') : '') . ','
            . 'end_date:' . ($src_crs->endDate() ? $src_crs->endDate()->format('Y-m-d') : '') . ','
            . 'idd:' . $src_crs->idd() . ','
            . 'provider:' . $src_crs->provider() . ','
            . 'venue:' . $src_crs->venue()
        );
    }

    protected function logTransferCrs(
        PI\DataSources\Course $src_crs,
        PI\DataTargets\Course $target_crs
    ) {
        $this->log->notice(
            'Transfer of course based on crs_id:' . $src_crs->crsId() . ' '
            . 'with target properties '
            . 'crs_id:' . $target_crs->crsId() . ','
            . 'title:' . $target_crs->crsTitle() . ','
            . 'type:' . $target_crs->crsType() . ','
            . 'begin_date:' . ($target_crs->beginDate() ? $target_crs->beginDate()->format('Y-m-d') : '') . ','
            . 'end_date:' . ($target_crs->endDate() ? $target_crs->endDate()->format('Y-m-d') : '') . ','
            . 'idd:' . $target_crs->idd() . ','
            . 'provider:' . $target_crs->provider() . ','
            . 'venue:' . $target_crs->venue()
        );
    }



    protected function importParticipations()
    {
        $cnt = 0;
        foreach ($this->participations_source->getParticipations() as $src_part) {
            $this->logInitParticipation($src_part);
            try {
                $target_part = $this->participation_translator->translateParticipation($src_part);
                $this->participations_target->importParticipation($target_part);
                $this->logTransferParticipation($src_part, $target_part);
            } catch (PI\Exception $e) {
                $this->log->error('error during import of partcipation crs_id:' . $src_part->externCrsId() . ',usr_id:' . $src_part->externUsrId() . '.' . $e->getMessage());
            }
            if ((++$cnt) % 100 === 0) {
                \ilCronManager::ping($this->getId());
            }
        }
    }


    protected function logInitParticipation(PI\DataSources\Participation $src_part)
    {
        $this->log->notice(
            'Init import of participation '
            . 'crs_id:' . $src_part->externCrsId() . ','
            . 'login:' . $src_part->externUsrId() . ','
            . 'booking_status:' . $src_part->bookingStatus() . ','
            . 'participation_status:' . $src_part->participationStatus() . ','
            . 'begin_date:' . ($src_part->beginDate() ? $src_part->beginDate()->format('Y-m-d') : '') . ','
            . 'end_date:' . ($src_part->endDate() ? $src_part->endDate()->format('Y-m-d') : '') . ','
            . 'idd:' . $src_part->idd()
        );
    }


    protected function logTransferParticipation(
        PI\DataSources\Participation $src_part,
        PI\DataTargets\Participation $target_part
    ) {
        $this->log->notice(
            'Transfer of participation based on '
            . 'crs_id:' . $src_part->externCrsId() . ','
            . 'login:' . $src_part->externUsrId() . ','
            . 'with target properties '
            . 'crs_id:' . $target_part->crsId() . ','
            . 'usr_id:' . $target_part->usrId() . ','
            . 'booking_status:' . $target_part->bookingStatus() . ','
            . 'participation_status:' . $target_part->participationStatus() . ','
            . 'begin_date:' . ($target_part->beginDate() ? $target_part->beginDate()->format('Y-m-d') : '') . ','
            . 'end_date:' . ($target_part->endDate() ? $target_part->endDate()->format('Y-m-d') : '') . ','
            . 'idd:' . $target_part->idd()
        );
    }
}
