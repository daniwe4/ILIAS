<?php declare(strict_types=1);

namespace CaT\Plugins\OrguByMailDomain;

class IliasOrgus implements Orgus
{
    protected $db;

    protected static $orgus_cache = null;
    protected static $positions_cache = null;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function orguList() : array
    {
        if (self::$orgus_cache == null) {
            self::$orgus_cache = $this->loadOrguList();
        }
        return self::$orgus_cache;
    }

    protected function loadOrguList() : array
    {
        $q = 'SELECT title,ref_id'
            . '	FROM object_data'
            . '	JOIN object_reference USING(obj_id)'
            . '	WHERE type = \'orgu\' AND deleted IS NULL'
            . '		AND ref_id != ' . $this->db->quote(\ilObjOrgUnit::getRootOrgRefId(), 'integer');
        $return = [];
        $res = $this->db->query($q);
        while ($rec = $this->db->fetchAssoc($res)) {
            $return[(int) $rec['ref_id']] = (string) $rec['title'];
        }

        return $return;
    }

    public function positionList() : array
    {
        if (self::$positions_cache == null) {
            self::$positions_cache = $this->loadPositionList();
        }
        return self::$positions_cache;
    }


    protected function loadPositionList() : array
    {
        $return = [];
        foreach (\ilOrgUnitPosition::get() as $position) {
            $return[(int) $position->getId()] = $position->getTitle();
        }
        return $return;
    }

    public function assignUserToPositionAtOrgu(int $usr_id, int $position_id, int $orgu_ref_id)
    {
        \ilOrgUnitUserAssignment::findOrCreateAssignment($usr_id, $position_id, $orgu_ref_id);
    }

    public function positionExists(int $position_id) : bool
    {
        $inst = \ilOrgUnitPosition::where([
            'id' => $position_id
        ])->first();
        if ($inst instanceof \ilOrgUnitPosition) {
            return true;
        }
        return false;
    }

    public function orguExists(int $orgu_ref_id) : bool
    {
        return \ilObject::_lookupType($orgu_ref_id, true) === 'orgu';
    }
}
