<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport;

class CourseTranslator
{
    protected $c_m;
    protected $tr_m;
    protected $te_m;

    public function __construct(
        Mappings\CourseMapping $c_m
    ) {
        $this->c_m = $c_m;
    }

    public function translateCourse(
        DataSources\Course $src_course
    ) : DataTargets\Course {
        $title = $src_course->title();
        $crs_id = $this->c_m->iliasCrsIdForExternCrsId($src_course->crsId());
        $crs_type = $src_course->crsType();
        $begin_date = $src_course->beginDate();
        $end_date = $src_course->endDate();
        $idd = $src_course->idd();
        $provider = $src_course->provider();
        $venue = $src_course->venue();
        return new DataTargets\Course(
            $crs_id,
            $title,
            $crs_type,
            $begin_date,
            $end_date,
            $idd,
            $provider,
            $venue
        );
    }
}
