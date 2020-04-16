<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\IliasUtils;

interface UserUtils
{
    const NONE = -1;

    public function userIdByLogin(string $login) : int;
    public function userIdsByEmail(string $email) : array;
    public function userIdByUdfField(string $value, int $field_id) : array;
    /**
     * field_id => title
     */
    public function udfFields() : array;
}
