<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\Mappings;

use CaT\Plugins\ParticipationsImport\IliasUtils\UserUtils;

class ilUserMapping implements UserMapping
{
    protected $cs;
    protected $uu;

    public function __construct(
        ConfigStorage $cs,
        UserUtils $uu
    ) {
        $this->c = $cs->loadCurrentConfig();
        $this->uu = $uu;
    }

    public function iliasUserIdForExternUserId(string $extern_id) : int
    {
        $ext_id_field = $this->c->externUsrIdField();
        switch ($ext_id_field) {
            case Config::NONE:
                return self::NO_MAPPING_FOUND_INT;
            case Config::LOGIN:
                $usr_id = $this->uu->userIdByLogin($extern_id);
                return $usr_id === UserUtils::NONE ? self::NO_MAPPING_FOUND_INT : $usr_id;
            case Config::EMAIL:
                $usr_id = $this->uu->userIdByEmail($extern_id);
                return $usr_id === UserUtils::NONE ? self::NO_MAPPING_FOUND_INT : $usr_id;
            default:
                $usr_ids = $this->uu->userIdByUdfField($extern_id, $ext_id_field);
                return count($usr_ids) === 0 ? self::NO_MAPPING_FOUND_INT : array_shift($usr_ids);
        }
    }
}
