<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\Mappings;

interface UserMapping extends Mapping
{
    public function iliasUserIdForExternUserId(string $extern_id) : int;
}
