<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\DataTargets;

interface CoursesTarget
{
    public function importCourse(Course $course);
}
