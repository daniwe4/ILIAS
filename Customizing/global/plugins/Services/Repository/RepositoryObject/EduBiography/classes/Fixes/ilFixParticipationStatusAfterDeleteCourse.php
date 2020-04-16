<?php

namespace CaT\Plugins\EduBiography\Fixes;

class ilFixParticipationStatusAfterDeleteCourse
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function run()
    {
        foreach ($this->getDeletedCourseIdsWithUserIds() as $deleted_crs) {
            $crs_id = $deleted_crs["crs_id"];
            $usr_ids = explode(";", $deleted_crs["usr_ids"]);
            foreach ($usr_ids as $usr_id) {
                $this->updateParticipationStatusFor($crs_id, $usr_id);
            }
        }
    }

    protected function getDeletedCourseIdsWithUserIds()
    {
        $q = "SELECT GROUP_CONCAT(DISTINCT huc.usr_id SEPARATOR ';') AS usr_ids, hc.crs_id" . PHP_EOL
            . " FROM hhd_crs hc" . PHP_EOL
            . " JOIN hst_usrcrs huc ON huc.crs_id = hc.crs_id" . PHP_EOL
            . " WHERE hc.deleted = 1 AND huc.participation_status IN ('successful', 'absent')" . PHP_EOL
            . " GROUP BY hc.crs_id"
        ;

        $res = $this->db->query($q);
        $ret = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $ret[] = $row;
        }

        return $ret;
    }

    protected function updateParticipationStatusFor(int $crs_id, int $usr_id)
    {
        $q = "UPDATE hhd_usrcrs SET participation_status =" . PHP_EOL
            . "    (SELECT participation_status" . PHP_EOL
            . "    FROM hst_usrcrs" . PHP_EOL
            . "    WHERE crs_id = " . $crs_id . PHP_EOL
            . "        AND usr_id = " . $usr_id . PHP_EOL
            . "        AND created_ts < " . PHP_EOL
            . "            (SELECT created_ts" . PHP_EOL
            . "            FROM hst_crs" . PHP_EOL
            . "            WHERE crs_id = " . $crs_id . PHP_EOL
             . "               AND deleted = 1" . PHP_EOL
            . "            ORDER BY row_id LIMIT 1" . PHP_EOL
            . "            )" . PHP_EOL
            . "        AND participation_status IN ('successful', 'absent')" . PHP_EOL
            . "    ORDER BY row_id DESC" . PHP_EOL
            . "    LIMIT 1" . PHP_EOL
            . "    )" . PHP_EOL
            . " WHERE crs_id = " . $crs_id . PHP_EOL
            . "    AND usr_id = " . $usr_id
        ;

        $this->db->manipulate($q);
    }
}
