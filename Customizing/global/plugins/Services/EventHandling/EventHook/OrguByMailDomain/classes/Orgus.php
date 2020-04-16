<?php declare(strict_types=1);

namespace CaT\Plugins\OrguByMailDomain;

interface Orgus
{
    public function orguList() : array;
    public function positionList() : array;
    public function assignUserToPositionAtOrgu(int $usr_id, int $position_id, int $orgu_id);
    public function positionExists(int $position_id) : bool;
    public function orguExists(int $orgu_id) : bool;
}
