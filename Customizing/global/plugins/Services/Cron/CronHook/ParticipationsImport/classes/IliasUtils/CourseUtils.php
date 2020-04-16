<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\IliasUtils;

interface CourseUtils
{
    public function courseIdExists(int $id) : bool;
}
