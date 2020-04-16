<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\DataTargets;

use Psr\Log\LoggerInterface as LoggerInterface;

class HistCoursesTarget extends HistTarget implements CoursesTarget
{
    const TABLE_HHD_CRS = 'hhd_crs';
    const TABLE_HST_CRS = 'hst_crs';

    const FIELD_CRS_ID = 'crs_id';
    const FIELD_BEGIN_DATE = 'begin_date';
    const FIELD_END_DATE = 'end_date';
    const FIELD_CRS_TYPE = 'crs_type';
    const FIELD_TITLE = 'title';
    const FIELD_IDD = 'idd_learning_time';
    const FIELD_PROVIDER = 'provider';
    const FIELD_VENUE = 'venue';


    public function importCourse(Course $course)
    {
        $this->updateData(
            $this->extractData($course)
        );
    }

    public function extractData(Course $course) : array
    {
        $data = [];
        $data[self::FIELD_CRS_ID] = ['integer',$course->crsId()];
        $data[self::FIELD_BEGIN_DATE] = ['text',$course->beginDate() ? $course->beginDate()->format('Y-m-d') : null];
        $data[self::FIELD_END_DATE] = ['text',$course->endDate() ? $course->endDate()->format('Y-m-d') : null];
        $data[self::FIELD_CRS_TYPE] = ['text',trim($course->crsType()) !== '' ? $course->crsType() : null];
        $data[self::FIELD_TITLE] = ['text',trim($course->crsTitle()) !== '' ? $course->crsTitle() : null];
        $data[self::FIELD_IDD] = ['integer',$course->idd() >= 0 ? $course->idd() : null];
        $data[self::FIELD_PROVIDER] = ['text',$course->provider() !== '' ? $course->provider() : null];
        $data[self::FIELD_VENUE] = ['text',$course->venue() !== '' ? $course->venue() : null];
        return $data;
    }

    protected function getPkFromData(array $data) : array
    {
        return [self::FIELD_CRS_ID => ['integer',$data[self::FIELD_CRS_ID][1]]];
    }
    protected function histTable() : string
    {
        return self::TABLE_HST_CRS;
    }
    protected function headTable() : string
    {
        return self::TABLE_HHD_CRS;
    }
}
