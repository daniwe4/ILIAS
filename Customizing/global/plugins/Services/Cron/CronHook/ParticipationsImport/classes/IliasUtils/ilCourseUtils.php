<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\IliasUtils;

class ilCourseUtils implements CourseUtils
{
    const CRS_TABLE = 'hhd_crs';

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function courseIdExists(int $id) : bool
    {
        $res = $this->db->query(
            'SELECT crs_id'
            . '	FROM ' . self::CRS_TABLE
            . '	WHERE crs_id = ' . $this->db->quote($id, 'integer')
        );
        while ($this->db->fetchAssoc($res)) {
            return true;
        }
        return false;
    }
}
