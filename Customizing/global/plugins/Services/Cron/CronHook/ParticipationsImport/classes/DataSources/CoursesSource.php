<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\DataSources;

interface CoursesSource
{
    public function getCourses() : \Generator;
}
