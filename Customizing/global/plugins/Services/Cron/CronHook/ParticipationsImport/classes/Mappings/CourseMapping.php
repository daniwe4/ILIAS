<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\Mappings;

interface CourseMapping extends Mapping
{
    public function iliasCrsIdForExternCrsId(string $extern_id) : int;
}
