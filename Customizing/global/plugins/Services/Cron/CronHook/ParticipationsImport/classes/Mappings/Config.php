<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\Mappings;

class Config
{
    const LOGIN = -1;
    const EMAIL = -2;
    const NONE = 0;
    protected $extern_id_field;

    public function __construct(
        int $extern_id_field
    ) {
        $this->extern_id_field = $extern_id_field;
    }

    public function externUsrIdField() : int
    {
        return $this->extern_id_field;
    }
}
